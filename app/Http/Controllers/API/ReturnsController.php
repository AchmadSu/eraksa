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
use App\Models\ReturnDetails;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\API\BaseController;
use App\Services\Returns\ReturnsRequestService;

class ReturnsController extends BaseController
{
    public $returnsRequestService;

    public function __construct(
        ReturnsRequestService $returnsRequestService,
    )
    {
        $this->returnsRequestService = $returnsRequestService;
    }

    /** ATTRIVE RETURNS DATA */

    /** 
     * Get All returns
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
    */

    public function index(Request $request){
        try {
            sleep(5);
            $code = $request->code;
            $loaner_keyWords = $request->loaner_keyWords;
            $loaner_ids = array();
            $recipient_keyWords = $request->recipient_keyWords;
            $recipient_ids = array();
            $dateOne = $request->dateOne;
            $dateTwo = $request->dateTwo;
            $loan_code = $request->loan_code;
            $loan_ids = array();
            $order = $request->order;
            $skip = $request->skip;
            $take = $request->take;
            $trash = $request->trash;
            
            $from = date($dateOne);
            $to = date($dateTwo);
            
            if(isset($dateTwo)){
                if($from > $to){
                    return $this->sendError('Error!', [
                        'error' => 
                        'Parameter tanggal salah. Tanggal pertama harus lebih kecil atau sama dengan tanggal kedua!'
                    ]);
                }
            }
            
            // dd($request->loaner_ids);
            // \DB::enableQueryLog();
            if($loaner_keyWords){
                $loaner = User::
                where('name', 'like', '%'.$loaner_keyWords.'%')
                ->orWhere('email', 'like', '%'.$loaner_keyWords.'%')
                ->get();
                foreach ($loaner as $rowLoaner) {
                    $loaner_ids[] = $rowLoaner->id;
                }
            }
            
            if($recipient_keyWords){
                $recipient = User::
                where('name', 'like', '%'.$recipient_keyWords.'%')
                ->orWhere('email', 'like', '%'.$recipient_keyWords.'%')
                ->get();
                foreach ($recipient as $rowRecipient) {
                    $recipient_ids[] = $rowRecipient->id;
                }
            }
            
            // dd($recipient_ids);
            if($loan_code) {
                $loan = Loans::where('code', 'like', '%'.$loan_code.'%')->get();
                // $loans = Loans::whereIn('loaner_id', $loaner_ids)->get();
                foreach ($loan as $rowloan) {
                    $loan_ids[] = $rowloan->id;
                }
            }
            \DB::enableQueryLog();
            // dd($request->loaner_ids == NULL);
            
            $returns = Returns::join('users as loaners', 'returns.loaner_id', '=', 'loaners.id')
            ->join('users as recipients', 'returns.recipient_id', '=', 'recipients.id')
            ->join('loans', 'returns.loan_id', '=', 'loans.id')
            ->when(isset($code))
            ->where('returns.code', 'like', '%'.$code.'%')
            ->when(isset($loaner_keyWords))
            ->whereIn('returns.loaner_id', $loaner_ids)
            ->when(isset($recipient_keyWords))
            ->whereIn('returns.recipient_id', $recipient_ids)
            ->when(isset($dateOne) && isset($dateTwo))
            ->whereBetween('returns.date', [$from.' 00:00:00', $to.' 23:59:59'])
            ->when(isset($dateOne) && !isset($dateTwo))
            ->whereBetween('returns.date', [$from.' 00:00:00', $from.' 23:59:59'])
            ->when(isset($loan_code))
            ->whereIn('returns.loan_id', $loan_ids)
            ->select(
                'returns.id as id',
                'returns.code as code',
                'returns.loan_id as loan_id',
                'loans.code as loan_code',
                'returns.loaner_id as loaner_id',
                'loaners.name as loaner_name',
                'returns.recipient_id as recipient_id',
                'recipients.name as recipient_name',
                'returns.date as date',
                'returns.created_at as created_at',
                'returns.updated_at as updated_at',
            )
            ->when($order)
            ->orderBy($order, 'ASC')
            ->when($trash == 1)
            ->onlyTrashed()
            ->get();
                // dd("test");
                // dd(Loans::all());
                // dd(Auth::user()->name);
                // dd(\DB::getQueryLog());
                // dd($loans);
            if ($returns->isEmpty()) {
                return $this->sendError('Error!', ['error' => 'Data tidak ditemukan!']);
            }
            $count = $returns->count();
            // \DB::enableQueryLog();
            // $countDelete = Returns::onlyTrashed()->count();
            // dd(\DB::getQueryLog());
            $success['count'] = $count;
            // $success['countDelete'] = $countDelete;
            $success['returns']= $returns
                ->when(isset($skip))
                ->skip($skip)
                ->when(isset($take))
                ->take($take)
            ;
            return $this->sendResponse($success, 'Displaying all Returns data');
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
            // \DB::enableQueryLog();
            // $returns = Returns::find($id);
            $returns = Returns::
                join('users as loaners', 'returns.loaner_id', '=', 'loaners.id')
                ->join('users as recipients', 'returns.recipient_id', '=', 'recipients.id')
                ->join('loans as loans', 'returns.loan_id', '=', 'loans.id')
                ->select(
                    'returns.id as id',
                    'returns.code as code',
                    'returns.date as date',
                    'loans.id as loan_id',
                    'loans.code as loan_code',
                    'loans.status as loan_status',
                    'returns.loaner_id as loaner_id', 
                    'loaners.name as loaner_name',
                    'loaners.code_type as loaner_code_type', 
                    'loaners.code as loaner_code', 
                    'returns.recipient_id as recipient_id', 
                    'recipients.name as recipient_name',
                    'recipients.code_type as recipient_code_type', 
                    'recipients.code as recipient_code',
                )->find($id);
            
            $return_details = ReturnDetails::
                join('assets', 'return_details.asset_id', '=', 'assets.id')
                ->join('users as users', 'assets.user_id', '=', 'users.id')
                ->join('category_assets as category_assets', 'assets.category_id', '=', 'category_assets.id')
                ->join('placements as placements', 'assets.placement_id', '=', 'placements.id')
                ->join('study_programs as study_programs', 'assets.study_program_id', '=', 'study_programs.id')
                ->where('return_details.return_id', $id)
                ->select(
                    'return_details.id',
                    'return_details.return_id',
                    'return_details.asset_id',
                    'assets.name as asset_name',
                    'assets.code as asset_code',
                    'assets.condition as asset_condition',
                    'assets.status as asset_status',
                    'assets.deleted_at as asset_deleted_at',
                    'assets.date as asset_date',
                    'assets.placement_id as asset_placement_id', 
                    'placements.name as asset_placement_name', 
                    'assets.category_id as asset_category_id', 
                    'category_assets.name as asset_category_name', 
                    'assets.user_id as asset_creator_id',
                    'users.name as asset_creator_name',
                    'assets.study_program_id as asset_study_program_id',
                    'study_programs.name as asset_study_program_name',
                )
                ->get();
            // dd(\DB::getQueryLog());
            if (!$returns) {
                return $this->sendError('Error!', ['error' => 'Data tidak ditemukan!']);
            }
            $success['returns'] = $returns;
            $success['return_details'] = $return_details;
            return $this->sendResponse($success, 'Returns detail by Id');
        } catch (\Throwable $th) {
            return $this->sendError('Error!', ['error' => "Permintaan tidak dapat dilakukan."]);
        }
        
    }

