<?php

namespace __defaultNamespace__\Controllers;

use __defaultNamespace__\Models\Opname;
use __defaultNamespace__\Models\OpnameDetail;
use __defaultNamespace__\Models\Related\MItem;
use __defaultNamespace__\Requests\ConfirmApprovalRequest;
use __defaultNamespace__\Requests\StoreRequest;
use __defaultNamespace__\Requests\UpdateRequest;
use App\Http\Controllers\Controller;
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
        $startDate = now()->parse($request->start_date ?? now()->startOfMonth())->startOfDay();
        $endDate = now()->parse($request->end_date ?? now()->endOfMonth())->endOfDay();
        
        // filter by date-range
        $opnames = Opname::whereBetween('date', [$startDate, $endDate]);

        // filter by company-id
        if ($request->company_id) {
            $opnames = $opnames->whereCompanyId($request->company_id);
        }

        // filter by warehouse-id
        if ($request->warehouse_id) {
            $opnames = $opnames->whereWarehouseId($request->warehouse_id);
        }

        $opnames = $opnames->with('company', 'warehouse', 'details.item')
            ->orderByDesc('number')
            ->get();

        $this->loggerHelper->logSuccess($request->getRequestUri(), $request->user(), $request->all());
        return $this->responseFormatter->successResponse('', $opnames);
    }

    // store new data
    public function store(StoreRequest $request)
    {
        DB::beginTransaction();
        try {
            $date = now();
            $number = $request->number ?? NotaGenerator::generate('inv_opnames', 'number', 5, $date)->addPrefix('OPNAME', '/')->getResult();

            // insert new data
            $opname = new Opname();
            $opname->company_id = $request->company_id;
            $opname->warehouse_id = $request->warehouse_id;
            $opname->number = $number;
            $opname->date = $date;
            $opname->note = $request->note;
            $opname->created_by = $request->user()->id;
            $opname->updated_by = $request->user()->id;
            $opname->save();

            // insert details
            $listDetail = [];
            foreach ($request->list_item_id ?? [] as $key => $itemId) {
                $expiredDate = $request->list_expired_date[$key] ? now()->parse($request->list_expired_date[$key]) : null;
                array_push($listDetail, [
                    'opname_id' => $opname->id,
                    'item_id' => $itemId,
                    'expired_date' => $expiredDate,
                    'old_qty' => deformatCurrency($request->list_old_qty[$key]),
                    'new_qty' => deformatCurrency($request->list_new_qty[$key]),
                    'unit_price' => deformatCurrency($request->list_unit_price[$key] ?? 0),
                    'note' => $request->list_note[$key] ?? null,
                ]);
            }
            OpnameDetail::insert($listDetail);

            DB::commit();
            $this->loggerHelper->logSuccess($request->getRequestUri(), $request->user(), $request->all());
            return $this->responseFormatter->successResponse('Data berhasil disimpan', $opname);
        } catch (\Throwable $th) {
            DB::rollBack();
            $this->loggerHelper->logError($th, $request->user(), $request->all());
            return $this->responseFormatter->errorResponse($th);
        }
    }

    // get specific data
    public function show(Request $request, $id)
    {
        try {
            $opname = Opname::with('company', 'warehouse', 'createdBy', 'adjustedBy', 'updatedBy', 'details.item')
                ->findOrFail($id);
    
            $this->loggerHelper->logSuccess($request->getRequestUri(), $request->user(), $request->all());
            return $this->responseFormatter->successResponse('', $opname);
        } catch (\Throwable $th) {
            $this->loggerHelper->logError($th, $request->user(), $request->all());
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
            if ($opname->adjustment_status != 'waiting') {
                throw new Exception("Data sudah diproses, tidak dapat diubah", Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            // update data
            $opname->note = $request->note;
            $opname->updated_by = $request->user()->id;
            $opname->update();
            // delete current details
            OpnameDetail::where('opname_id', $opname->id)->delete();
            // update details
            $listDetail = [];
            foreach ($request->list_item_id ?? [] as $key => $itemId) {
                $expiredDate = $request->list_expired_date[$key] ? now()->parse($request->list_expired_date[$key]) : null;
                array_push($listDetail, [
                    'opname_id' => $opname->id,
                    'item_id' => $itemId,
                    'expired_date' => $expiredDate,
                    'old_qty' => deformatCurrency($request->list_old_qty[$key] ?? 0),
                    'new_qty' => deformatCurrency($request->list_new_qty[$key] ?? 0),
                    'unit_price' => deformatCurrency($request->list_unit_price[$key] ?? 0),
                    'note' => $request->list_note[$key] ?? null,
                ]);
            }
            OpnameDetail::insert($listDetail);

            DB::commit();
            $this->loggerHelper->logSuccess($request->getRequestUri(), $request->user(), $request->all());
            return $this->responseFormatter->successResponse('Data berhasil diperbarui', $opname);
        } catch (\Throwable $th) {
            DB::rollBack();
            $this->loggerHelper->logError($th, $request->user(), $request->all());
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
            if ($opname->adjustment_status != 'waiting') {
                throw new Exception("Data sudah diproses, tidak dapat diubah", Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            $opname->delete();

            DB::commit();
            $this->loggerHelper->logSuccess($request->getRequestUri(), $request->user(), $request->all());
            return $this->responseFormatter->successResponse();
        } catch (\Throwable $th) {
            DB::rollBack();
            $this->loggerHelper->logError($th, $request->user(), $request->all());
            return $this->responseFormatter->errorResponse($th);
        }
    }

    // approval data
    public function confirmApproval(ConfirmApprovalRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            // get data
            $opname = Opname::with('details')->findOrFail($id);
            if ($opname->adjustment_status != 'waiting') {
                throw new Exception("Data sudah diproses, tidak dapat diubah", Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            if ($request->status == 'reject') {
                $opname->adjustment_status = 'rejected';
            } else {
                $opname->adjustment_status = 'approved';
                // insert mutation
                $mutation = new StockMutation;
                foreach ($opname->details as $detail) {
                    $itemId = $detail->item_id;
                    $oldQty = $detail->old_qty;
                    $currentStock = $mutation->currentStock($itemId, $opname->company_id, $opname->warehouse_id)->getData()->curent_stock;
                    // validate current-stock is already changed or not
                    if ($oldQty != $currentStock) {
                        $detail->status = 'locked';
                        $detail->save();
                    } else {
                        $detail->status = 'adjusted';
                        $detail->save();
                        // insert mutation
                        $newQty = $detail->new_qty;
                        $hpp = $detail->hpp;
                        $diffStock = $newQty - $currentStock;
                        $cogm = 0;
                        if ($diffStock > 0) {
                            $mutationIn = $mutation->mutationIn($itemId, $opname->warehouse_id, abs($diffStock), now(), $hpp, $opname->number, $opname->company_id, null, "Stock Adjustment", $opname);
                            if ($mutationIn->getStatus() != 'success') {
                                throw new Exception($mutationIn->getErrorMessage(), 400);
                            }
                            $opname->stockMutations()->save($mutationIn->getData()->model);
                            $cogm = ($hpp * $diffStock);
                        } elseif ($diffStock < 0) {
                            $mutationOut = $mutation->mutationOut($itemId, $opname->warehouse_id, abs($diffStock), now(), $opname->company_id, $opname->number, "Stock Adjustment", $opname);
                            if ($mutationOut->getStatus() != 'success') {
                                $itemName = MItem::whereId($itemId)->value('name');
                                throw new Exception($mutationOut->getErrorMessage() . ' ( ' . $itemName . ' )', Response::HTTP_UNPROCESSABLE_ENTITY);
                            }
                            foreach ($mutationOut->getData()->model as $model) {
                                $opname->stockMutations()->save($model);
                            }
                            $cogm = ($mutationOut->getData()->cogm ?? 0) * -1;
                        }
                    }
                }
            }

            // update opname data
            $opname->adjusted_by = $request->user()->id;
            $opname->adjusted_at = now();
            $opname->updated_by = $request->user()->id;
            $opname->save();

            DB::commit();
            $this->loggerHelper->logSuccess($request->getRequestUri(), $request->user(), $request->all());
            return $this->responseFormatter->successResponse();
        } catch (\Throwable $th) {
            DB::rollBack();
            $this->loggerHelper->logError($th, $request->user(), $request->all());
            return $this->responseFormatter->errorResponse($th);
        }
    }
}
