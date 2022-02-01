<?php

namespace __defaultNamespace__\Controllers;

use __defaultNamespace__\Models\MUser;
use __defaultNamespace__\Models\Opname;
use __defaultNamespace__\Models\OpnameDetail;
use __defaultNamespace__\Models\Related\Item;
use __defaultNamespace__\Requests\LoginRequest;
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
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public $responseFormatter, $loggerHelper;

    public function __construct()
    {
        $this->responseFormatter = new ResponseFormatter();
        $this->loggerHelper = new LoggerHelper();
    }


    public function login(LoginRequest $request)
    {
        try {
            $user = MUser::where('email', $request->email)->first();
            if (!$user) {
                throw new Exception("User tidak ditemukan", 400);
            }
            if (!Hash::check($request->password, $user->password)) {
                throw new Exception("Password salah !", 400);
            }

            $credential = $user->createToken('my-token')->plainTextToken;

            $this->loggerHelper->logSuccess('login', null, $user->id, $request->all());
            return response()->json([
                'status' => 'success',
                'message' => 'Login success',
                'data' => [
                    'user' => $user,
                    'credential' => $credential,
                ],
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            $this->loggerHelper->logError($th, $request->user()->company_id, $request->user()->user_id, $request->all());
            return $this->responseFormatter->errorResponse($th);
        }
    }

}
