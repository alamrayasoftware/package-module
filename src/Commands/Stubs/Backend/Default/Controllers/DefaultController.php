<?php

namespace __defaultNamespace__\Controllers;

use App\Http\Controllers\Controller;
use __defaultNamespace__\Requests\StoreRequest;
use __defaultNamespace__\Requests\UpdateRequest;
use App\Helpers\ResponseFormatter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class __childModuleName__Controller extends Controller
{
    public $responseFormatter = null;

    public function __construct()
    {
        $this->responseFormatter = new ResponseFormatter();
    }

    public function index(Request $request)
    {
        // your code here
        $datas = [];

        Log::info('get-datas', ['user' => $request->user() ?? null]);
        return $this->responseFormatter->successResponse('', $datas);
    }

    public function store(StoreRequest $request)
    {
        DB::beginTransaction();
        try {
            // your code here
            $newData = null;

            DB::commit();
            Log::info('store', ['user' => $request->user() ?? null]);
            return $this->responseFormatter->successResponse('', $newData);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('store', [
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
            // your code here
            $data = null;

            Log::info('show-data', ['user' => $request->user() ?? null]);
            return $this->responseFormatter->successResponse('', $data);
        } catch (\Throwable $th) {
            Log::error('show-data', [
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
            // your code here
            $data = null;

            DB::commit();
            Log::info('update-data', ['user' => $request->user() ?? null]);
            return $this->responseFormatter->successResponse('', $data);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('update-data', [
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
            // your code here

            DB::commit();
            Log::info('delete-data', ['user' => $request->user() ?? null]);
            return $this->responseFormatter->successResponse();
        } catch (\Throwable $th) {
            Log::error('delete-data', [
                'user' => $request->user() ?? null,
                'context' => $th->getMessage(),
                'line' => $th->getLine()
            ]);
            return $this->responseFormatter->errorResponse($th);
        }
    }
}