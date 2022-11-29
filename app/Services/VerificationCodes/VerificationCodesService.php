<?php
namespace App\Services\VerificationCodes;
// require_once '/path/to/vendor/autoload.php';
use App\Http\Controllers\API\BaseController;
use Carbon\Carbon;
use Twilio\Rest\Client;
use Illuminate\Support\Str;
use App\Models\VerificationCodes;

class VerificationCodesService{

    /** Sending OTP via Whats App */
    public function sendWhatsappNotification(String $otp, String $recipient){
        sleep(5);
        // dd($recipient);
        $tokenMsg = Str::random(15);
        $sid    = getenv("TWILIO_SID");
        $token  = getenv("TWILIO_AUTH_TOKEN");
        $from = getenv("TWILIO_NUMBER"); 
        // dd($token); 
        $twilio = new Client($sid, $token);
        $text   = "ERAKSA\nAssets Management System\n\nKode OTP anda: *$otp*.\n\nKode ini hanya akan berlaku dalam 10 menit ke depan. Jangan bagikan kode ini kepada siapapun!$tokenMsg"; 
        $twilio->messages->create("whatsapp:$recipient", array("from" => "whatsapp:$from", "body" => "$text"));
        // dd($message);
    }
}