<?php
namespace App\Services;

use App\Http\Controllers\API\BaseController;
use Carbon\Carbon;
use Twilio\Rest\Client;
use Illuminate\Support\Str;
use App\Models\VerificationCodes;

class VerificationCodesService{

    /** Sending OTP via Whats App */
    public function sendWhatsappNotification(String $otp, String $recipient){
        sleep(5);
        // dd('test');
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
    }
}