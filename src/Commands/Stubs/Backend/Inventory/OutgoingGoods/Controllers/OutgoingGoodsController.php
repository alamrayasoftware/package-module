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

class OutgoingGoodsController extends Controller
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
        $datas = InventoryTransactions::whereBetween('date', [$startDate, $endDate]);

        // filter by company-origin-id
        if ($request->company_origin_id) {
            $datas = $datas->whereCompanyOriginId($request->company_origin_id);
        }
        // filter by company-destination-id
        if ($request->company_destination_id) {
            $datas = $datas->whereCompanyDestinationId($request->company_destination_id);
        }
        // filter by warehouse-origin-id
        if ($request->warehouse_origin_id) {
            $datas = $datas->whereWarehouseOriginId($request->warehouse_origin_id);
        }
        // filter by warehouse-destination-id
        if ($request->warehouse_destination_id) {
            $datas = $datas->whereWarehouseDestinationId($request->warehouse_destination_id);
        }

        $datas = $datas->with(['companyOrigin' => function ($q) {
                $q->select('id', 'name');
            }])
            ->with(['warehouseOrigin' => function ($q) {
                $q->select('id', 'name');
            }])
            ->with(['companyDestination' => function ($q) {
                $q->select('id', 'name');
            }])
            ->with(['warehouseDestination' => function ($q) {
                $q->select('id', 'name');
            }])
            ->with(['createdBy' => function ($q) {
                $q->select('id', 'first_name', 'last_name');
            }])
            ->with(['updatedBy' => function ($q) {
                $q->select('id', 'first_name', 'last_name');
            }])
            ->orderByDesc('number')
            ->get();

        $this->loggerHelper->logSuccess($request->getRequestUri(), $request->user(), $request->all());
        return $this->responseFormatter->successResponse('', $datas);
    }

    // store new data
    public function store(StoreRequest $request)
    {
        DB::beginTransaction();
        try {
            $date = now()->parse($request->date ?? now());
            $number = $request->number ?? NotaGenerator::generate('inv_transactions', 'number', 5, $date)->addPrefix('INC-TRANS', '/')->getResult();

            // insert new data
            $data = new InventoryTransactions();
            $data->company_origin_id = $request->company_origin_id;
            $data->warehouse_origin_id = $request->warehouse_origin_id;
            $data->company_destination_id = $request->company_destination_id;
            $data->warehouse_destination_id = $request->warehouse_destination_id;
            $data->number = $number;
            $data->type = 'outgoing-goods';
            $data->date = $date;
            $data->note = $request->note;
            $data->created_by = $request->user()->id;
            $data->updated_by = $request->user()->id;
            $data->save();

            // insert details
            $listDetail = [];
            foreach ($request->list_item_id ?? [] as $key => $itemId) {
                array_push($listDetail, [
                    'inv_transaction_id' => $data->id,
                    'item_id' => $itemId,
                    'qty' => deformatCurrency($request->list_qty[$key]),
                    'unit_id' => $request->list_unit_id[$key] ?? null,
                    'unit_price' => deformatCurrency($request->list_unit_price[$key] ?? 0),
                    'note' => $request->list_note[$key] ?? null,
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
            $data = InventoryTransactions::with(
                    'companyOrigin', 
                    'warehouseOrigin', 
                    'companyDestination', 
                    'warehouseDestination', 
                    'createdBy', 
                    'updatedBy'
                )
                ->with(['details' => function ($q) {
                    $q->with('item')
                        ->with('unit');
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
            // get data by id
            $data = InventoryTransactions::findOrFail($id);
            // validate is status
            if ($data->status != 'waiting') {
                throw new Exception("Data sudah diproses, tidak dapat diubah", Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            // update data
            $data->note = $request->note;
            $data->updated_by = $request->user()->id;
            $data->update();
            // delete current details
            InventoryTransactionDetails::where('inv_transaction_id', $data->id)->delete();
            // insert new details
            $listDetail = [];
            foreach ($request->list_item_id ?? [] as $key => $itemId) {
                array_push($listDetail, [
                    'inv_transaction_id' => $data->id,
                    'item_id' => $itemId,
                    'qty' => deformatCurrency($request->list_qty[$key]),
                    'unit_id' => $request->list_unit_id[$key] ?? null,
                    'unit_price' => deformatCurrency($request->list_unit_price[$key] ?? 0),
                    'note' => $request->list_note[$key] ?? null,
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
            // get data by id
            $data = InventoryTransactions::findOrFail($id);
            if ($data->status != 'waiting') {
                throw new Exception("Data sudah diproses, tidak dapat diubah", Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            $data->delete();

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
