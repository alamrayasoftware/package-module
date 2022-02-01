<?php

namespace __defaultNamespace__\Controllers;

use App\Http\Controllers\Controller;
use __defaultNamespace__\Models\MCompany;
use __defaultNamespace__\Requests\StoreRequest;
use __defaultNamespace__\Requests\UpdateRequest;
use App\Helpers\LoggerHelper;
use App\Helpers\ResponseFormatter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CompanyController extends Controller
{
    public $responseFormatter = null;

    public function __construct()
    {
        $this->responseFormatter = new ResponseFormatter();
        $this->loggerHelper = new LoggerHelper();
    }

    public function index(Request $request)
    {
        $listCompanies = MCompany::orderBy('name')->get();

        $this->loggerHelper->logSuccess('index', $request->user()->company_id, $request->user()->user_id, $request->all());
        return $this->responseFormatter->successResponse('', $listCompanies);
    }

    public function store(StoreRequest $request)
    {
        DB::beginTransaction();
        try {
            $newData = new MCompany();
            $newData->name = $request->name;
            $newData->company_parent_id = $request->company_parent_id;
            $newData->code = $request->code;
            $newData->phone = $request->phone;
            $newData->email = $request->email;
            $newData->address = $request->address;
            // insert image and get the path
            $imagePath = $request->image;
            $newData->image_path = $imagePath;
            $newData->province_id = $request->province_id;
            $newData->city_id = $request->city_id;
            $newData->district_id = $request->district_id;
            $newData->type = $request->type;
            $newData->ownership_type = $request->ownership_type;
            $newData->account_first_period = now()->parse($request->account_first_period ?? now());
            $newData->save();

            DB::commit();
            $this->loggerHelper->logSuccess('store', $request->user()->company_id, $request->user()->user_id, $request->all());
            return $this->responseFormatter->successResponse('', $newData);
        } catch (\Throwable $th) {
            DB::rollBack();
            $this->loggerHelper->logError($th, $request->user()->company_id, $request->user()->user_id, $request->all());
            return $this->responseFormatter->errorResponse($th);
        }
    }

    public function show(Request $request, $id)
    {
        try {
            $company = MCompany::findOrFail($id);

            $this->loggerHelper->logSuccess('store', $request->user()->company_id, $request->user()->user_id, $request->all());
            return $this->responseFormatter->successResponse('', $company);
        } catch (\Throwable $th) {
            $this->loggerHelper->logError($th, $request->user()->company_id, $request->user()->user_id, $request->all());
            return $this->responseFormatter->errorResponse($th);
        }
    }

    public function update(UpdateRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $data = MCompany::findOrFail($id);
            $data->name = $request->name;
            $data->company_parent_id = $request->company_parent_id;
            $data->code = $request->code;
            $data->phone = $request->phone;
            $data->email = $request->email;
            $data->address = $request->address;
            // insert image and get the path
            $imagePath = $request->image;
            $data->image_path = $imagePath;
            $data->province_id = $request->province_id;
            $data->city_id = $request->city_id;
            $data->district_id = $request->district_id;
            $data->type = $request->type;
            $data->ownership_type = $request->ownership_type;
            $data->account_first_period = now()->parse($request->account_first_period ?? now());
            $data->save();

            DB::commit();
            $this->loggerHelper->logSuccess('update', $request->user()->company_id, $request->user()->user_id, $request->all());
            return $this->responseFormatter->successResponse('', $data);
        } catch (\Throwable $th) {
            DB::rollBack();
            $this->loggerHelper->logError($th, $request->user()->company_id, $request->user()->user_id, $request->all());
            return $this->responseFormatter->errorResponse($th);
        }
    }

    public function destroy(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            MCompany::destroy($id);

            DB::commit();
            $this->loggerHelper->logSuccess('delete', $request->user()->company_id, $request->user()->user_id, $request->all());
            return $this->responseFormatter->successResponse();
        } catch (\Throwable $th) {
            DB::rollBack();
            $this->loggerHelper->logError($th, $request->user()->company_id, $request->user()->user_id, $request->all());
            return $this->responseFormatter->errorResponse($th);
        }
    }
}