<?php

namespace App\Http\Controllers\API;

use DB;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Loans;
use App\Models\Assets;
use App\Models\Returns;
use App\Models\LoanDetails;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Validator;
use App\Services\Loans\LoansRequestService;
use App\Http\Controllers\API\BaseController;

class LoansController extends BaseController
{
    public $loansRequestService;

    public function __construct(
        LoansRequestService $loansRequestService,
    )
    {
        $this->loansRequestService = $loansRequestService;
    }
    /** ATTRIVE LOANS DATA */

    /** 
     * Get All loans
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
    */

    public function index(Request $request){
        try {
            sleep(5);
            $code = $request->code;
            $loaner_keyWords = $request->loaner_keyWords;
            $lender_id = $request->lender_id;
            $dateOne = $request->dateOne;
            $dateTwo = $request->dateTwo;
            $status = $request->status;
            $return_code = $request->return_code;
            $dueDateOne = $request->dueDateOne;
            $dueDateTwo = $request->dueDateTwo;

            $from = date($dateOne);
            $to = date($dateTwo);
            
            $dueFrom = date($dueDateOne);
            $dueTo = date($dueDateTwo);
            // dd($request->loaner_ids);
            // \DB::enableQueryLog();
            if($request->loaner_ids == NULL){
                $loaner = User::
                where('name', 'like', '%'.$loaner_keyWords.'%')
                ->orWhere('email', 'like', '%'.$loaner_keyWords.'%')
                ->get();
                $loaner_ids = array();
                foreach ($loaner as $rowLoaner) {
                    $loaner_ids[] = $rowLoaner->id;
                }
            } else {
                $loaner_ids = $request->loaner_ids;
            }
            // dd(isset($loaner_ids));
            $return = Returns::where('code', 'like', '%'.$return_code.'%')->get();
            // $loans = Loans::whereIn('loaner_id', $loaner_ids)->get();
            $return_ids = array();
            foreach ($return as $rowReturn) {
                $return_ids[] = $rowReturn->id;
            }
            // \DB::enableQueryLog();
            // dd($request->loaner_ids == NULL);
            $loans = Loans::when(isset($code))
            ->where('code', 'like', '%'.$code.'%')
            ->when(isset($loaner_ids))
            ->whereIn('loaner_id', $loaner_ids)
            ->when(isset($lender_id))
            ->where('lender_id', $lender_id)
            ->when(isset($dateOne) && !isset($dateTwo))
            ->where('date', $from)
            ->when(isset($dateOne) && isset($dateTwo))
            ->whereBetween('date', [$from, $to])
            ->when(isset($dueDateOne) && !isset($dueDateTwo))
            ->where('due_date', $dueFrom)
            ->when(isset($dueDateOne) && isset($dueDateTwo))
            ->whereBetween('due_date', [$dueFrom, $dueTo])
            ->when(isset($status))
            ->where('status', $status)
            ->when(isset($return_code))
            ->whereIn('return_id', $return_ids)
            ->get();
            // dd(Loans::all());
            // dd(Auth::user()->name);
            // \DB::enableQueryLog();
            // dd(\DB::getQueryLog());
            // dd($loans);
            if ($loans->isEmpty()) {
                return $this->sendError('Error!', ['error' => 'Data tidak ditemukan!']);
            }
            return $this->sendResponse($loans, 'Displaying all Loans data');
        } catch (\Throwable $th) {
            return $this->sendError('Error!', ['error' => "Permintaan tidak dapat dilakukan."]);
        }
    }

    /** 
     * Get Loans By Id
     * 
     * @param Int $id
     * @return \Illuminate\Http\Response
    */

    public function show(Int $id)
    {
        try {
            sleep(5);
            $Loans = Loans::where('id', $id)->first();
            // dd(\DB::getQueryLog());
            if (!$Loans) {
                return $this->sendError('Error!', ['error' => 'Data tidak ditemukan!']);
            }
            return $this->sendResponse($Loans, 'Loans detail by Id');
        } catch (\Throwable $th) {
            return $this->sendError('Error!', ['error' => "Permintaan tidak dapat dilakukan."]);
        }
        
    }

