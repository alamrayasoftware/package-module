<?php

namespace __defaultNamespace__\Controllers;

use __defaultNamespace__\Models\Opname;
use __defaultNamespace__\Models\OpnameDetail;
use __defaultNamespace__\Models\Related\Item;
use App\Http\Controllers\Controller;
use __defaultNamespace__\Requests\StoreRequest;
use __defaultNamespace__\Requests\UpdateRequest;
use App\Helpers\LoggerHelper;
use App\Helpers\ResponseFormatter;
use ArsoftModules\NotaGenerator\Facades\NotaGenerator;
use ArsoftModules\StockMutation\StockMutation;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class OpnameController extends Controller
{
    public $responseFormatter, $loggerHelper;

    public function __construct()
    {
        $this->responseFormatter = new ResponseFormatter();
        $this->loggerHelper = new LoggerHelper();
    }

    // get all data
    public function index(Request $request)
    {
        $startDate = now()->startOfMonth();
        $endDate = now()->endOfMonth();
        if ($request->start_date) {
            $startDate = now()->parse($request->start_date)->startOfDay();
        }
        if ($request->end_date) {
            $endDate = now()->parse($request->end_date)->endOfDay();
        }
        $opname = Opname::whereBetween('date', [$startDate, $endDate])
            ->with('financeAccount', 'warehousePosition', 'details.item')
            ->orderByDesc('number')
            ->get();

        $this->loggerHelper->logSuccess('index', $request->user()->company_id, $request->user()->user_id, $request->all());
        return $this->responseFormatter->successResponse('', $opname);
    }

    // store new data
    public function store(StoreRequest $request)
    {
        DB::beginTransaction();
        try {
            $date = now()->parse($request->date ?? now());
            $number = $request->code ?? NotaGenerator::generate('inv_opname', 'number', 5, $date)->addPrefix('OPNAME', '/')->getResult();
            // insert new data
            $opname = new Opname();
            $opname->company_id = $request->user()->company_id;
            $opname->position_id = $request->position_id;
            $opname->account_id = $request->account_id;
            $opname->number = $number;
            $opname->date = $date;
            $opname->note = $request->note;
            $opname->save();
            // insert details
            $listDetail = [];
            foreach ($request->list_item_id ?? [] as $key => $item) {
                array_push($listDetail, [
                    'opname_id' => $opname->id,
                    'item_id' => $item,
                    'old_qty' => deformatCurrency($request->list_qty_system[$key] ?? 0),
                    'new_qty' => deformatCurrency($request->list_qty_new[$key] ?? 0),
                ]);
            }
            OpnameDetail::insert($listDetail);
            // commit data
            DB::commit();
            $this->loggerHelper->logSuccess('store', $request->user()->company_id, $request->user()->user_id, $request->all());
            return $this->responseFormatter->successResponse('Data berhasil disimpan', $opname);
        } catch (\Throwable $th) {
            DB::rollBack();
            $this->loggerHelper->logError($th, $request->user()->company_id, $request->user()->user_id, $request->all());
            return $this->responseFormatter->errorResponse($th);
        }
    }

    // get specific data
    public function show(Request $request, $id)
    {
        try {
            $opname = Opname::with('financeAccount', 'warehousePosition', 'details.item')
                ->findOrFail($id);
    
            $this->loggerHelper->logSuccess('show', $request->user()->company_id, $request->user()->user_id, $request->all());
            return $this->responseFormatter->successResponse('', $opname);
        } catch (\Throwable $th) {
            $this->loggerHelper->logError($th, $request->user()->company_id, $request->user()->user_id, $request->all());
            return $this->responseFormatter->errorResponse($th);
        }
    }

    // update data
    public function update(UpdateRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            // get opname by id
            $opname = Opname::findOrFail($id);
            // validate is adjusted
            if ($opname->adjustment_status) {
                throw new Exception("Data sudah diproses, tidak dapat diubah", Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            // update data
            $opname->company_id = $request->user()->company_id;
            $opname->position_id = $request->position_id;
            $opname->account_id = $request->account_id;
            $opname->note = $request->note;
            $opname->update();
            // delete current details
            OpnameDetail::where('opname_id', $opname->id)->delete();
            // update details
            $listDetail = [];
            foreach ($request->list_item_id ?? [] as $key => $item) {
                array_push($listDetail, [
                    'opname_id' => $opname->id,
                    'item_id' => $item,
                    'old_qty' => deformatCurrency($request->list_qty_system[$key] ?? 0),
                    'new_qty' => deformatCurrency($request->list_qty_new[$key] ?? 0),
                ]);
            }
            OpnameDetail::insert($listDetail);
            // commit data
            DB::commit();
            $this->loggerHelper->logSuccess('update', $request->user()->company_id, $request->user()->user_id, $request->all());
            return $this->responseFormatter->successResponse('Data berhasil diperbarui', $opname);
        } catch (\Throwable $th) {
            DB::rollBack();
            $this->loggerHelper->logError($th, $request->user()->company_id, $request->user()->user_id, $request->all());
            return $this->responseFormatter->errorResponse($th);
        }
    }

    // delete data
    public function destroy(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            // get opname by id
            $opname = Opname::findOrFail($id);
            if ($opname->adjustment_status) {
                throw new Exception("Data sudah diproses, tidak dapat diubah", Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            $opname->delete();

            DB::commit();
            $this->loggerHelper->logSuccess('delete', $request->user()->company_id, $request->user()->user_id, $request->all());
            return $this->responseFormatter->successResponse();
        } catch (\Throwable $th) {
            DB::rollBack();
            $this->loggerHelper->logError($th, $request->user()->company_id, $request->user()->user_id, $request->all());
            return $this->responseFormatter->errorResponse($th);
        }
    }

    // approval data
    public function approval(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            // get data
            $opname = Opname::with('details')->findOrFail($id);
            if ($opname->adjustment_status) {
                throw new Exception("Data sudah diproses, tidak dapat diubah", Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            // insert mutation
            $mutation = new StockMutation;
            foreach ($opname->details as $detail) {
                $itemId = $detail->item_id;
                $oldQty = $detail->old_qty;
                $currentStock = $mutation->currentStock($itemId, $opname->company_id, $opname->position_id)->getData()->curent_stock;
                // validate current-stock is already changed or not
                if ($oldQty != $currentStock) {
                    $opname->note = '( Stok sudah berubah, opname tidak dapat diproses )';
                    $opname->save();
                    throw new Exception("Stok sudah berubah, opname tidak dapat diproses", Response::HTTP_UNPROCESSABLE_ENTITY);
                }
                // insert mutation
                $newQty = $detail->new_qty;
                $hpp = $detail->hpp;
                $diffStock = $newQty - $currentStock;
                $cogm = 0;
                if ($diffStock > 0) {
                    $mutationIn = $mutation->mutationIn($itemId, $opname->position_id, abs($diffStock), now(), $hpp, $opname->number, $opname->company_id, null, "Stock Adjustment", $opname);
                    if ($mutationIn->getStatus() != 'success') {
                        throw new Exception($mutationIn->getErrorMessage(), 400);
                    }
                    $opname->mutation()->save($mutationIn->getData()->model);
                    $cogm = ($hpp * $diffStock);
                } elseif ($diffStock < 0) {
                    $mutationOut = $mutation->mutationOut($itemId, $opname->position_id, abs($diffStock), now(), $opname->company_id, $opname->number, "Stock Adjustment", $opname);
                    if ($mutationOut->getStatus() != 'success') {
                        $itemName = Item::where('id', $itemId)->value('name');
                        throw new Exception($mutationOut->getErrorMessage() . ' ( ' . $itemName . ' )', Response::HTTP_UNPROCESSABLE_ENTITY);
                    }
                    foreach ($mutationOut->getData()->model as $key => $model) {
                        $opname->mutation()->save($model);
                    }
                    $cogm = ($mutationOut->getData()->cogm ?? 0) * -1;
                }
            }
            // update opname data
            $opname->adjustment_status = true;
            $opname->adjustment_by = $request->user()->id;
            $opname->adjustment_at = now();
            $opname->update();

            DB::commit();
            $this->loggerHelper->logSuccess('approval', $request->user()->company_id, $request->user()->user_id, $request->all());
            return $this->responseFormatter->successResponse();
        } catch (\Throwable $th) {
            DB::rollBack();
            $this->loggerHelper->logError($th, $request->user()->company_id, $request->user()->user_id, $request->all());
            return $this->responseFormatter->errorResponse($th);
        }
    }
}
