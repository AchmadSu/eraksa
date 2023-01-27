<?php
namespace App\Services\Returns;
// require_once '/path/to/vendor/autoload.php';
use App\Http\Controllers\API\BaseController;
use Carbon\Carbon;
use Twilio\Rest\Client;
use Illuminate\Support\Str;
// use App\Models\VerificationCodes;

class ReturnsRequestService{

    /** Sending OTP via Whats App */
    public function sendWhatsappNotification(String $message, String $recipient){
        sleep(5);
        // dd($recipient);
        $tokenMsg = Str::random(15);
        $sid    = getenv("TWILIO_SID");
        $token  = getenv("TWILIO_AUTH_TOKEN");
        $from = getenv("TWILIO_NUMBER"); 
        // dd($token); 
        $twilio = new Client($sid, $token);
        $text   = "ERAKSA\nAssets Management System\n\n$message\n\n\n$tokenMsg"; 
        $messageText = $twilio->messages->create("whatsapp:$recipient", array("from" => "whatsapp:$from", "body" => "$text"));
        // print($messageText->sid);
    }
}