    /** 
     * Get All Assets in Trash
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
    */
    public function trash(Request $request){
        try {
            sleep(5);
            $code = $request->code;
            $loaner_id = Auth::user()->id;
            $lender_id = $request->lender_id;
            $dateOne = $request->dateOne;
            $dateTwo = $request->dateTwo;
            $return_code = $request->return_code;
            $dueDateOne = $request->dueDateOne;
            $dueDateTwo = $request->dueDateTwo;

            $from = date($dateOne);
            $to = date($dateTwo);
            
            $dueFrom = date($dueDateOne);
            $dueTo = date($dueDateTwo);
            // dd($request->loaner_ids);
            // \DB::enableQueryLog();
            // dd(isset($loaner_ids));
            $return = Returns::where('code', 'like', '%'.$return_code.'%')->get();
            // $loans = Loans::whereIn('loaner_id', $loaner_ids)->get();
            $return_ids = array();
            foreach ($return as $rowReturn) {
                $return_ids[] = $rowReturn->id;
            }
            // \DB::enableQueryLog();
            // dd($request->loaner_ids == NULL);
            $loans = Loans::onlyTrashed()
            ->when(isset($code))
            ->where('code', 'like', '%'.$code.'%')
            ->when(isset($loaner_ids))
            ->whereIn('loaner_id', $loaner_id)
            ->when(isset($lender_id))
            ->where('lender_id', $lender_id)
            ->when(isset($dateOne) && !isset($dateTwo))
            ->where('date', $from)
            ->when(isset($dateOne) && isset($dateTwo))
            ->whereBetween('date', [$from, $to])
            ->when(isset($dueDateOne) && !isset($dueDateTwo))
            ->where('due_date', $dueFrom)
            ->when(isset($dueDateOne) && isset($dueDateTwo))
            ->whereBetween('due_date', [$dueFrom, $dueTo])
            ->where('status', "0")
            ->get();
            // dd(Loans::all());
            // dd(Auth::user()->name);
            // \DB::enableQueryLog();
            // dd(\DB::getQueryLog());
            // dd($loans);
            if ($loans->isEmpty()) {
                return $this->sendError('Error!', ['error' => 'Data tidak ditemukan!']);
            }
            return $this->sendResponse($loans, 'Displaying all Loans data');
        } catch (\Throwable $th) {
            return $this->sendError('Error!', ['error' => "Permintaan tidak dapat dilakukan."]);
        }
    }

    /** CRUD LOANS */
    
    /**
     * Create Request Loans
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */

