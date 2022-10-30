<?php

namespace App\Http\Controllers\API;

use DB;
use Throwable;
use Carbon\Carbon;
use App\Models\User;
use Twilio\Rest\Client;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use App\Models\VerificationCodes;
use Spatie\Permission\Model\Roles;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\Console\Input\Input;
use App\Http\Controllers\API\BaseController;
use App\Http\Controllers\API\AuthOtpController as AuthOtpController;
use Illuminate\Support\Facades\Session;

class UsersController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
        //  $this->middleware('role:role-list|role-create|role-edit|role-delete', ['only' => ['index','store']]);
         $this->middleware('permission:get all user', ['only' => ['index']]);
         $this->middleware('permission:get all user trash', ['only' => ['trash']]);
         $this->middleware('permission:get user by id', ['only' => ['show']]);
         $this->middleware('permission:delete user', ['only' => ['delete']]);
         $this->middleware('permission:restore user', ['only' => ['restore']]);
    }

    /** ATTRIVE USER DATA */
    /** 
     * Get All Users
     * 
     * @return \Illuminate\Http\Response
    */

    public function index(){
        try {
            // dd(Auth::user());
            if (Auth::user()) {
                // dd(Auth::user()->name);
                $users = User::all();
                if (!$users) {
                    return $this->sendError('Error!', ['error' => 'Data tidak ditemukan!']);
                }
                return $this->sendResponse($users, 'Displaying all users data');
            } else {
                return $this->sendError('Account is not login.', ['error' => 'Silakan login terlebih dulu!']);
            }
        } catch (\Throwable $th) {
            return $this->sendError('Error!', ['error' => $th]);
        }
        
    }

    /** 
     * Get All Users
     * 
     * @return \Illuminate\Http\Response
    */

    public function getSuperAdmin(){
        try {
            // dd(Auth::user());
            if (Auth::user()) {
                // dd(Auth::user()->name);
                $users = User::whereHas(
                    'roles', function($q){
                        $q->where('name', 'Super-Admin');
                    }
                )->get();
                if (!$users) {
                    return $this->sendError('Error!', ['error' => 'Data tidak ditemukan!']);
                }
                return $this->sendResponse($users, 'Displaying all users data');
            } else {
                return $this->sendError('Account is not login.', ['error' => 'Silakan login terlebih dulu!']);
            }
        } catch (\Throwable $th) {
            return $this->sendError('Error!', ['error' => $th]);
        }
        
    }

    /** 
     * Get All Users in Trash
     * 
     * @return \Illuminate\Http\Response
    */

    public function trash(){
        try {
            // dd(Auth::user());
            if (Auth::user()) {
                // dd(Auth::user());
                // \DB::enableQueryLog();
                $users = User::onlyTrashed()->get();
                // var_dump($users);exit();
                // $users = DB::table('users')->whereNotNull('deleted_at')->get();
                // dd(\DB::getQueryLog());
                // var_dump($users->isEmpty());exit();
                if ($users->isEmpty()) {
                    return $this->sendError('Error!', ['error' => 'Data tidak ditemukan!']);
                }
                return $this->sendResponse($users, 'Displaying all trash data');

            } else {
                return $this->sendError('Account is not login.', ['error' => 'Silakan login terlebih dulu!']);
            }
        } catch (\Throwable $th) {
            return $this->sendError('Error!', ['error' => $th]);
        }
    }

    /** 
     * Get User By Id
     * 
     * @param Int $id
     * @return \Illuminate\Http\Response
    */

    public function show(Int $id)
    {
        try {
            if (Auth::user()) {
                // \DB::enableQueryLog();
                $user = User::where('id', $id)->first();
                // dd(\DB::getQueryLog());
                if (!$user) {
                    return $this->sendError('Error!', ['error' => 'Data tidak ditemukan!']);
                }
                return $this->sendResponse($user, 'User detail by Id');
            } else {
                return $this->sendError('Account is not login.', ['error' => 'Silakan login terlebih dulu!']);
            }
        } catch (\Throwable $th) {
            return $this->sendError('Error!', ['error' => $th]);
        }
        
    }

    /** LOGIN AND LOGOUT */

    /**
     * Login API
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */

    public function login(Request $request){
        try {
            if (Auth::user()) {
                return $this->sendError('Account is already login.', ['error' => 'Akun anda sedang aktif!']);
            }
            // $checkUserDeleted = User::where('email', $request->email)->where('deleted_at', NULL)->first();
            // if (!$checkUserDeleted) {
            //     return $this->sendError('Account deleted.', ['error' => 'Akun anda sudah dihapus, silakan hubungi admin!']);
            // }

            // $checkUserStatus = User::where('email', $request->email)->where('status', '0')->first();
            // // dd($checkUserStatus);exit();
            // if ($checkUserStatus) {
            //     return $this->sendError('Invalid OTP', ['error' => 'OTP anda belum tervalidasi, silakan masukkan kode OTP dengan benar!']);
            // }

            if(Auth::attempt(['email' => $request->email, 'password' => $request->password])){
                $user = Auth::user();
                $success['token'] = $user->createToken('MyApp')->accessToken;
                $success['name'] = $user->name;
                return $this->sendResponse($success, 'Anda berhasil masuk!');
    
                // return Route::resource('user', UserController::class);
            } else {
                return $this->sendError('Unauthorised.', ['error'=> 'Email atau Password salah!']);
            }
        } catch (\Throwable $th) {
            return $this->sendError('Error!'.$th, ['error' => $th]);
        }
        
    }

    /**
     * Logout API.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        $success['msg'] = "Logged out!";
        $success['token'] = Str::random(15);
        return $this->sendResponse($success, 'Anda berhasil keluar!');
    }

    /** CRUD USER */
    
    /**
     * Register API
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */

    public function register(Request $request){
        try {
            if (!Auth::user()) {
                $validator = Validator::make($request->all(),[
                    'name' => 'required',
                    'email' => 'required|email|unique:users,phone',
                    'password' => 'required',
                    'confirm_pass' => 'required|same:password',
                    'phone' => 'required|numeric|unique:users,phone',
                ]);
        
                if ($validator->fails()){
                    return $this->sendError('Validator Error.', $validator->errors());
                }
        
                $input = $request->all();
                // \DB::enableQueryLog();
                $checkEmail = User::where('email', $input['email'])->first();
                // dd(\DB::getQueryLog());
                if($checkEmail){
                    return $this->sendError('Data sudah ada!', ['error'=>'Email sudah terdaftar, silakan login!']);
                }
        
                // $input['otp'] = rand(1000, 9999);
                $input['password'] = bcrypt($input['password']);
                $user = User::create($input);
                $success['token'] = $user->createToken('MyApp')->plainTextToken;
                $success['name'] = $user->name;
        
                return $this->sendResponse($success, 'User ditambahkan!');
            } else {
                return $this->sendError('Account is already login.', ['error' => 'Akun anda sedang aktif!']);
            }    
        } catch (\Throwable $th) {
            return $this->sendError('Error!'.$th, ['error'=>$th]);
        } 
    }

    /**
     * Update User
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    
    public function update(Request $request)
    {
        try {
            if (Auth::user()) {
                // dd(Auth::user()->name);
                $id = $request->id;
                $updateDataUser = User::find($id);
                
                $name = $request->name;
                $new_email = $request->new_email;
                $old_password = $request->old_password;
                $new_password = $request->new_password;
                $confirm_new_password = $request->confirm_new_password;
                $phone = $request->phone;
                $new_phone = $request->new_phone;
                
                $checkUser = User::where('id', $id)->first();
                if (!$checkUser) {
                    return $this->sendError('Error!', ['error' => 'Data user tidak ditemukan!']);
                }
                
                $checkPassword = Auth::attempt(['id' => $id, 'password' => $old_password]);
                $checkPhone = User::where('id', $id)->where('phone', $phone)->first();
                // dd($checkPhone);

                if (!$checkPassword) {
                    return $this->sendError('Error!', $credentials = ['Password lama yang anda masukkan salah!']);
                }

                if ($new_password == NULL) {
                    $validator = Validator::make($request->all(),[
                        'name' => 'required',
                        'new_email' => 'email|unique:users,email',
                        'new_phone' => 'numeric|unique:users,phone',
                    ]);
                    
                    if ($new_email != NULL) {
                        $updateDataUser->email = $new_email;
                    }

                } elseif ($new_password != NULL) {
                    $validator = Validator::make($request->all(),[
                        'name' => 'required',
                        'new_email' => 'email|unique:users,email',
                        'new_password' => 'required',
                        'confirm_new_password' => 'required|same:new_password',
                        'new_phone' => 'numeric|unique:users,phone',
                    ]);

                    if ($new_email != NULL) {
                        $updateDataUser->email = $new_email;
                    }

                    $updateDataUser->password = bcrypt($new_password);
                }

                if (!$checkPhone) {
                    $this->updatePhone("$id", $new_phone);
                    $updateDataUser->phone = $new_phone;
                    $updateDataUser->status = "0";
                }

                if ($validator->fails()) {
                    return $this->sendError('Error!', $validator->errors());
                }

                $updateDataUser->name = $name;

                // dd($data);exit();

                $updateDataUser->save();
                $tokenMsg = Str::random(15);
                $success['token'] = $tokenMsg;
                $success['message'] = "Data berhasil diupdate!";
                $success['data'] = $updateDataUser;
                return $this->sendResponse($success, 'Update data');
            } else {
                return $this->sendError('Account is not login.', ['error' => 'Silakan login terlebih dulu!']);
            }
        } catch (\Throwable $th) {
            return $this->sendError('Error!', $th);
        }
    }

    /**
     * Put User into trash
     * 
     * @param int $id
     * @return \Illuminate\Http\Response
     */

    public function delete(Int $id)
    {
        try {
            if (Auth::user()) {
                // \DB::enableQueryLog();
                $checkUser = User::where('id', $id)->first();
                // dd(\DB::getQueryLog());
                // dd($checkUser);exit();
                if(!$checkUser){
                    return $this->sendError('Error!', ['error'=> 'Tidak ada data yang dihapus!']);
                }

                $deleteUser = User::find($id);
                $deleteUser->deleted_at = Carbon::now();
                $deleteUser->delete();

                $tokenMsg = Str::random(15);
                $success['token'] = $tokenMsg;
                $success['message'] = "Delete data";
                $success['data'] = $deleteUser;
                return $this->sendResponse($success, 'Data berhasil dihapus');
            } else {
                return $this->sendError('Account is not login.', ['error' => 'Silakan login terlebih dulu!']);
            }
        } catch (\Throwable $th) {
            return $this->sendError('Error!', $th);
        }
    }

    /**
     * Put Multiple User into trash
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */

    public function deleteMultiple(Request $request)
    {
        try {
            if (Auth::user()) {
                $ids = $request->ids;
                // \DB::enableQueryLog();
                $checkUser = User::whereIn('id', $ids)->first();
                // dd(\DB::getQueryLog());
                // dd($checkUser);exit();
                if(!$checkUser){
                    return $this->sendError('Error!', ['error'=> 'Tidak ada data yang dihapus!']);
                }

                // \DB::enableQueryLog();
                $deleteUser = User::findMany($ids);
                // dd(\DB::getQueryLog());
                $counter = 0;
                foreach ($deleteUser as $rowUsers) {
                    $deleteUser = User::find($rowUsers->id);
                    $deleteUser->deleted_at = Carbon::now();
                    $deleteUser->delete();
                    $counter++;
                }

                $tokenMsg = Str::random(15);
                $success['token'] = $tokenMsg;
                $success['message'] = "Delete selected data";
                $success['total_data'] = $counter;
                return $this->sendResponse($success, 'Data terpilih berhasil dihapus');
            } else {
                return $this->sendError('Account is not login.', ['error' => 'Silakan login terlebih dulu!']);
            }
        } catch (\Throwable $th) {
            return $this->sendError('Error!', $th);
        }
    }

    /**
     * Restore User from trash
     * 
     * @param int $id
     * @return \Illuminate\Http\Response
     */

    public function restore(Int $id)
    {
        // return "Cek";exit();
        try {
            if (Auth::user()) {
                // \DB::enableQueryLog();
                $checkUser = User::onlyTrashed()->where('id', $id)->get();
                // dd(\DB::getQueryLog());
                
                if($checkUser->isEmpty()){
                    return $this->sendError('Error!', ['error'=> 'Tidak ada data yang dipulihkan']);
                }
                $restoreUser = User::onlyTrashed()->find($id);
                $restoreUser->deleted_at = null;
                $restoreUser->restore();
                
                $tokenMsg = Str::random(15);
                $success['token'] = $tokenMsg;
                $success['message'] = "Restore user data";
                $success['data'] = $restoreUser;
                return $this->sendResponse($success, 'Data dipulihkan');
            } else {
                return $this->sendError('Account is not login.', ['error' => 'Silakan login terlebih dulu!']);
            }
        } catch (\Throwable $th) {
            return $this->sendError('Error!', $th);
        }
    }

    /**
     * Restore Multiple User from trash
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */

    public function restoreMultiple(Request $request)
    {
        // return "Cek";exit();
        try {
            if (Auth::user()) {
                $ids = $request->ids;
                // \DB::enableQueryLog();
                $checkUser = User::onlyTrashed()->whereIn('id', $ids)->get();
                // dd(\DB::getQueryLog());
                
                if($checkUser->isEmpty()){
                    return $this->sendError('Error!', ['error'=> 'Tidak ada data yang dipulihkan']);
                }

                // \DB::enableQueryLog();
                $deleteUser = User::onlyTrashed()->findMany($ids);
                // dd(\DB::getQueryLog());
                $counter = 0;
                
                foreach ($deleteUser as $rowUsers) {
                    // dd($deleteUser);
                    $restoreUser = User::onlyTrashed()->find($rowUsers->id);
                    $restoreUser->deleted_at = null;
                    $restoreUser->restore();
                    $counter++;
                }                
                
                $tokenMsg = Str::random(15);
                $success['token'] = $tokenMsg;
                $success['message'] = "Restore multiple user data";
                $success['total_data'] = $counter;
                return $this->sendResponse($success, 'Data terpilih dipulihkan');
            } else {
                return $this->sendError('Account is not login.', ['error' => 'Silakan login terlebih dulu!']);
            }
        } catch (\Throwable $th) {
            return $this->sendError('Error!', $th);
        }
    }

    /** GENERATE OTP AND SEND OTP TO NUMBER ACCOUNT */

    /** Generate OTP Update Phone Number*/ 
    protected function updatePhone(String $user_id, String $phone){
        try {
            if (Auth::user()) {
                $check = VerificationCodes::where('user_id', $user_id)->first();
                if(!$check){
                    return $this->sendError('Error!', ['error'=>'Tidak ada data user!']);
                }
                // dd($check->id);
                $setStatusOtp = VerificationCodes::where('user_id', $user_id)->update(['status' => '0']);
                $otp = rand(100000, 999999);
                $updateVerificationCode = VerificationCodes::where('user_id', $user_id)->update([
                    'otp' => $otp,
                    'expired_at' => Carbon::now()->addMinutes(10),
                    'status' => '0',
                ]);
                
                $strPhone = "$phone";
                $strOtp = "$otp";
                // return var_dump($strOtp);
                $this->sendWhatsappNotification($strOtp, $strPhone);
                // $tokenMsg = Str::random(15);
                // $success['token'] = $tokenMsg;
                // $success['message'] = "Kode OTP telah dikirim. Silakan buka pesan di What's App anda!";
                // return $this->sendResponse($success, 'Kode OTP Terkirim.');
            } else {
                return $this->sendError('Account is not login.', ['error' => 'Silakan login terlebih dulu!']);
            }   
        } catch (\Throwable $e) {
            return $this->sendError('Error!', ['error' => $e]);
        }
    }

    /** Sending OTP via Whats App */
    private function sendWhatsappNotification(String $otp, String $recipient){
        try {
            $tokenMsg = Str::random(15);
            $sid    = getenv("TWILIO_SID"); 
            $token  = getenv("TWILIO_AUTH_TOKEN"); 
            $twilio = new Client($sid, $token);
            $text   = "ERAKSA\nAssets Management System\n\nKode OTP anda: *$otp*.\n\nKode ini hanya akan berlaku dalam 10 menit ke depan. Jangan bagikan kode ini kepada siapapun!$tokenMsg"; 
            $message = $twilio->messages 
                            ->create("whatsapp:$recipient", // to 
                                    array( 
                                        "from" => "whatsapp:".getenv("TWILIO_NUMBER"),       
                                        "body" => "$text",
                                    ) 
                            );
        } catch (\Throwable $e) {
            return $this->sendError('Error!', ['error' => $e]);
        }
        
    }
}
