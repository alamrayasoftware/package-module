<?php

namespace __defaultNamespace__\Controllers;

use __defaultNamespace__\Models\MUser;
use __defaultNamespace__\Requests\LoginRequest;
use __defaultNamespace__\Requests\RegisterRequest;
use App\Http\Controllers\Controller;
use App\Helpers\LoggerHelper;
use App\Helpers\ResponseFormatter;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public $responseFormatter, $loggerHelper;

    public function __construct()
    {
        $this->responseFormatter = new ResponseFormatter();
        $this->loggerHelper = new LoggerHelper();
    }

    public function register(RegisterRequest $request)
    {
        DB::beginTransaction();
        try {
            $password = $request->password ?? Str::random(8);
            $user = new MUser();
            $user->email = $request->email;
            $user->username = $request->username;
            $user->password = Hash::make($password);
            $user->save();
            
            // TODO : send mail to registered email

            DB::commit();
            $this->loggerHelper->logSuccess('register', null, $user->id, $request->all());
            return response()->json([
                'status' => 'success',
                'message' => 'register success',
                'data' => [
                    'user' => $user,
                ],
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            $this->loggerHelper->logError($th, null, null, $request->all());
            return $this->responseFormatter->errorResponse($th);
        }
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
            $this->loggerHelper->logError($th, null, null, $request->all());
            return $this->responseFormatter->errorResponse($th);
        }
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();

            $this->loggerHelper->logSuccess('logout', null, $request->user()->id, $request->all());
            return response()->json([
                'status' => 'success',
                'message' => 'Logout success',
                'data' => [
                    'user' => $request->user(),
                ],
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            $this->loggerHelper->logError($th, null, null, $request->all());
            return $this->responseFormatter->errorResponse($th);
        }
    }
}