     public function create(Request $request){
        try {
            // dd();
            // $this->loansRequestService->sendWhatsappNotification("Test", Auth::user()->phone);
            sleep(5);
            $loaner_id = Auth::user()->id;

            $checkStatusLoans = Loans::
            where('loaner_id', $loaner_id)
            ->where('status', "0")
            ->orWhere('status', "1")
            ->first();

            // dd($checkStatusLoans->status == "0");

            if($checkStatusLoans){
                if($checkStatusLoans->status == "0"){
                    return $this->sendError('Error!', [
                        'error' =>
                        'Anda masih memiliki riwayat permintaan peminjaman, Mohon menunggu persetujuan!'
                    ]);
                } elseif($checkStatusLoans->status == "1") {
                    return $this->sendError('Error!', [
                        'error' =>
                        'Anda masih memiliki riwayat peminjaman yang aktif, Mohon selesaikan peminjaman sebelumnya untuk melakukan peminjaman lagi!'
                    ]);
                }
            }
            
            $asset_ids = $request->asset_ids;
            $checkAssets = Assets::whereIn("id", $asset_ids)->get();
            if($checkAssets->isEmpty())
            {
                return $this->sendError('Error!', [
                    'error' =>
                    'Tidak ada data aset yang dipinjam!'
                ]);
            }

            $hours = (int)$request->hours;
            switch ($hours) {
                case 2:
                    $range = "2 Jam";
                    break;
                case 3:
                    $range = "3 Jam";
                    break;
                case 4:
                    $range = "4 Jam";
                    break;
                case 8:
                    $range = "8 Jam";
                    break;
                case 12:
                    $range = "12 Jam";
                    break;
                case 24:
                    $range = "1 Hari";
                    break;
                case 48:
                    $range = "2 hari";
                    break;
                case 72:
                    $range = "3 Hari";
                    break;
                case 96:
                    $range = "4 Hari";
                    break;
                case 120:
                    $range = "5 Hari";
                    break;
                case 144:
                    $range = "6 Hari";
                    break;
                case 168:
                    $range = "1 Minggu";
                    break;
                case 336:
                    $range = "2 Minggu";
                    break;
                case 504:
                    $range = "3 Minggu";
                    break;
                case 720:
                    $range = "1 Bulan";
                    break;
                
                default:
                    # code...
                    break;
            }
            $date = date("d/m/Y");
            $phone = Auth::user()->phone;
            $inv = rand(1000, 9999);
            $strInv = "$inv";
            $code = "INV-".$date."-ERK-LOANS"."/".$phone."/".$strInv;
            
            $checkCodeLoans = Loans::
            where('code', $code)
            ->first();

            if($checkCodeLoans)
            {
                return $this->sendError('Error!', [
                    'error' =>
                    'Kode peminjaman sudah ada, Gunakan kode yang lain!'
                ]);
            }

            $status = "0";
            $due_date = Carbon::now()->addHours($hours);

            $loanArray = array(
                "code" => $code,
                "loaner_id" => $loaner_id,
                "date" => Carbon::now(),
                "due_date" => $due_date,
                "status" => $status
            );
            
            // dd($createLoans);
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            $createLoans = Loans::create($loanArray);
            $loan_id = $createLoans->id;
            $sortNumber = 0;
            for ($i=0; $i < count($asset_ids) ; $i++) {
                $asset_id = $asset_ids[$i];
                $checkAssets = Assets::find($asset_id);
                // dd($checkAssets);
                if($checkAssets->status == "0"){
                    $createLoanDetails = array(
                        "asset_id" => $asset_id,
                        "loan_id" => $loan_id
                    );
                    $checkAssets->status = "1";
                    $checkAssets->save();
                    LoanDetails::create($createLoanDetails);
                    // dd($message);
                    // \DB::enableQueryLog();
                    // var_dump($studyProgramAssets[$i] == $checkAssets->study_program_id);exit();
                    $studyProgramAssets = Assets::orderBy('study_program_id')->whereIn('id', $asset_ids)->get();
                    if($sortNumber !== $studyProgramAssets[$i]['study_program_id']){
                        $loaner_name = Auth::user()->name;
                        $loaner_code = Auth::user()->code;
                        $loaner_code_type = Auth::user()->code_type;
                        $adminPhone = User::where('study_program_id', $studyProgramAssets[$i]['study_program_id'])->pluck('phone');
                        
                        if($adminPhone->isEmpty() === FALSE) {
                            for ($rowPhone= 0; $rowPhone < count($adminPhone); $rowPhone++) { 
                                if($adminPhone[$rowPhone]) {
                                    $strPhone = implode('|', (array) $adminPhone[$rowPhone]);
                                    // var_dump($adminNumber);exit();
                                    if($loaner_code_type == "0") {
                                        $strUserCode = 'NIM';
                                    } else {
                                        $strUserCode = 'NIDN';                                        
                                    }
                                    $message = "Anda mendapatkan *Permintaan Peminjaman Baru*!\n\nRincian Permintaan\nNama peminjam: *$loaner_name*\n$strUserCode: *$loaner_code*\nKode: *$code*\n Periode: *$range*\n\nLihat detailnya melalui tautan berikut: ";
                                    $this->loansRequestService->sendWhatsappNotification($message, $strPhone);
                                    // dd($adminPhone[$rowPhone]);
                                }
                            }
                        }
                    }   
                    $sortNumber = $studyProgramAssets[$i]['study_program_id'];
                } else {
                    return $this->sendError('Error!', [
                        'error' =>
                        'Seluruh aset yang dipilih dalam keadaan tidak tersedia!'
                    ]);
                }
            }

            $superAdminPhone = User::role('Super-Admin')->pluck('phone');
            if($superAdminPhone->isEmpty() === FALSE) {
                for ($rowPhone= 0; $rowPhone < count($superAdminPhone); $rowPhone++) {
                    if($superAdminPhone[$rowPhone]){
                        // dd($superAdminPhone);
                        $strPhone = implode('|', (array) $superAdminPhone[$rowPhone]);
                        // var_dump($adminNumber);exit();
                        if($loaner_code_type == "0") {
                            $strUserCode = 'NIM';
                        } else {
                            $strUserCode = 'NIDN';                                        
                        }
                        $message = "Anda mendapatkan *Permintaan Peminjaman Baru*!\n\nRincian Permintaan\nNama peminjam: *$loaner_name*\n$strUserCode: *$loaner_code*\nKode: *$code*\n Periode: *$range*\n\nLihat detailnya melalui tautan berikut: ";
                        $this->loansRequestService->sendWhatsappNotification($message, $strPhone);
                    }
                }
            }

            $success['message'] = "Silakan menunggu Admin untuk memberikan konfirmasi persetujuan!";
            return $this->sendResponse($success, 'Permintaan peminjaman Berhasil!');    
        } catch (\Throwable $th) {
            return $this->sendError('Error!', ['error' => "Permintaan tidak dapat dilakukan. Periksa kembali jaringan anda!"]);
        } 
    }

