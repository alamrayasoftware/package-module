<?php

namespace __defaultNamespace__\Controllers;

use App\Http\Controllers\Controller;
use __defaultNamespace__\Requests\StoreRequest;
use __defaultNamespace__\Requests\UpdateRequest;
use App\Helpers\LoggerHelper;
use App\Helpers\ResponseFormatter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class __childModuleName__Controller extends Controller
{
    public $responseFormatter, $loggerHelper;

    public function __construct()
    {
        $this->responseFormatter = new ResponseFormatter();
        $this->loggerHelper = new LoggerHelper();
    }

    public function index(Request $request)
    {
        // your code here
        $datas = [];

        $this->loggerHelper->logSuccess('index', $request->user(), $request->all());
        return $this->responseFormatter->successResponse('', $datas);
    }

    public function store(StoreRequest $request)
    {
        DB::beginTransaction();
        try {
            // your code here
            $newData = null;

            DB::commit();
            $this->loggerHelper->logSuccess('store', $request->user(), $request->all());
            return $this->responseFormatter->successResponse('', $newData);
        } catch (\Throwable $th) {
            DB::rollBack();
            $this->loggerHelper->logError($th, $request->user(), $request->all());
            return $this->responseFormatter->errorResponse($th);
        }
    }

    public function show(Request $request, $id)
    {
        try {
            // your code here
            $data = null;

            $this->loggerHelper->logSuccess('show', $request->user(), $request->all());
            return $this->responseFormatter->successResponse('', $data);
        } catch (\Throwable $th) {
            $this->loggerHelper->logError($th, $request->user(), $request->all());
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
            $this->loggerHelper->logSuccess('update', $request->user(), $request->all());
            return $this->responseFormatter->successResponse('', $data);
        } catch (\Throwable $th) {
            DB::rollBack();
            $this->loggerHelper->logError($th, $request->user(), $request->all());
            return $this->responseFormatter->errorResponse($th);
        }
    }

    public function destroy(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            // your code here

            DB::commit();
            $this->loggerHelper->logSuccess('delete', $request->user(), $request->all());
            return $this->responseFormatter->successResponse();
        } catch (\Throwable $th) {
            DB::rollBack();
            $this->loggerHelper->logError($th, $request->user(), $request->all());
            return $this->responseFormatter->errorResponse($th);
        }
    }
}