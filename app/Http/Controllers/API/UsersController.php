<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\Console\Input\Input;
use App\Http\Controllers\API\BaseController;
use App\Http\Controllers\API\AuthOtpController as AuthOtpController;

class UsersController extends BaseController
{
    /** 
     * Get All Users
     * 
     * @return \Illuminate\Http\Response
    */

    /** get all users */
    public function index(){
        try {
            $users = User::all();
            if (!$users) {
                return $this->sendError('Error!', ['error' => 'Data tidak ditemukan!']);
            }
            return $this->sendResponse($users, 'Displaying all users data');
        } catch (\Throwable $th) {
            return $this->sendError('Error!', ['error' => $th]);
        }
        
    }

    /** 
     * Get User By Id
     * 
     * 
     * @return \Illuminate\Http\Response
    */

    public function getUserById(int $id)
    {
        try {
            $user = User::where('id', $id)->first();
            if (!$user) {
                return $this->sendError('Error!', ['error' => 'Data tidak ditemukan!']);
            }
            return $this->sendResponse($user, 'User detail by Id');
        } catch (\Throwable $th) {
            return $this->sendError('Error!', ['error' => $th]);
        }
        
    }

    /**
     * Register API
     * 
     * @return \Illuminate\Http\Response
     */

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
        // dd($request->email);
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
    
    /**
     * Update User
     * 
     * @return \Illuminate\Http\Response
     */

    public function updateUser(Request $request)
    {
        try {
            $id = $request->id;
            $name = $request->name;
            $email = $request->email;
            $old_password = $request->old_password;
            $new_password = $request->new_password;
            $confirm_new_password = $request->confirm_new_password;
            $phone = $request->phone;
            $checkUser = User::where('id', $id)->first();
            if (!$checkUser) {
                return $this->sendError('Error!', ['error' => 'Data tidak ditemukan!']);
            }
            $checkPassword = Auth::attempt(['id' => $id, 'password' => $old_password]);
            // $checkPassword = User::where('id', $id)->where('password', $old_password)->first();
            // dd($checkPassword);exit();

            if ($checkPassword == NULL) {
                return $this->sendError('Error!', ['error' => 'Password yang anda masukkan salah!']);
            }
            if ($new_password == NULL) {
                $validator = Validator::make($request->all(),[
                    'name' => 'required',
                    'email' => 'required|email',
                    'phone' => ['required','numeric'],
                ]);
                $data = array(
                    'name' => $name,
                    'email' => $email,
                    'phone' => $phone,
                );
            } elseif ($new_password != NULL) {
                $validator = Validator::make($request->all(),[
                    'name' => 'required',
                    'email' => 'required|email',
                    'new_password' => 'required',
                    'confirm_new_password' => 'required|same:new_password',
                    'phone' => ['required','numeric'],
                ]);
                $data = array(
                    'name' => $name,
                    'email' => $email,
                    'phone' => $phone,
                    'password' => bcrypt($new_password),
                );
            }

            if ($validator->fails()) {
                return $this->sendError('Error!', $validator->errors());
            }

            // dd($data['name']);exit();

            $updateDataUser = User::where('id', $request->id)->update($data);
            $tokenMsg = Str::random(15);
            $success['token'] = $tokenMsg;
            $success['message'] = "Data berhasil diupdate!";
            $success['data'] = $updateDataUser;
            return $this->sendResponse($success, 'Update data');

        } catch (\Throwable $th) {
            return $this->sendError('Error!', $th);
        }
    }
}