    /**
     * Update Loans
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    
    public function update(Request $request)
    {
        try {
            sleep(5);
            $loan_id = $request->id;
            $loaner_id = Auth::user()->id;
            
            $checkLoaner = Loans::
            where('id', $loan_id)
            ->where('loaner_id', $loaner_id)
            ->first();
            // dd("test");

            // dd(!$checkLoaner);
            
            if(!$checkLoaner){
                return $this->sendError('Error!', [
                    'error' =>
                    'Anda bukan peminjam untuk transaksi peminjaman ini. Permintaan tidak dapat dilakukan!'
                ]);
            }

            $checkStatus = Loans::
            where('id', $loan_id)
            ->where('status', "0")
            ->first();

            // dd($checkStatus);
            if(!$checkStatus){
                return $this->sendError('Error!', [
                    'error' =>
                    'Peminjaman ini sedang aktif atau sudah selesai. Permintaan tidak dapat dilakukan!'
                ]);
            }

            $asset_ids = $request->asset_ids;
            $checkAssets = Assets::whereIn("id", $asset_ids)->get();
            // dd($checkAssets->isEmpty());
            if($checkAssets->isEmpty())
            {
                return $this->sendError('Error!', [
                    'error' =>
                    'Tidak ada data aset yang dipinjam!'
                ]);
            }

            $hours = (int)$request->hours;
            switch ($hours) {
                case 2:
                    $range = "2 Jam";
                    break;
                case 3:
                    $range = "3 Jam";
                    break;
                case 4:
                    $range = "4 Jam";
                    break;
                case 8:
                    $range = "8 Jam";
                    break;
                case 12:
                    $range = "12 Jam";
                    break;
                case 24:
                    $range = "1 Hari";
                    break;
                case 48:
                    $range = "2 hari";
                    break;
                case 72:
                    $range = "3 Hari";
                    break;
                case 96:
                    $range = "4 Hari";
                    break;
                case 120:
                    $range = "5 Hari";
                    break;
                case 144:
                    $range = "6 Hari";
                    break;
                case 168:
                    $range = "1 Minggu";
                    break;
                case 336:
                    $range = "2 Minggu";
                    break;
                case 504:
                    $range = "3 Minggu";
                    break;
                case 720:
                    $range = "1 Bulan";
                    break;
                
                default:
                    # code...
                    break;
            }
            // \DB::enableQueryLog();
            $getAssetFromLoanDetails = LoanDetails::
            where('loan_id', $loan_id)->get();
            // dd(\DB::getQueryLog());
            // dd($getAssetFromLoanDetails[0]['asset_id']);
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            $sortNumber = 0;
            // if($asset_ids)
            for ($i=0; $i < count($getAssetFromLoanDetails) ; $i++) { 
                $unSetStatusAssets = Assets::find($getAssetFromLoanDetails[$i]['asset_id']);
                // if()
                $unSetStatusAssets->status = "0";
                $unSetStatusAssets->save();
                $getAssetFromLoanDetails[$i]->delete();
                // dd("test");
                // dd($updateLoanDetails['asset_id']);
                // dd($getAssetFromLoanDetails[$i]['asset_id']);
                $asset_id = $asset_ids[$i];
                // dd($asset_id);
                $checkAssets = Assets::find($asset_id);
                if($checkAssets->status == "0"){
                    $createLoanDetails = array(
                        "loan_id" => $loan_id,
                        "asset_id" => $asset_id
                    );
                    $checkAssets->status = "1";
                    $checkAssets->save();
                    // dd($updateLoanDetails);
                    // $getAssetFromLoanDetails->asset_id = $updateLoanDetails[0]['asset_id'];
                    // $checkAssets->save();
                    LoanDetails::create($createLoanDetails);
                    // dd($message);
                    // \DB::enableQueryLog();
                    // var_dump($studyProgramAssets[$i] == $checkAssets->study_program_id);exit();
                    $studyProgramAssets = Assets::orderBy('study_program_id')->whereIn('id', $asset_ids)->get();
                    if($sortNumber !== $studyProgramAssets[$i]['study_program_id']){
                        $loaner_name = Auth::user()->name;
                        $loaner_code = Auth::user()->code;
                        $loaner_code_type = Auth::user()->code_type;
                        $adminPhone = User::where('study_program_id', $studyProgramAssets[$i]['study_program_id'])->pluck('phone');
                        
                        if($adminPhone->isEmpty() === FALSE) {
                            for ($rowPhone= 0; $rowPhone < count($adminPhone); $rowPhone++) { 
                                if($adminPhone[$rowPhone]) {
                                    $strPhone = implode('|', (array) $adminPhone[$rowPhone]);
                                    // var_dump($adminNumber);exit();
                                    $getCodeLoans = Loans::
                                    where('id', $loan_id)
                                    ->pluck('code');
                                    if($getCodeLoans){
                                        $code = $getCodeLoans[0];
                                        if($loaner_code_type == "0") {
                                            $strUserCode = 'NIM';
                                        } else {
                                            $strUserCode = 'NIDN';                                        
                                        }
                                        $message = "Anda mendapatkan *Perubahan Permintaan Peminjaman*!\n\nRincian Permintaan\nNama peminjam: *$loaner_name*\n$strUserCode: *$loaner_code*\nKode: *$code*\n Periode: *$range*\n\nLihat detailnya melalui tautan berikut: ";
                                        $this->loansRequestService->sendWhatsappNotification($message, $strPhone);
                                    }
                                    // dd($adminPhone[$rowPhone]);
                                }
                            }
                        }
                    }   
                    $sortNumber = $studyProgramAssets[$i]['study_program_id'];
                } else {
                    return $this->sendError('Error!', [
                        'error' =>
                        'Seluruh aset yang dipilih dalam keadaan tidak tersedia!'
                    ]);
                }
            }

            $superAdminPhone = User::role('Super-Admin')->pluck('phone');
            if($superAdminPhone->isEmpty() === FALSE) {
                for ($rowPhone= 0; $rowPhone < count($superAdminPhone); $rowPhone++) {
                    if($superAdminPhone[$rowPhone]){
                        // dd($superAdminPhone);
                        $strPhone = implode('|', (array) $superAdminPhone[$rowPhone]);
                        // var_dump($adminNumber);exit();
                        if($loaner_code_type == "0") {
                            $strUserCode = 'NIM';
                        } else {
                            $strUserCode = 'NIDN';                                        
                        }
                        $message = "Anda mendapatkan *Permintaan Peminjaman Baru*!\n\nRincian Permintaan\nNama peminjam: *$loaner_name*\n$strUserCode: *$loaner_code*\nKode: *$code*\n Periode: *$range*\n\nLihat detailnya melalui tautan berikut: ";
                        $this->loansRequestService->sendWhatsappNotification($message, $strPhone);
                    }
                }
            }
            
            $success['message'] = "Permintaan peminjaman berhasil diupdate!";
            // $success['data'] = $updateDataAsset;
            return $this->sendResponse($success, 'Update data');
        } catch (\Throwable $th) {
            return $this->sendError('Error!', $th);
        }
    }

    /**
     * Confirmation Loans Request
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */

