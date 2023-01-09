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
                                        $message = "Anda mendapatkan permintaan peminjaman baru!\n\nRincian Permintaan\nNama peminjam: *$loaner_name*\nNIM: *$loaner_code*\nKode: *$code*\n Periode: *$range*\n\nLihat detailnya melalui tautan berikut: ";
                                    } else {
                                        $message = "Anda mendapatkan permintaan peminjaman baru!\n\nRincian Permintaan\nNama peminjam: *$loaner_name*\nNIDN: *$loaner_code*\nKode: *$code*\n Periode: *$range*\n\nLihat detailnya melalui tautan berikut: ";
                                    }
                                    $this->loansRequestService->sendWhatsappNotification($message, $strPhone);
                                    // dd($adminPhone[$rowPhone]);
                                }
                            }
                        }
                    }   
                    $sortNumber = $studyProgramAssets[$i]['study_program_id'];
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
                            $message = "Anda mendapatkan permintaan peminjaman baru!\n\nRincian Permintaan\nNama peminjam: *$loaner_name*\nNIM: *$loaner_code*\nKode: *$code*\n Periode: *$range*\n\nLihat detailnya melalui tautan berikut: ";
                        } else {
                            $message = "Anda mendapatkan permintaan peminjaman baru!\n\nRincian Permintaan\nNama peminjam: *$loaner_name*\nNIDN: *$loaner_code*\nKode: *$code*\n Periode: *$range*\n\nLihat detailnya melalui tautan berikut: ";
                        }
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
     * Update Asset
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    
    public function update(Request $request)
    {
        try {
            sleep(5);
            $id = $request->id;
            $updateDataAsset = Loans::find($id);
            
            $name = $request->name;
            $new_code = $request->new_code;
            $category_id = $request->category_id;
            $user_id = $request->user_id;
            $placement_id = $request->placement_id;
            $date = $request->date;
            $condition = $request->condition;
            $status = $request->status;
            $study_program_id = $request->study_program_id;
            
            if ($new_code == NULL) {
                $validator = Validator::make($request->all(), [
                    'name' => 'required',
                    'user_id' => 'required|numeric',
                    'date' => 'required',
                    'condition' => 'required',
                    'status' => 'required',
                    'category_id' => 'required|numeric',
                    'placement_id' => 'required|numeric',
                    'study_program_id' => 'required|numeric',
                ]);
                
            } elseif ($new_code != NULL) {
                $validator = Validator::make($request->all(),[
                    'name' => 'required',
                    'user_id' => 'required',
                    'new_code' => 'required|unique:assets,code',
                    'date' => 'required',
                    'condition' => 'required',
                    'status' => 'required',
                    'category_id' => 'required|numeric',
                    'placement_id' => 'required|numeric',
                    'study_program_id' => 'required|numeric',
                ]);
                $updateDataAsset->code = $new_code;
            }

            if ($validator->fails()) {
                return $this->sendError('Error!', $validator->errors());
            }

            $updateDataAsset->name = $name;
            $updateDataAsset->user_id = $user_id;
            $updateDataAsset->date = $date;
            $updateDataAsset->placement_id = $placement_id;
            $updateDataAsset->condition = $condition;
            $updateDataAsset->status = $status;
            $updateDataAsset->category_id = $category_id;
            $updateDataAsset->study_program_id = $study_program_id;
            // dd($data);exit();

            $updateDataAsset->save();
            $tokenMsg = Str::random(15);
            $success['token'] = $tokenMsg;
            $success['message'] = "Asset berhasil diupdate!";
            $success['data'] = $updateDataAsset;
            return $this->sendResponse($success, 'Update data');
        } catch (\Throwable $th) {
            return $this->sendError('Error!', $th);
        }
    }

    /**
     * Put Multiple Assets into trash
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */

    public function delete(Request $request)
    {
        try {
            sleep(5);
            $ids = $request->ids;
            // dd($ids);
            // \DB::enableQueryLog();
            $checkAssets = Assets::whereIn('id', $ids)->get();
            // dd(\DB::getQueryLog());
            // dd($checkAssets);
            if($checkAssets->isEmpty()){
                return $this->sendError('Error!', ['error'=> 'Tidak ada data yang dihapus!']);
            }
            // \DB::enableQueryLog();
            $deleteAssets = Assets::findMany($ids);
            // dd(\DB::getQueryLog());\
            $totalDelete = 0;
            foreach($deleteAssets as $rowAssets){
                // dd($rowAssets->id);
                // if($rowAssets > 0){
                // dd($rowAssets->id);
                $deleteAssets = Assets::find($rowAssets->id);
                $deleteAssets->deleted_at = Carbon::now();
                $deleteAssets->delete();
                $totalDelete++;
                // }
            }

            $tokenMsg = Str::random(15);
            $success['token'] = $tokenMsg;
            $success['message'] = "Delete selected data";
            $success['total_deleted'] = $totalDelete;
            return $this->sendResponse($success, 'Data terpilih berhasil dihapus');
        } catch (\Throwable $th) {
            return $this->sendError('Error!', $th);
        }
    }

    /**
     * Restore Multiple Assets from trash
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
            $checkAssets = Assets::onlyTrashed()->whereIn('id', $ids)->get();
            // dd(\DB::getQueryLog());
            
            if($checkAssets->isEmpty()){
                return $this->sendError('Error!', ['error'=> 'Tidak ada data yang dipulihkan']);
            }

            // \DB::enableQueryLog();
            $restoreAssets = Assets::onlyTrashed()->findMany($ids);
            // dd(\DB::getQueryLog());
            $totalRestore = 0;
            
            foreach($restoreAssets as $rowAssets){
                $restoreAssets = Assets::onlyTrashed()->find($rowAssets->id);
                $restoreAssets->deleted_at = null;
                $restoreAssets->restore();
                $totalRestore++;
            }

            $tokenMsg = Str::random(15);
            $success['token'] = $tokenMsg;
            $success['message'] = "Restore asset data";
            $success['total_restored'] = $totalRestore;
            return $this->sendResponse($success, 'Data dipulihkan');
        } catch (\Throwable $th) {
            return $this->sendError('Error!', $th);
        }
    }
}
