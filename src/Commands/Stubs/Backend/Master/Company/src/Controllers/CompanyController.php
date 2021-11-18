<?php

namespace __defaultNamespace__\Controllers;

use App\Http\Controllers\Controller;
use __defaultNamespace__\Models\Company;
use __defaultNamespace__\Requests\StoreRequest;
use __defaultNamespace__\Requests\UpdateRequest;
use App\Helpers\ResponseFormatter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CompanyController extends Controller
{
    public $responseFormatter = null;

    public function __construct()
    {
        $this->responseFormatter = new ResponseFormatter();
    }

    public function index(Request $request)
    {
        $listCompanies = Company::orderBy('name')->get();

        Log::info('get-list-companies', ['user' => $request->user() ?? null]);
        return $this->responseFormatter->successResponse('', $listCompanies);
    }

    public function store(StoreRequest $request)
    {
        DB::beginTransaction();
        try {
            $newData = new Company();
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
            Log::info('store-new-company', ['user' => $request->user() ?? null]);
            return $this->responseFormatter->successResponse('', $newData);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('store-new-company', [
                'user' => $request->user() ?? null,
                'context' => $th->getMessage(),
                'line' => $th->getLine()
            ]);
            return $this->responseFormatter->errorResponse($th);
        }
    }

    public function show(Request $request, $id)
    {
        try {
            $company = Company::findOrFail($id);

            Log::info('show-company', ['user' => $request->user() ?? null]);
            return $this->responseFormatter->successResponse('', $company);
        } catch (\Throwable $th) {
            Log::error('show-company', [
                'user' => $request->user() ?? null,
                'context' => $th->getMessage(),
                'line' => $th->getLine()
            ]);
            return $this->responseFormatter->errorResponse($th);
        }
    }

    public function update(UpdateRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $data = Company::findOrFail($id);
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
            Log::info('update-company', ['user' => $request->user() ?? null]);
            return $this->responseFormatter->successResponse('', $data);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('update-company', [
                'user' => $request->user() ?? null,
                'context' => $th->getMessage(),
                'line' => $th->getLine()
            ]);
            return $this->responseFormatter->errorResponse($th);
        }
    }

    public function destroy(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            Company::destroy($id);

            DB::commit();
            Log::info('delete-company', ['user' => $request->user() ?? null]);
            return $this->responseFormatter->successResponse();
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('delete-company', [
                'user' => $request->user() ?? null,
                'context' => $th->getMessage(),
                'line' => $th->getLine()
            ]);
            return $this->responseFormatter->errorResponse($th);
        }
    }
}