     public function confirmation(Request $request)
     {
         // return "Cek";exit();
        try {
            sleep(5);
            $id = $request->id;
            $lender_id = Auth::user()->id;
            $status = $request->status;
            // \DB::enableQueryLog();
            $checkLoans = Loans::find($id);
            // dd(\DB::getQueryLog());
            
            if(!$checkLoans){
                return $this->sendError('Error!', ['error'=> 'Tidak ada data yang dipilih']);
            }

            // dd(!$checkLoans->status != "0");

            if($checkLoans->status != "0") {
                return $this->sendError('Error!', ['error'=> 'Status peminjaman bukan permintaan!']);
            }

            $date = Carbon::parse($checkLoans->date);
            $due_date = Carbon::parse($checkLoans->due_date);
            $diff = $date->diff($due_date);

            if($diff->d !== 0) {
                $hours = $diff->d * 24;
            } elseif($diff->h !== 0) {
                $hours = $diff->h;
            }
            // dd($hours);
            
            $due_date = Carbon::now()->addHours($hours);

            $checkLoans->lender_id = $lender_id;
            $checkLoans->date = Carbon::now();
            $checkLoans->due_date = $due_date;
            $checkLoans->status = $status;
            $checkLoans->save();

            $code = $checkLoans->code;
            $loaner = User::find($checkLoans->loaner_id);
            $loaner_name = $loaner->name;
            $loaner_phone = $loaner->phone;
            
            // dd($getLoanerPhone->phone);
            
            if($checkLoans->status == "1") {
                $confirmation = "Selamat, Permintaan Anda DISETUJUI!";
                $instruction = "\nSilakan temui Admin dari masing-masing Program Studi terkait! Terima kasih.\n";
            } elseif($checkLoans->status == "2") {
                $confirmation = "Mohon maaf, Permintaan Anda DITOLAK.";
                $instruction = "";
            }
            $message = "Anda mendapatkan *Konfirmasi Permintaan Peminjaman*!\n\nRincian Permintaan\nNama peminjam: *$loaner_name*\nKode: *$code*\nPesan Konfirmasi: \n*$confirmation*$instruction\n\nLihat detailnya melalui tautan berikut: ";
            $this->loansRequestService->sendWhatsappNotification($message, $loaner_phone);

            $success['message'] = "Pesan konfirmasi untuk permintaan peminjaman tersebut telah kami kirim melalui Pesan WhatsApp Peminjam!";
                // $success['total_restored'] = $totalRestore;
            return $this->sendResponse($success, 'Konfirmasi Peminjaman Berhasil!');
        } catch (\Throwable $th) {
            return $this->sendError('Error!', $th);
        }
     }

