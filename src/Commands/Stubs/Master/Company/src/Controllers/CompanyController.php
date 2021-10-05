<?php

namespace __defaultNamespace__Controllers;

use App\Http\Controllers\Controller;
use __defaultNamespace__Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CompanyController extends Controller
{
    public function index(Request $request)
    {
        $listCompanies = Company::orderBy('name')
            ->get();

        Log::info('get-list-companies', ['user' => $request->user() ?? null]);
        return response()->json([
            'status' => 'success',
            'data' => $listCompanies
        ]);
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $newData = new Company();
            $newData->name = $request->name ?? '-';
            $newData->company_parent_id = $request->company_parent_id;
            $newData->code = $request->code;
            $newData->phone = $request->phone;
            $newData->email = $request->email;
            $newData->address = $request->address;
            $newData->province_id = $request->province_id;
            $newData->city_id = $request->city_id;
            $newData->district_id = $request->district_id;
            $newData->account_first_period = now()->parse($request->account_first_period ?? now());
            $newData->save();

            DB::commit();
            Log::info('store-new-company', ['user' => $request->user() ?? null]);
            return response()->json([
                'status' => 'success',
                'data' => $newData
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('store-new-company', [
                'user' => $request->user() ?? null,
                'context' => $th->getMessage()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 400);
        }
    }

    public function show(Request $request, $id)
    {
        try {
            $company = Company::find($id);

            Log::info('show-company', ['user' => $request->user() ?? null]);
            return response()->json([
                'status' => 'success',
                'data' => $company
            ]);
        } catch (\Throwable $th) {
            Log::error('show-company', [
                'user' => $request->user() ?? null,
                'context' => $th->getMessage()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 400);
        }
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $data = Company::where('id', $id)
                ->firstOrFail();
            $data->name = $request->name;
            $data->company_parent_id = $request->company_parent_id;
            $data->code = $request->code;
            $data->phone = $request->phone;
            $data->email = $request->email;
            $data->address = $request->address;
            $data->province_id = $request->province_id;
            $data->city_id = $request->city_id;
            $data->district_id = $request->district_id;
            $data->account_first_period = now()->parse($request->account_first_period ?? now());
            $data->save();

            DB::commit();
            Log::info('update-company', ['user' => $request->user() ?? null]);
            return response()->json([
                'status' => 'success',
                'data' => $data
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('update-company', [
                'user' => $request->user() ?? null,
                'context' => $th->getMessage()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 400);
        }
    }

    public function destroy(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $data = Company::where('id', $id)->delete();

            DB::commit();
            Log::info('delete-company', ['user' => $request->user() ?? null]);
            return response()->json([
                'status' => 'success',
                'data' => $data
            ]);
        } catch (\Throwable $th) {
            Log::error('delete-company', [
                'user' => $request->user() ?? null,
                'context' => $th->getMessage()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 400);
        }
    }
}