    /**
     * Create Request Returns
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */

     public function create(Request $request){
        try {
            // dd();
            // $this->loansRequestService->sendWhatsappNotification("Test", Auth::user()->phone);
            sleep(2);
            $recipient_id = Auth::user()->id;
            $loan_id = $request->loan_id;
            \DB::enableQueryLog();
            $checkLoans = Loans::
            // where('id', $loan_id)
            // ->where('status', "1")
            find($loan_id);
            // dd(\DB::getQueryLog());

            // dd($checkLoans);

            if($checkLoans){
                if($checkLoans->status == "0"){
                    return $this->sendError('Error!', [
                        'error' =>
                        'Status transaksi peminjaman tersebut adalah permintaan, tidak dapat mengeksekusi pengembalian!'
                    ]);
                } elseif($checkLoans->status == "2") {
                    return $this->sendError('Error!', [
                        'error' =>
                        'Status transaksi peminjaman tersebut adalah ditolak, tidak dapat mengeksekusi pengembalian!'
                    ]);
                } elseif($checkLoans->status == "3") {
                    return $this->sendError('Error!', [
                        'error' =>
                        'Status transaksi peminjaman tersebut adalah selesai, tidak dapat mengeksekusi pengembalian!'
                    ]);
                }
            }

            // dd($checkLoans->loaner_id);

            $getAssetFromLoanDetails = LoanDetails::
            where('loan_id', $loan_id)->get();
            // dd($getAssetFromLoanDetails[0]['asset_id']);
            if($getAssetFromLoanDetails) {
                for($i = 0; $i < count($getAssetFromLoanDetails); $i++) {
                    $setStatusAssets = Assets::find($getAssetFromLoanDetails[$i]['asset_id']);
                    if($setStatusAssets->status == "1"){
                        // dd("test");
                        $setStatusAssets->status = "0";
                        $setStatusAssets->save();
                    }
                }
                
                $loaner_id = $checkLoans->loaner_id;
                $getLoaner = User::find($loaner_id);

                // dd($getLoaner->phone);
                $date = date("d/m/Y");
                $inv = rand(100000, 999999);
                $strInv = "$inv";
                $code = "INV-".$date."-ERK-KEMBALI"."/".$strInv;
                
                $checkCodeLoans = Returns::
                where('code', $code)
                ->first();

                if($checkCodeLoans)
                {
                    return $this->sendError('Error!', [
                        'error' =>
                        'Kode peminjaman sudah ada, Gunakan kode yang lain!'
                    ]);
                }

                $returnArray = array(
                    "loan_id" => $loan_id,
                    "code" => $code,
                    "loaner_id" => $getLoaner->id,
                    "recipient_id" => $recipient_id,
                    "date" => Carbon::now()
                );
                // dd($getAssetFromLoanDetails[0]['asset_id']);
                $createReturn = Returns::create($returnArray);
                $return_id = $createReturn->id;
                $getRecipient = User::find($recipient_id);
                $asset_ids = array();
                // dd($getLoaner->id);
                $sortNumber = 0;
                for ($i=0; $i < count($getAssetFromLoanDetails) ; $i++) {
                    $asset_id = $getAssetFromLoanDetails[$i]['asset_id'];
                    // dd($checkAssets);
                    $createReturnDetails = array(
                        "asset_id" => $asset_id,
                        "return_id" => $return_id
                    );
                    ReturnDetails::create($createReturnDetails);
                }

                $checkLoans->status = '3';
                $checkLoans->return_id = $return_id;
                $checkLoans->save();
                
                foreach ($getAssetFromLoanDetails as $rowGetAsset) {
                    $asset_ids[] = $rowGetAsset->asset_id;
                }
                
                $loaner_name = $getLoaner->name;
                $loaner_code = $getLoaner->code;
                $loaner_code_type = $getLoaner->code_type;
                $recipient_name = $getRecipient->name;
                $recipient_code = $getRecipient->code;
                $recipient_code_type = $getRecipient->code_type;
                $encodeId = base64_encode($createReturn->id);
                $link = getenv("APP_URL_FE")."/loans/returnDetails?data=".$encodeId;
                
                $superAdminPhone = User::role('Super-Admin')->pluck('phone');
                if(!$superAdminPhone->isEmpty()) {
                    for ($rowPhone= 0; $rowPhone < count($superAdminPhone); $rowPhone++) {
                        if($superAdminPhone[$rowPhone]){
                            // dd($superAdminPhone);
                            $strPhone = implode('|', (array) $superAdminPhone[$rowPhone]);
                            // var_dump($adminNumber);exit();
                            if($loaner_code_type == "0") {
                                $strUserCode = 'NISN';
                            } elseif($loaner_code_type == "1") {
                                $strUserCode = 'NUPTK';                                        
                            } elseif($loaner_code_type == "2") {
                                $strUserCode = 'NIP';                                        
                            }
                            
                            if($recipient_code_type == "0") {
                                $strRecipientCode = 'NISN';
                            } elseif($recipient_code_type == "1") {
                                $strRecipientCode = 'NUPTK';                                        
                            } elseif($recipient_code_type == "2") {
                                $strRecipientCode = 'NIP';                                        
                            }
                            $message = "Anda mendapatkan pesan *Pengembalian Peminjaman*!\n\nRincian Pengembalian\nNama peminjam: *$loaner_name*\n$strUserCode: *$loaner_code*\nNama penerima aset: *$recipient_name*\n$strRecipientCode: *$recipient_code*\nKode Pengembalian: *$code*\n\nLihat detailnya melalui tautan berikut: \n$link";
                            try {
                                $this->returnsRequestService->sendWhatsappNotification($message, $strPhone);
                                $success['whatsapp'] = "WhatsApp berhasil dikirim.";
                            } catch (\Throwable $th) {
                                $success['whatsapp'] = "WhatsApp gagal dikirim. Error: ".$th;
                            }
                        }
                    }
                }
                $success['message'] = "Konfirmasi pengembalian aset berhasil!";
                return $this->sendResponse($success, 'Konfirmasi pengembalian berhasil!');
                // dd($getRecipient->name);

            } else {
                return $this->sendError('Error!', [
                    'error' =>
                    'Tidak ada aset yang dikembalikan!'
                ]);
            }    
        } catch (\Throwable $th) {
            return $this->sendError('Error!', ['error' => "Permintaan tidak dapat dilakukan. Periksa kembali jaringan anda!"]);
        } 
    }
    
}
