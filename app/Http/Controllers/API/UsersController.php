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
use App\Mail\ResetPasswordLink;
use App\Models\VerificationCodes;
use Spatie\Permission\Model\Roles;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use App\Services\VerificationCodes\VerificationCodesService;
use Symfony\Component\Console\Input\Input;
use App\Http\Controllers\API\BaseController;
use App\Http\Controllers\API\AuthOtpController as AuthOtpController;
use App\Services\Users\UserService;

class UsersController extends BaseController
{
    public $verificationCodesService;
    // public $userService;

    public function __construct(
        VerificationCodesService $verificationCodesService,
        // UserService $userService,
    )
    {
        $this->verificationCodesService = $verificationCodesService;
        // $this->userService = $userService;
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    /** ATTRIVE USER DATA */
    /** 
     * Get All Users
     * 
     * @return \Illuminate\Http\Response
    */

    public function index(){
        try {
            sleep(5);
            $users = User::all();
            if(Auth::user()->hasRole('Admin')){
                $users = User::role('Member')->where('study_program_id', Auth::user()->study_program_id)->get();
            }
            // dd($users);
            if ($users->isEmpty()) {
                return $this->sendError('Error!', ['error' => 'Data tidak ditemukan!']);
            }
            return $this->sendResponse($users, 'Displaying all users data');
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
            sleep(5);
            // dd(Auth::user());
            // dd(Auth::user()->hasRole('Admin'));
            // \DB::enableQueryLog();
            $users = User::onlyTrashed()->get();
            if(Auth::user()->hasRole('Admin')){
                $users = User::onlyTrashed()
                ->role('Member')
                ->where(
                    'study_program_id', Auth::user()->study_program_id
                    )->get();
            }
            // var_dump($users);exit();
            // var_dump($users->isEmpty());exit();
            if ($users->isEmpty()) {
                return $this->sendError('Error!', ['error' => 'Data tidak ditemukan!']);
            }
            return $this->sendResponse($users, 'Displaying all trash data');
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
            sleep(5);
            $user = User::where('id', $id)->first();
            // dd(\DB::getQueryLog());
            if (!$user) {
                return $this->sendError('Error!', ['error' => 'Data tidak ditemukan!']);
            }
            return $this->sendResponse($user, 'User detail by Id');
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
            sleep(5);
            // \DB::enableQueryLog();
            $checkDeletedUser = User::onlyTrashed()->where('email', $request->email)->first();
            // dd(\DB::getQueryLog());
            // dd($checkUser);
            if($checkDeletedUser) {
                return $this->sendError('Error!', ['error'=> 'Akun anda telah dihapus. Silakan hubungi admin untuk mengaktifkan kembali!']);
            }

            if(Auth::attempt(['email' => $request->email, 'password' => $request->password])){
                $user = Auth::user();
                // dd(Role::find(3));
                $success['token'] = $user->createToken('token-name', ['server:update'])->plainTextToken;
                if($user->hasRole('Super-Admin')){
                    $success['roles'] = 'Super-Admin';
                } elseif ($user->hasRole('Super-Admin') && ($user->hasRole('Admin') || $user->hasRole('Member'))) {
                    $success['roles'] = 'Super-Admin';
                } elseif ($user->hasRole('Admin')) {
                    $success['roles'] = 'Admin';
                } elseif ($user->hasRole('Admin') && $user->hasRole('Member')) {
                    $success['roles'] = 'Admin';
                } else {
                    $success['roles'] = 'Member';
                }
                $success['user'] = $user;
                $checkOTP = VerificationCodes::where('user_id', $user->id)->first();
                
                return $this->sendResponse($success, 'Anda berhasil masuk!');
    
                // return Route::resource('user', UserController::class);
            } else {
                return $this->sendError('Unauthorised!', ['error'=> 'Email atau Password salah!']);
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
        sleep(5);
        // $user->tokens()->delete();
        // $header = $request->header('Authorization');
        // dd($header);
        // Auth::user()->tokens()->where('id', $request->id)->delete();
        $request->user()->currentAccessToken()->delete();
        $success['message'] = "Logged out!";
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
            sleep(5);
            $validator = Validator::make($request->all(),[
                'name' => 'required',
                'email' => 'required|email|unique:users,email',
                'password' => 'required',
                'confirm_pass' => 'required|same:password',
                'phone' => 'required|numeric|unique:users,phone'
            ]);
    
            
            $input = $request->all();
            // \DB::enableQueryLog();
            $checkDeletedUser = User::onlyTrashed()->where('email', $input['email'])->first();
            // dd(\DB::getQueryLog());
            // dd($checkUser);
            if($checkDeletedUser) {
                return $this->sendError('Error!', ['error'=> 'Akun anda sudah terdaftar. Namun telah dihapus. Silakan hubungi admin untuk mengaktifkan kembali!']);
            }

            $checkEmail = User::where('email', $input['email'])->first();
            // dd(\DB::getQueryLog());
            if($checkEmail){
                return $this->sendError('Error!', ['error'=>'Email sudah terdaftar, silakan login!']);
            }
            
            $input['name'] = ucwords($input['name']);
            $input['password'] = bcrypt($input['password']);
            
            $spiltPhone = str_split($input['phone']);
            
            if($spiltPhone[0] === '8'){
                $input['phone'] = '+62'.$input['phone'];
            }
            // dd($spiltPhone[0].$spiltPhone[1]);
            if($spiltPhone[0].$spiltPhone[1] === '62'){
                $input['phone'] = '+'.$input['phone'];
            }
            
            $checkPhone = User::where('phone', $input['phone'])->first();
            if($checkPhone){
                return $this->sendError('Error!', ['error'=>'Nomor sudah terdaftar, silakan login!']);
            }
            
            if ($validator->fails()){
                return $this->sendError('Error!', $validator->errors());
            }
            
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            $user = User::create($input);
            $role1 = Role::find(3);
            $user->assignRole($role1);

            $otp = rand(100000, 999999);
            VerificationCodes::create([
                'otp' => $otp,
                'user_id' => $user->id,
                'expired_at' => Carbon::now()->addMinutes(10),
                'status' => '0',
            ]);
            $this->verificationCodesService->sendWhatsappNotification("$otp", $input['phone']);
            // $success['token'] = $user->createToken('MyApp')->plainTextToken;
            
            $success['message'] = "Hai, $user->name! Kami telah mengirimkan OTP ke nomor WhatsApp anda. Silakan login untuk melanjutkan!";

            // $stringId = $user->id;

            // $this->generate("$stringId");
    
            return $this->sendResponse($success, 'User ditambahkan!'); 
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
            sleep(5);
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
            // $new_studyProgram_id = $request->new_studyProgram_id;
            
            $checkUser = User::where('id', $id)->first();
            if (!$checkUser) {
                return $this->sendError('Error!', ['error' => 'Data user tidak ditemukan!']);
            }
            // \DB::enableQueryLog();
            // $checkPassword = User::where('id', $id)->where('password', bcrypt($old_password))->first();
            // dd(\DB::getQueryLog());
            $checkPassword = Hash::check($old_password, $updateDataUser->password);
            // dd($checkPassword);

            $spiltPhone = str_split($phone);
            // dd($spiltPhone);
            if($spiltPhone[0] == '8'){
                $phone = '+62'.$phone;
            } elseif($spiltPhone[0].$spiltPhone[1] == '62'){
                $phone = '+'.$phone;
            }
            // dd($phone);

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

            } elseif ($new_password != NULL) {
                $validator = Validator::make($request->all(),[
                    'name' => 'required',
                    'new_email' => 'email|unique:users,email',
                    'new_password' => 'required',
                    'confirm_new_password' => 'required|same:new_password',
                    'new_phone' => 'numeric|unique:users,phone',
                ]);
                $updateDataUser->password = bcrypt($new_password);
            }   

            if ($validator->fails()) {
                return $this->sendError('Error!', $validator->errors());
            }

            if ($new_email != NULL) {
                $updateDataUser->email = $new_email;
            }

            if ($checkPhone) {
                if($new_phone != NULL) {
                    $spiltPhone = str_split($new_phone);
                    if($spiltPhone[0] === '8'){
                        $new_phone = '+62'.$new_phone;
                    }elseif($spiltPhone[0].$spiltPhone[1] === '62'){
                        $new_phone = '+'.$new_phone;
                    }
                    $this->updatePhone("$id", $new_phone);
                    $updateDataUser->phone = $new_phone;
                    $updateDataUser->status = "0";
                    $success['updatePhone'] = "Nomor berhasi di-update. Silakan masukkan kode otp yang kami kirim ke nomor baru anda!";
                }
            } else {
                return $this->sendError('Error!', ['error' => 'Nomor lama yang anda masukkan salah!']);
            }

            $updateDataUser->name = ucwords($name);
            // DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            // $updateDataUser->study_program_id = $new_studyProgram_id;

            // dd($data);exit();

            $updateDataUser->save();
            $tokenMsg = Str::random(15);
            $success['token'] = $tokenMsg;
            $success['message'] = "Data berhasil diupdate!";
            $success['data'] = $updateDataUser;
            return $this->sendResponse($success, 'Update data');
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
            sleep(5);
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
            sleep(5);
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
            sleep(5);
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
            sleep(5);
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
        } catch (\Throwable $th) {
            return $this->sendError('Error!', $th);
        }
    }

    /**
     * Request Reset User password
     * 
     * @param \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */

    public function requestResetPassword(Request $request)
    {
        try {
            sleep(5);
            $email = $request->email;
            
            $checkDeletedUser = User::onlyTrashed()->where('email', $email)->first();
            // dd(\DB::getQueryLog());
            // dd($checkUser);
            if($checkDeletedUser) {
                return $this->sendError('Error!', ['error'=> 'Akun anda telah dihapus. Silakan hubungi admin untuk mengaktifkan kembali!']);
            }
            // \DB::enableQueryLog();
            $checkUser = User::where('email', $email)->first();
            // dd(\DB::getQueryLog());
            // dd($checkUser);exit();
            if($checkUser){
                $token = Str::random(60);
                $checkUser->remember_token = $token;
                $checkUser->save();
                $mailData = [
                    "name" => "Reset Password",
                    "link" => config('app.url').':3000/resetPassword/'.'data?token='.$token.'&email='.urlencode($email).'&expired_at='.Carbon::now()->addMinutes(10),
                ];
                Mail::to($email)->send(new ResetPasswordLink($mailData));
                $success['message'] = "Link reset password berhasil dikirim kepada $email. Silakan cek pesan masuk anda!";
                return $this->sendResponse($success, 'Pesan berhasil dikirim!');
            } else {
                return $this->sendError('Error!', ['error'=> 'Email tidak terdaftar!']);
            }
            
        } catch (\Throwable $th) {
            return $this->sendError('Error!', $th);
        }
    }

    /**
     * Reset User password
     * 
     * @param \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */

    public function resetPassword(Request $request)
    {
        try {
            sleep(5);
            $email = $request->email;
            $token = $request->token;
            $password = $request->password;

            $validator = Validator::make($request->all(),[
                'token' => 'required',
                'email' => 'required|email',
                'password' => 'required',
                'confirm_pass' => 'required|same:password'
            ]);

            if ($validator->fails()){
                return $this->sendError('Error!', $validator->errors());
            }

            $checkDeletedUser = User::onlyTrashed()->where('email', $email)->first();
            // dd(\DB::getQueryLog());
            // dd($checkUser);
            if($checkDeletedUser) {
                return $this->sendError('Error!', ['error'=> 'Akun anda telah dihapus. Silakan hubungi admin untuk mengaktifkan kembali!']);
            }

            // \DB::enableQueryLog();
            $checkUser = User::where('email', $email)->where('remember_token', $token)->first();
            // dd(\DB::getQueryLog());
            // dd($checkUser->isEmpty());exit();
            // var_dump($now < $addMinutes);exit();
            if($checkUser){
                $updateTime = strtotime($checkUser->updated_at);
                $addMinutes = strtotime('+ 10 minutes', $updateTime);
                $now = strtotime(Carbon::now());
                if ($now <= $addMinutes) {
                    $checkUser->forceFill([
                        'password' => bcrypt($password),
                        'remember_token' => null,
                    ]);
                    $checkUser->save();
                    $success['message'] = "Password berhasil diubah. Silakan login!";
                    return $this->sendResponse($success, 'Password berhasil direset!');
                }
                else {
                    $checkUser->forceFill([
                        'remember_token' => null,
                    ]);
                    $checkUser->save();
                    return $this->sendError('Error!', ['error'=> 'Waktu reset password telah habis. Silakan ulangi reset password!']);
                }
            } else {
                return $this->sendError('Error!', ['error'=> 'Email atau Token tidak sesuai!']);
            }
            
        } catch (\Throwable $th) {
            return $this->sendError('Error!', $th);
        }
    }

    /**
     * Reset User password
     * 
     * @param \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */

    public function resetPhone(String $id, Request $request)
    {
        try {
            sleep(5);
            $phone = $request->phone;
            
            $spiltPhone = str_split($phone);
            // dd($spiltPhone);
            if($spiltPhone[0] == '8'){
                $phone = '+62'.$phone;
            } elseif($spiltPhone[0].$spiltPhone[1] == '62'){
                $phone = '+'.$phone;
            }

            // dd($phone);

            $checkPhone = User::where('phone', $phone)->first();

            if ($checkPhone){
                return $this->sendError('Error!', 'Nomor sudah terdaftar pada user lain. Silakan ganti dengan nomor yang lain!');
            }

            // \DB::enableQueryLog();
            $checkUser = User::where('id', $id)->first();
            // dd(\DB::getQueryLog());
            // dd($checkUser->isEmpty());exit();
            // var_dump($now < $addMinutes);exit();
            if($checkUser){
                $checkUser->forceFill([
                    'phone' => $phone,
                ]);
                $checkUser->save();
                $success['message'] = "Nomor berhasil diubah. Silakan kirim ulang kode OTP anda!";
                return $this->sendResponse($success, 'Nomor berhasil direset!');                    
            } else {
                return $this->sendError('Error!', ['error'=> 'Data user tidak sesuai!']);
            }
            
        } catch (\Throwable $th) {
            return $this->sendError('Error!', $th);
        }
    }

    /** GENERATE OTP AND SEND OTP TO NUMBER ACCOUNT */

    /** Generate OTP Update Phone Number*/ 
    protected function updatePhone(String $user_id, String $phone)
    {
        try {
            sleep(5);
            $check = VerificationCodes::where('user_id', $user_id)->first();
            if(!$check){
                return $this->sendError('Error!', ['error'=>'Tidak ada data user!']);
            }
            // sleep(5);
            $setStatusOtp = VerificationCodes::where('user_id', $user_id)->update(['status' => '0']);
            $otp = rand(100000, 999999);
            $updateVerificationCode = VerificationCodes::where('user_id', $user_id)->update([
                'otp' => $otp,
                'expired_at' => Carbon::now()->addMinutes(10),
                'status' => '0',
            ]);
            
            $strPhone = "$phone";
            $strOtp = "$otp";

            $this->verificationCodesService->sendWhatsappNotification($strOtp, $strPhone);
        } catch (\Throwable $e) {
            return $this->sendError('Error!', ['error' => $e]);
        }
    }

}
