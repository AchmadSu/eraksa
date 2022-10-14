<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\API\BaseController;
use App\Models\User;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\Console\Input\Input;
use App\Http\Controllers\API\AuthOtpController as AuthOtpController;

class UsersController extends BaseController
{
    /**
     * Register API
     * 
     * @return \Illuminate\Http\Response
     */

    /** get all users */
    public function index(){
        $users = User::all();
        return $this->sendResponse($users, 'Displaying all users data');
    }

    public function register(Request $request){
        try {
            $validator = Validator::make($request->all(),[
                'name' => 'required',
                'email' => 'required|email',
                'password' => 'required',
                'confirm_pass' => 'required|same:password',
                'phone' => ['required','numeric'],
            ]);
    
            if ($validator->fails()){
                return $this->sendError('Validator Error.', $validator->errors());
            }
    
            $input = $request->all();
    
            if(User::where('email', $input['email'])->first()){
                return $this->sendError('Data sudah ada!', ['error'=>'Email sudah terdaftar, silakan login!']);
            }
    
            // $input['otp'] = rand(1000, 9999);
            $input['password'] = bcrypt($input['password']);
            $user = User::create($input);
            $success['token'] = $user->createToken('MyApp')->plainTextToken;
            $success['name'] = $user->name;
    
            return $this->sendResponse($success, 'User ditambahkan!');    
        } catch (\Throwable $th) {
            return $this->sendError('Error!'.$th, ['error'=>$th]);
        } 
    }

    /**
     * Login API
     * 
     * @return \Illuminate\Http\Response
     */

    public function login(Request $request){
        try {
            if(Auth::attempt(['email' => $request->email, 'password' => $request->password])){
                $user = Auth::user();
                // json_encode($user->id);
                $success['token'] = $user->createToken('MyApp')->plainTextToken;
                $success['name'] = $user->name;
                $success['id'] = $user->id;
                return $this->sendResponse($success, 'Anda berhasil masuk!');
    
                // return Route::resource('user', UserController::class);
            } else {
                return $this->sendError('Unauthorised.', ['error'=>'Data tidak ditemukan, silakan daftar!']);
            }
        } catch (\Throwable $th) {
            return $this->sendError('Error!'.$th, ['error' => $th]);
        }
        
    }   
}