    /**
     * Put Loan Request into trash
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */

    public function delete(Request $request)
    {
        try {
            // dd(Auth::user()->name);
            sleep(5);
            $id = $request->id;
            $loaner_id = Auth::user()->id;
            // dd($ids);
            // \DB::enableQueryLog();
            $checkLoans = Loans::where('id', $id)->where('loaner_id', $loaner_id)->first();
            // dd(\DB::getQueryLog());
            // dd($checkLoans);
            if(!$checkLoans){
                return $this->sendError('Error!', ['error'=> 'Tidak ada data permintaan peminjaman yang dihapus!']);
            }

            // dd($checkLoans->status);

            if($checkLoans->status != "0"){
                return $this->sendError('Error!', ['error'=> 'Status transaksi peminjaman bukan permintaan!']);
            }
            // \DB::enableQueryLog();
            $deleteLoans = Loans::find($id);
            // dd(\DB::getQueryLog());\
            $totalDelete = 0;
            if($deleteLoans) {
                $getAssetFromLoanDetails = LoanDetails::
                where('loan_id', $id)->get();
                for($i = 0; $i < count($getAssetFromLoanDetails); $i++) {
                    $unSetStatusAssets = Assets::find($getAssetFromLoanDetails[$i]['asset_id']);
                    if($unSetStatusAssets->status == "1"){
                        $unSetStatusAssets->status = "0";
                        $unSetStatusAssets->save();
                    }
                    // dd($getAssetFromLoanDetails[$i]);
                    $getAssetFromLoanDetails[$i]['deleted_at'] = Carbon::now();
                    $getAssetFromLoanDetails[$i]->delete();
                }     
            }
            $deleteLoans->deleted_at = Carbon::now();
            $deleteLoans->delete();
            $totalDelete++;

            $tokenMsg = Str::random(15);
            $success['token'] = $tokenMsg;
            $success['message'] = "Delete selected data";
            $success['total_deleted'] = $totalDelete;
            return $this->sendResponse($success, 'Data terpilih berhasil dihapus');
        } catch (\Throwable $th) {
            return $this->sendError('Error!', $th);
        }
    }

}
