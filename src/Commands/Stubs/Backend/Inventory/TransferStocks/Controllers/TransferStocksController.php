<?php

namespace __defaultNamespace__\Controllers;

use __defaultNamespace__\Models\InventoryTransactionDetails;
use __defaultNamespace__\Models\InventoryTransactions;
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

class TransferStocksController extends Controller
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
        $companyId = $request->user()->company_id;
        $transfer = InventoryTransactions::dataOwner($companyId, 'transfer')->whereBetween('date', [$startDate, $endDate]);

        if ($request->company_destination_id) {
            $transfer = $transfer->where('company_destination_id', $request->company_destination_id);
        }
        $transfer = $transfer->where('type', 'transfer')->with('destination', 'companyDestination', 'financeAccount', 'origin', 'companyOrigin')->get();

        $this->loggerHelper->logSuccess($request->getRequestUri(), $request->user(), $request->all());
        return $this->responseFormatter->successResponse('', $transfer);
    }

    // store new data
    public function store(StoreRequest $request)
    {
        DB::beginTransaction();
        try {
            $date = $request->date ?? now();
            $data = new InventoryTransactions();
            $data->company_origin_id = $request->user()->company_id;
            $data->warehouse_origin_id = $request->warehouse_origin_id;
            $data->company_destination_id = $request->company_destination_id;
            $data->warehouse_destination_id = $request->warehouse_destination_id;
            $data->number = NotaGenerator::generate('inv_transactions', 'number', 7, $date)->addPrefix('STT', '-')->getResult();
            $data->type = 'transfer';
            $data->date = $date;
            $data->note = $request->note;
            $data->save();

            $listDetail = [];
            foreach ($request->list_item_id ?? [] as $key => $item) {
                $qty = deformatCurrency($request->list_qty[$key] ?? 0);
                $unitId = $request->list_unit_id[$key];
                $unitPrice = deformatCurrency($request->unit_price[$key]);
                
                array_push($listDetail, [
                    'inv_transaction_id' => $data->id,
                    'item_id' => $item,
                    'note' => $request->list_note[$key] ?? null,
                    'qty' => $qty,
                    'unit_id' => $unitId,
                    'unit_price' => $unitPrice,
                ]);
            }

            InventoryTransactionDetails::insert($listDetail);

            DB::commit();
            $this->loggerHelper->logSuccess($request->getRequestUri(), $request->user(), $request->all());
            return $this->responseFormatter->successResponse('Data berhasil disimpan', $data);
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
            $data = InventoryTransactions::where('type', 'transfer')
                ->with('destination', 'companyDestination', 'financeAccount', 'origin', 'companyOrigin')
                ->with(['details' => function ($q) {
                    $q->with(['item' => function ($q) {
                        $q->with('itemType');
                        $q->with('brand');
                        $q->with('itemUnit');
                    }]);
                    $q->with('unit');
                }])
                ->findOrFail($id);

            $this->loggerHelper->logSuccess($request->getRequestUri(), $request->user(), $request->all());
            return $this->responseFormatter->successResponse('', $data);
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
            $data = InventoryTransactions::findOrFail($id);
            $data->company_destination_id = $request->company_destination_id;
            $data->warehouse_destination_id = $request->warehouse_destination_id;
            if ($request->date) {
                $data->date = $request->date;
            }
            $data->note = $request->note;
            $data->update();

            // delete details
            InventoryTransactionDetails::where('inv_transaction_id', $data->id)->delete();

            $listDetail = [];
            foreach ($request->list_item_id ?? [] as $key => $item) {
                $qty = deformatCurrency($request->list_qty[$key] ?? 0);
                $unitId = $request->list_unit_id[$key];
                $unitPrice = deformatCurrency($request->unit_price[$key]);

                array_push($listDetail, [
                    'inv_transaction_id' => $data->id,
                    'item_id' => $item,
                    'note' => $request->list_note[$key] ?? null,
                    'qty' => $qty,
                    'unit_id' => $unitId,
                    'unit_price' => $unitPrice,
                ]);
            }
            InventoryTransactionDetails::insert($listDetail);

            DB::commit();
            $this->loggerHelper->logSuccess($request->getRequestUri(), $request->user(), $request->all());
            return $this->responseFormatter->successResponse('Data berhasil diperbarui', $data);
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
            InventoryTransactions::findOrFail($id)->delete();

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
            $data = InventoryTransactions::with('details')->findOrFail($id);
            if ($data->status != 'waiting') {
                throw new Exception("Data sudah diproses, tidak dapat diubah", Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            if ($request->status == 'reject') {
                $data->status = 'rejected';
            } else {
                $data->status = 'approved';
                // insert mutation
                $mutation = new StockMutation;
                foreach ($data->details as $detail) {
                    $itemId = $detail->item_id;
                    $qty = $detail->qty;
                    $cogm = 0;

                    $mutationOut = $mutation->mutationOut($itemId, $data->warehouse_origin_id, abs($qty), now(), $data->company_destination_id,  $data->number, "Outgoing Goods");
                    if ($mutationOut->getStatus() != 'success') {
                        throw new Exception($mutationOut->getErrorMessage(), Response::HTTP_UNPROCESSABLE_ENTITY);
                    }
                    $data->stockMutations()->save($mutationOut->getData()->model);
                    $cogm = $mutationOut->getData()->cogm;
                }
            }

            // update data
            $data->updated_by = $request->user()->id;
            $data->save();

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
