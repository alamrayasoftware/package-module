<?php

namespace __defaultNamespace__\Controllers;

use App\Http\Controllers\Controller;
use __defaultNamespace__\Models\__childModuleName__;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class __childModuleName__Controller extends Controller
{
    public function index(Request $request)
    {
        // your code here

        Log::info('get-datas', ['user' => $request->user() ?? null]);
        return response()->json([
            'status' => 'success',
            'data' => ''
        ]);
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            // your code here

            DB::commit();
            Log::info('store', ['user' => $request->user() ?? null]);
            return response()->json([
                'status' => 'success',
                'data' => ''
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('store', [
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
            // your code here

            Log::info('show-data', ['user' => $request->user() ?? null]);
            return response()->json([
                'status' => 'success',
                'data' => ''
            ]);
        } catch (\Throwable $th) {
            Log::error('show-data', [
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
            // your code here

            DB::commit();
            Log::info('update-data', ['user' => $request->user() ?? null]);
            return response()->json([
                'status' => 'success',
                'data' => ''
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('update-data', [
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
            // your code here

            DB::commit();
            Log::info('delete-data', ['user' => $request->user() ?? null]);
            return response()->json([
                'status' => 'success',
                'data' => ''
            ]);
        } catch (\Throwable $th) {
            Log::error('delete-data', [
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