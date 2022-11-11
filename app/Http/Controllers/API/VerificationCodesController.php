<?php

namespace App\Http\Controllers\API;

use Exception;
use Carbon\Carbon;
use App\Models\User;
use Twilio\Rest\Client;
// use GuzzleHttp\Client;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\VerificationCode;
// use App\Http\Controllers\Controller;
use App\Models\VerificationCodes;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Enum;
use App\Providers\RouteServiceProvider;
use App\Services\VerificationCodesService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
// use Illuminate\Support\Facades\Validator;
// use Twilio\Rest\Client;


class VerificationCodesController extends BaseController
{

    public $verificationCodesService;

    public function __construct(VerificationCodesService $verificationCodesService)
    {
        $this->verificationCodesService = $verificationCodesService;
    }

    /** Generate New OTP */ 
    protected function generate(String $user_id)
    {
        try {
            sleep(5);
            $checkValidate = VerificationCodes::where('user_id', $user_id)->where('status', '1')->first();
            // print($checkValidate);exit();
            if ($checkValidate) {
                return $this->sendError('Error!', ['error'=>'Akun sudah divalidasi!']);
            }
            if(VerificationCodes::where('user_id', $user_id)->first()){
                return $this->sendError('Kode OTP sudah terkirim!', ['error'=>'OTP untuk user ini sudah dikirim, silakan masukkan!']);
            }
            $otp = rand(100000, 999999);
            $verificationCode = VerificationCodes::create([
                'otp' => $otp,
                'user_id' => $user_id,
                'expired_at' => Carbon::now()->addMinutes(10),
                'status' => '0',
            ]);
            $phone = VerificationCodes::join('users', 'users.id', '=', 'verification_codes.user_id')
                    ->where('users.id', $user_id)
                    ->where('verification_codes.user_id', $user_id)
                    ->get(['users.phone'])
            ;
            $strPhone = implode(',', collect($phone)->map(fn($item) => $item['phone'])->all());
            $strOtp = "$otp";
            // return var_dump($strOtp);
            $this->verificationCodesService->sendWhatsappNotification($strOtp, $strPhone);
            $tokenMsg = Str::random(15);
            $success['token'] = $tokenMsg;
            $success['message'] = "Kode OTP telah dikirim. Silakan buka pesan di What's App anda!";
            return $this->sendResponse($success, 'Kode OTP Terkirim.');    
        } catch (Exception $e) {
            return $this->sendError('Error!', ['error' => $e]);
        }
    }

    /** Generate Update OTP */ 
    protected function regenerate(String $user_id){
        try {
            sleep(5);
            // VerificationCodes::where('user_id', $user_id)->first();
            if(!VerificationCodes::where('user_id', $user_id)->first()){
                return $this->sendError('Error!', ['error'=>'Tidak ada data user!']);
            }
            
            $checkValidate = VerificationCodes::where('user_id', $user_id)->where('status', '1')->first();
            
            if ($checkValidate) {
                return $this->sendError('Error!', ['error'=>'Akun sudah divalidasi!']);
            }
            $otp = rand(100000, 999999);
            $updateVerificationCode = VerificationCodes::where('user_id', $user_id)->update([
                'otp' => $otp,
                'expired_at' => Carbon::now()->addMinutes(10),
            ]);
            // \DB::enableQueryLog(); 
            $phone = DB::table('verification_codes')
                ->select('users.id', 'users.phone', 'verification_codes.user_id')
                ->join('users', 'users.id', '=', 'verification_codes.user_id')
                ->where('verification_codes.user_id', $user_id)
                ->pluck('users.phone');
            // dd(\DB::getQueryLog());
            // var_dump((array) $phone[0]);exit();
            $strPhone = implode('|', (array) $phone[0]);
            // dd($strPhone);
            $strOtp = "$otp";
            // return var_dump($strOtp);
            $this->verificationCodesService->sendWhatsappNotification($strOtp, $strPhone);
            // dd($this->verificationCodesService);
            $tokenMsg = Str::random(15);
            $success['token'] = $tokenMsg;
            $success['message'] = "Kode OTP telah dikirim. Silakan buka pesan di What's App anda!";
            return $this->sendResponse($success, 'Kode OTP Terkirim.');   
        } catch (\Throwable $e) {
            return $this->sendError('Error!', ['error' => $e]);
        }
    }

    /** Check OTP */
    public function checkOtp(String $user_id, Request $request){
        try {
            sleep(5);
            $validator = Validator::make($request->all(),[
                'otp' => 'required',
            ]);

            // dd($user_id);
            // \DB::enableQueryLog();
            $verificationCode = VerificationCodes::where('user_id', $user_id)->where('otp', $request->otp)->first();
            // dd(\DB::getQueryLog());
            // dd($verificationCode);
            $now = Carbon::now();
            if (!$verificationCode) {
                return $this->sendError('OTP Salah.', ['error'=>'Masukkan ulang otp dengan benar!']);
            } elseif ($verificationCode && $now->isAfter($verificationCode->expired_at)) {
                return $this->sendError('OTP Kadaluarsa.', ['error'=>'Otp sudah melewati batas waktu penginputan!']);
            } else {
                $this->setValidate($user_id);
                $this->setUserStatus($user_id);
                $success['status'] = "Nomor tervalidasi";
                $success['token'] = $verificationCode->createToken('MyApp')->plainTextToken;
                return $this->sendResponse($success, 'Kode OTP Benar.');
            }
        } catch (Exception $e) {
            return $this->sendError('Error!', ['error' => $e]);
        }
        
    }

    /** Set Status Verification Codes to validate */

    private function setValidate(String $user_id){
        try {
            sleep(5);
            $updateValidate = VerificationCodes::where('user_id', $user_id)->update(array('status' => "1"));
            return $updateValidate;
        } catch (Exception $e) {
            return $this->sendError('Error!', ['error' => $e]);
        }
        
    }

    /** Set User Status to validate */
    private function setUserStatus(String $user_id){
        try {
            sleep(5);
            $updateStatus = User::find($user_id);
            $updateStatus->status = '1';
            // $updateStatus->save();
            return $updateStatus->save();
        } catch (Exception $e) {
            return $this->sendError('Error!', ['error' => $e]);
        }
        
    }
}
