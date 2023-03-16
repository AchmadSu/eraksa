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
use App\Models\StudyPrograms;
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
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
    */

    public function index(Request $request){
        try {
            // dd(Auth::user()->name);
            sleep(5);
            //$users = User::all();
            $keyWords = $request->keyWords;
            $code_type = $request->code_type;
            $status = $request->status;
            $phone = $request->phone;
            $skip = $request->skip;
            $take = $request->take;
            $order = $request->order;
            $trash = $request->trash;
            $roles = $request->roles;

            // dd(Auth::user()->hasRole('Super-Admin'));
            $study_program_id = $request->study_program_id;
            $study_program_keyWords = $request->study_program_keyWords;

            if(isset($phone)) {
                $spiltPhone = str_split($phone);
                // dd($spiltPhone);
                if($spiltPhone[0] === '8'){
                    $phone = '+62'.$phone;
                }
                // dd($spiltPhone[0].$spiltPhone[1]);
                if($spiltPhone[0].$spiltPhone[1] === '62'){
                    $phone = '+'.$phone;
                }
            }

            $studyPrograms = StudyPrograms::where('name', 'like', '%'.$study_program_keyWords.'%')
            ->get();
            $studyProgram_ids = array();
            foreach ($studyPrograms as $rowstudyPrograms) {
                $studyProgram_ids[] = $rowstudyPrograms->id;
            }

            // dd($studyProgram_ids);
            \DB::enableQueryLog();
            $users = User::
            leftJoin('model_has_roles as model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
            ->leftJoin('roles as roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->leftJoin('study_programs as study_programs','users.study_program_id', '=', 'study_programs.id')
            ->when(isset($keyWords))
            ->where(function ($query) use ($keyWords){
                $query
                ->where('users.name', 'like', '%'.$keyWords.'%')
                ->orWhere('users.email', 'like', '%'.$keyWords.'%')
                ->orWhere('users.code', 'like', '%'.$keyWords.'%');
            })
            ->when(isset($code_type))
            ->where('users.code_type', $code_type)
            ->when(isset($status))
            ->where('users.status', $status)
            ->when(isset($roles))
            ->where('roles.id', $roles)
            ->when(isset($phone))
            ->where('users.phone', 'like', '%'.$phone.'%')
            ->when(isset($study_program_id))
            ->where('users.study_program_id', $study_program_id)
            ->when(isset($study_program_keyWords))
            ->whereIn('users.study_program_id', $studyProgram_ids)
            ->when(Auth::user()->hasRole('Admin'))
            ->role('Member')
            ->select(
                'users.id as id',
                'users.name as name',
                'users.code as code',
                'users.code_type as code_type',
                'users.email as email',
                'users.status as status',
                'users.created_at as created_at',
                'users.phone as phone',
                'users.updated_at as updated_at',
                'study_programs.id as study_program_id',
                'study_programs.name as study_program_name',
                'roles.name as user_role'
                )
            ->when($order)
            ->orderBy($order, 'ASC')
            ->when($trash == 1)
            ->onlyTrashed()
            ->get();
            // dd(\DB::getQueryLog());
            // dd($users);
            if ($users->isEmpty()) {
                return $this->sendError('Error!', ['error' => 'Data tidak ditemukan!']);
            }
            $countDelete = User::onlyTrashed()->count();
            // dd(\DB::getQueryLog());
            $success['count'] = $users->count();
            $success['countDelete'] = $countDelete;
            $success['users'] = $users
                ->when(isset($skip))
                ->skip($skip)
                ->when(isset($take))
                ->take($take)
            ;
            return $this->sendResponse($success, 'Displaying all users data');
        } catch (\Throwable $th) {
            return $this->sendError('Error!', ['error' => "Permintaan tidak dapat dilakukan"]);
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
            $user = User::
            leftJoin('model_has_roles as model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
            ->leftJoin('roles as roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->leftJoin('study_programs as study_programs','users.study_program_id', '=', 'study_programs.id')
            ->select(
                'users.id as id',
                'users.name as name',
                'users.code as code',
                'users.code_type as code_type',
                'users.email as email',
                'users.status as status',
                'users.created_at as created_at',
                'users.phone as phone',
                'users.updated_at as updated_at',
                'study_programs.id as study_program_id',
                'study_programs.name as study_program_name',
                'roles.name as user_role'
                )
            ->find($id);
            // dd(\DB::getQueryLog());
            if (!$user) {
                return $this->sendError('Error!', ['error' => 'Data tidak ditemukan!']);
            }
            return $this->sendResponse($user, 'User detail by Id');
        } catch (\Throwable $th) {
            return $this->sendError('Error!', ['error' => "Permintaan tidak dapat dilakukan"]);
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
                $success['roles'] = $user['roles'][0]['name'];
                $success['user'] = $user;
                
                return $this->sendResponse($success, 'Anda berhasil masuk!');
    
                // return Route::resource('user', UserController::class);
            } else {
                return $this->sendError('Unauthorised!', ['error'=> 'Email atau Password salah!']);
            }
        } catch (\Throwable $th) {
            return $this->sendError('Error!', ['error' => "Permintaan tidak dapat dilakukan"]);
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
                'code' => 'required|unique:users,code',
                'code_type' => 'required',
                'email' => 'required|email|unique:users,email',
                'password' => 'required',
                'confirm_pass' => 'required|same:password',
                'phone' => 'required|numeric|unique:users,phone'
            ]);
    
            
            $input = $request->all();
            // dd($input);
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
            
            $input['name'] = ucwords(strtolower($input['name']));
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
                return $this->sendError('Error!', ['error'=>'Data tidak valid!']);
            }
            
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            $user = User::create($input);
            $role3 = Role::find(3); // Member
            $user->assignRole($role3);

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
            return $this->sendError('Error!', ['error' => "Permintaan tidak dapat dilakukan"]);
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
            // dd(Auth::user()->email);
            sleep(5);
            // dd(Auth::user()->id);
            $id = Auth::user()->id;
            $updateDataUser = User::find($id);
            $name = $request->name;
            $code = $request->code;
            $code_type = $request->code_type;
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
                return $this->sendError('Error!', ['error' => 'Password lama yang anda masukkan salah!']);
            }

            // \DB::enableQueryLog();
            $checkCode = User::where('id', $id)->where('code', $code)->first();
            // dd(\DB::getQueryLog());

            if(!$checkCode){
                // dd(!$checkCode);
                $validator = Validator::make($request->code, [
                    'code' => 'required|unique:users,code',
                ]);
                if ($validator->fails()) {
                    return $this->sendError('Error!', ['error' => 'NIM atau NIDN sudah digunakan oleh pengguna lain']);
                }
            }

            if ($new_password == NULL || $new_password == '') {
                $validator = Validator::make($request->all(),[
                    'name' => 'required',
                    'code' => 'required',
                    'code_type' => 'required',
                ]);

            } elseif ($new_password != NULL || $new_password != '') {
                $validator = Validator::make($request->all(),[
                    'name' => 'required',
                    'code' => 'required',
                    'code_type' => 'required',
                    'new_password' => 'required',
                    'confirm_new_password' => 'required|same:new_password',
                ]);
                $updateDataUser->password = bcrypt($new_password);
            }   

            if ($validator->fails()) {
                return $this->sendError('Error!', ['error'=>'Data tidak valid. Masukkan data valid!']);
            }

            if ($new_email != NULL || $new_email != '') {
                $checkEmail = User::where('email', $new_email)->first();
                if($checkEmail){
                    return $this->sendError('Error!', ['error' => 'Email baru sudah digunakan oleh pengguna lain!']);
                }
                $updateDataUser->email = $new_email;
            }

            if ($checkPhone) {
                if($new_phone != NULL || $new_phone != '') {
                    $checkNewPhone = User::where('phone', $new_phone)->first();
                    if($checkNewPhone){
                        return $this->sendError('Error!', ['error' => 'Nomor baru sudah digunakan oleh pengguna lain!']);
                    }
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

            $updateDataUser->name = ucwords(strtolower($name));
            $updateDataUser->code = Str::upper($code);
            $updateDataUser->code_type = $code_type;
            // DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            // $updateDataUser->study_program_id = $new_studyProgram_id;

            // dd($data);exit();

            $updateDataUser->save();
            $success['message'] = "Data berhasil diupdate!";
            $success['data'] = $updateDataUser;
            return $this->sendResponse($success, 'Update data');
        } catch (\Throwable $th) {
            return $this->sendError('Error!', ['error' => "Permintaan tidak dapat dilakukan"]);
        }
    }

    /**
     * Put Multiple User into trash
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */

    public function delete(Request $request)
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
            return $this->sendError('Error!', ['error' => "Permintaan tidak dapat dilakukan"]);
        }
    }

    /**
     * Delete Multiple data permanently
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */

     public function deletePermanently(Request $request)
     {
         try {
             sleep(5);
             $ids = $request->ids;
             // dd($ids);
             if($ids == NULL) {
                 return $this->sendError('Error!', ['error' => 'Tidak ada aset yang dipilih!']);
             }
             // \DB::enableQueryLog();
             $checkData = User::whereIn('id', $ids)->onlyTrashed()->get();
             // dd(\DB::getQueryLog());
             if($checkData->isEmpty()){
                 return $this->sendError('Error!', ['error'=> 'Data tidak ditemukan!']);
             }
 
             // dd($checkAssets);
             // \DB::enableQueryLog();
         //  $deleteAssets = Assets::findMany($ids);
             // dd(\DB::getQueryLog());
             $totalDelete = 0;
             DB::statement('SET FOREIGN_KEY_CHECKS=0;');
             foreach($checkData as $rowData){
                 $rowData->forceDelete();  
                 $totalDelete++;
             }
 
             $tokenMsg = Str::random(15);
             $success['token'] = $tokenMsg;
             $success['message'] = "Delete selected data";
             $success['total_deleted'] = $totalDelete;
             return $this->sendResponse($success, 'Data terpilih berhasil dihapus');
         } catch (\Throwable $th) {
             return $this->sendError('Error!', ['error' => 'Permintaan tidak dapat dilakukan']);
         }
     }

    /**
     * Restore Multiple User from trash
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */

    public function restore(Request $request)
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
            return $this->sendError('Error!', ['error' => "Permintaan tidak dapat dilakukan"]);
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
            return $this->sendError('Error!', ['error' => "Permintaan tidak dapat dilakukan"]);
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
                return $this->sendError('Error!', ['error'=>'Data tidak valid!']);
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
            return $this->sendError('Error!', ['error' => "Permintaan tidak dapat dilakukan"]);
        }
    }

    /**
     * Reset User phone
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
                return $this->sendError('Error!', ['error' => 'Nomor sudah terdaftar pada user lain. Silakan ganti dengan nomor yang lain!']);
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
                $this->updatePhone("$id", $phone);
                
                $success['new_phone'] = $phone;
                $success['message'] = "Nomor berhasil diubah. Silakan kirim ulang kode OTP anda!";
                return $this->sendResponse($success, 'Nomor berhasil direset!');                    
            } else {
                return $this->sendError('Error!', ['error'=> 'Data user tidak sesuai!']);
            }
            
        } catch (\Throwable $th) {
            return $this->sendError('Error!', ['error' => "Permintaan tidak dapat dilakukan"]);
        }
    }

    /**
     * Assign User Roles
     * 
     * @param \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */

    public function assignRoles(Request $request)
    {
        try {
            sleep(5);
            $id = $request->id;
            $roles = $request->roles;
            if($roles == 2) {
                $study_program_id = $request->study_program_id;
            } else {
                $study_program_id = 0;
            }

            $checkUser = User::find($id);
            // dd($checkUser);
            if($checkUser){
                $checkUser->roles()->detach();
                $role = Role::find($roles);
                $checkUser->assignRole($role);
                DB::statement('SET FOREIGN_KEY_CHECKS=0;');
                $checkUser->study_program_id = $study_program_id;
                $checkUser->save();
                
                $success['user_name'] = $checkUser->name;
                $success['user_roles'] = $role->name;
                return $this->sendResponse($success, 'Peran pengguna berhasil direset!');
            } else {
                return $this->sendError('Error!', ['error' => "Pengguna atau peran pengguna tidak ditemukan!"]);
            }
            
        } catch (\Throwable $th) {
            return $this->sendError('Error!', ['error' => "Permintaan tidak dapat dilakukan"]);
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
        } catch (\Throwable $th) {
            return $this->sendError('Error!', ['error' => "Permintaan tidak dapat dilakukan"]);
        }
    }

}
