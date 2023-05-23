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
use App\Models\CategoryAssets;

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
            $sleep = $request->sleep;
            if($sleep) {
                sleep($sleep);
            } else {
                sleep(5);
            }

            $code = $request->code;
            $loaner_keyWords = $request->loaner_keyWords;
            $loaner_id = $request->loaner_id;
            $loaner_ids = array();
            $lender_keyWords = $request->lender_keyWords;
            $lender_ids = array();
            $lender_id = $request->lender_id;
            $dateOne = $request->dateOne;
            $dateTwo = $request->dateTwo;
            $status = $request->status;
            $dueDateOne = $request->dueDateOne;
            $dueDateTwo = $request->dueDateTwo;
            $skip = $request->skip;
            $take = $request->take;
            $trash = $request->trash;
            $orderDate = $request->orderDate;
            $orderDueDate = $request->orderDueDate;

            $from = date($dateOne);
            $to = date($dateTwo);
            
            $dueFrom = date($dueDateOne);
            $dueTo = date($dueDateTwo);

            if(isset($dateTwo)){
                if($from > $to){
                    return $this->sendError('Error!', [
                        'error' => 
                        'Parameter tanggal salah. Tanggal pertama harus lebih kecil atau sama dengan tanggal kedua!'
                    ]);
                }
            }

            if(isset($dueDateTwo)){
                if($dueFrom > $dueTo){
                    return $this->sendError('Error!', [
                        'error' => 
                        'Parameter tanggal salah. Tanggal tenggang waktu pertama harus lebih kecil atau sama dengan tanggal tenggang waktu kedua!'
                    ]);
                }
            }
            
            // dd($request->loaner_ids);
            if($loaner_keyWords){
                $loaner = User::
                where('name', 'like', '%'.$loaner_keyWords.'%')
                ->orWhere('email', 'like', '%'.$loaner_keyWords.'%')
                ->get();
                foreach ($loaner as $rowLoaner) {
                    $loaner_ids[] = $rowLoaner->id;
                }
            }

            if($lender_keyWords){
                $lender = User::
                where('name', 'like', '%'.$lender_keyWords.'%')
                ->orWhere('email', 'like', '%'.$lender_keyWords.'%')
                ->get();
                foreach ($lender as $rowLender) {
                    $lender_ids[] = $rowLender->id;
                }
            }

            $loans = Loans::
            join('users as loaners', 'loans.loaner_id', '=', 'loaners.id')
            ->leftJoin('users as lenders', 'loans.lender_id', '=', 'lenders.id')
            ->leftJoin('returns as returns', 'loans.return_id', '=', 'returns.id')
            ->leftJoin('users as recipients', 'returns.recipient_id', '=', 'recipients.id')
            ->when(isset($code))
            ->where('loans.code', 'like', '%'.$code.'%')
            ->when(isset($loaner_id))
            ->where('loans.loaner_id', $loaner_id)
            ->when(isset($loaner_keyWords))
            ->whereIn('loans.loaner_id', $loaner_ids)
            ->when(isset($lender_id))
            ->where('loans.lender_id', $lender_id)
            ->when(isset($lender_keyWords))
            ->whereIn('loans.lender_id', $lender_ids)
            ->when(isset($dateOne) && isset($dateTwo))
            ->whereBetween('loans.date', [$from.' 00:00:00', $to.' 23:59:59'])
            ->when(isset($dateOne) && !isset($dateTwo))
            ->whereBetween('loans.date', [$from.' 00:00:00', $from.' 23:59:59'])
            ->when(isset($dueDateOne) && isset($dueDateTwo))
            ->whereBetween('loans.due_date', [$dueFrom.' 00:00:00', $dueTo.' 23:59:59'])
            ->when(isset($dueDateOne) && !isset($dueDateTwo))
            ->whereBetween('loans.due_date', [$dueFrom.' 00:00:00', $dueFrom.' 23:59:59'])
            ->when(isset($status))
            ->where('loans.status', $status)
            ->select(
                'loans.id as id',
                'loans.code as code',
                'loans.status as status',
                'loans.date as date',
                'loans.due_date as due_date',
                'loans.return_id as return_id',
                'loans.loaner_id as loaner_id', 
                'loaners.name as loaner_name', 
                'loaners.code_type as loaner_code_type', 
                'loaners.code as loaner_code', 
                'loans.lender_id as lender_id', 
                'lenders.name as lender_name',
                'lenders.code_type as lender_code_type', 
                'lenders.code as lender_code',  
                'recipients.id as recipient_id',
                'recipients.name as recipient_name',
                'recipients.code_type as recipient_code_type', 
                'recipients.code as recipient_code',  
            )
            ->when($trash == 1)
            ->onlyTrashed()
            ->when($orderDate)
            ->orderby('date', $orderDate)
            ->when($orderDueDate)
            ->orderby('due_date', $orderDueDate)
            ->get();
                // dd(Loans::all());
                // dd(Auth::user()->name);
            // \DB::enableQueryLog();
            
            // dd($loans);
            if ($loans->isEmpty()) {
                return $this->sendError('Error!', ['error' => 'Data tidak ditemukan!']);
            }
            $count = $loans->count();
            $countDelete = Loans::onlyTrashed()->count();
            // dd(\DB::getQueryLog());
            $success['count'] = $count;
            $success['countDelete'] = $countDelete;
            $success['loans'] = $loans
                ->when(isset($skip))
                ->skip($skip)
                ->when(isset($take))
                ->take($take)
            ;
            return $this->sendResponse($success, 'Displaying all Loans data');
        } catch (\Throwable $th) {
            return $this->sendError('Error!', ['error' => "Permintaan tidak dapat dilakukan."]);
        }
    }
    
    /** 
     * Get History Assets Loans
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
    */

    public function historyAssets(Request $request){
        try {
            $sleep = $request->sleep;
            if($sleep) {
                sleep($sleep);
            } else {
                sleep(5);
            }

            $asset_id = $request->asset_id;
            $dateOne = $request->dateOne;
            $dateTwo = $request->dateTwo;
            $dueDateOne = $request->dueDateOne;
            $dueDateTwo = $request->dueDateTwo;
            $skip = $request->skip;
            $take = $request->take;
            $trash = $request->trash;
            $orderDate = $request->orderDate;
            $orderDueDate = $request->orderDueDate;

            $from = date($dateOne);
            $to = date($dateTwo);
            
            $dueFrom = date($dueDateOne);
            $dueTo = date($dueDateTwo);

            if(!isset($asset_id)){
                return $this->sendError('Error!', [
                    'error' => 
                    'Tidak ada riwayat aset yang dipilih!'
                ]);
            }

            
            $checkAssets = Assets::find($asset_id);
            
            if(!$checkAssets){
                return $this->sendError('Error!', [
                    'error' => 
                    'Tidak ada riwayat aset yang dipilih!'
                ]);
            }

            // dd($checkAssets);

            if(isset($dateTwo)){
                if($from > $to){
                    return $this->sendError('Error!', [
                        'error' => 
                        'Parameter tanggal salah. Tanggal pertama harus lebih kecil atau sama dengan tanggal kedua!'
                    ]);
                }
            }

            if(isset($dueDateTwo)){
                if($dueFrom > $dueTo){
                    return $this->sendError('Error!', [
                        'error' => 
                        'Parameter tanggal salah. Tanggal tenggang waktu pertama harus lebih kecil atau sama dengan tanggal tenggang waktu kedua!'
                    ]);
                }
            }
            // dd($request->loaner_ids)

            // \DB::enableQueryLog();
            $loans = Loans::
            join('loan_details as loan_details', 'loans.id', '=', 'loan_details.loan_id')
            ->leftJoin('returns as returns', 'loans.return_id', '=', 'returns.id')
            ->when(isset($dateOne) && isset($dateTwo))
            ->whereBetween('loans.date', [$from.' 00:00:00', $to.' 23:59:59'])
            ->when(isset($dateOne) && !isset($dateTwo))
            ->whereBetween('loans.date', [$from.' 00:00:00', $from.' 23:59:59'])
            ->when(isset($dueDateOne) && isset($dueDateTwo))
            ->whereBetween('loans.due_date', [$dueFrom.' 00:00:00', $dueTo.' 23:59:59'])
            ->when(isset($dueDateOne) && !isset($dueDateTwo))
            ->whereBetween('loans.due_date', [$dueFrom.' 00:00:00', $dueFrom.' 23:59:59'])
            ->where('loan_details.asset_id', $asset_id)
            ->whereIn('loans.status', ['0', '1', '3'])
            ->select(
                'loans.id as id',
                'loans.code as code',
                'loans.status as status',
                'loans.date as date',
                'loans.due_date as due_date',
                'loans.return_id as return_id',  
            )
            ->when($trash == 1)
            ->onlyTrashed()
            ->when($orderDate)
            ->orderby('date', $orderDate)
            ->when($orderDueDate)
            ->orderby('due_date', $orderDueDate)
            ->get();
                // dd(Loans::all());
                // dd(Auth::user()->name);
            // dd(\DB::getQueryLog());
            // \DB::enableQueryLog();
            
            // dd($loans);
            if ($loans->isEmpty()) {
                return $this->sendError('Error!', ['error' => 'Data tidak ditemukan!']);
            }

            $success['asset_name'] = $checkAssets->name;
            $success['asset_code'] = $checkAssets->code;
            $count = $loans->count();
            // dd(\DB::getQueryLog());
            $success['count'] = $count;
            $success['loans'] = $loans
                ->when(isset($skip))
                ->skip($skip)
                ->when(isset($take))
                ->take($take)
            ;
            return $this->sendResponse($success, 'Displaying all Loans data');
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
            // dd(Auth::user()->id);
            $loans = Loans::find($id);
            // $loaner_id = $request->loaner_id;
            if (!$loans) {
                return $this->sendError('Error!', ['error' => 'Data tidak ditemukan!']);
            }
            // \DB::enableQueryLog();
            $loans = Loans::join('users as loaners', 'loans.loaner_id', '=', 'loaners.id')
            ->leftJoin('users as lenders', 'loans.lender_id', '=', 'lenders.id')
            ->leftJoin('returns as returns', 'loans.return_id', '=', 'returns.id')
            ->leftJoin('users as recipients', 'returns.recipient_id', '=', 'recipients.id')
            ->select(
                'loans.id as id',
                'loans.code as code',
                'loans.status as status',
                'loans.date as date',
                'loans.due_date as due_date',
                'loans.return_id as return_id',
                'loans.loaner_id as loaner_id', 
                'loaners.name as loaner_name', 
                'loaners.code_type as loaner_code_type', 
                'loaners.code as loaner_code', 
                'loans.lender_id as lender_id', 
                'lenders.name as lender_name',
                'lenders.code_type as lender_code_type', 
                'lenders.code as lender_code',  
                'recipients.id as recipient_id',
                'recipients.name as recipient_name',
                'recipients.code_type as recipient_code_type', 
                'recipients.code as recipient_code', 
            )->find($id);
            // dd(\DB::getQueryLog());
            if (!$loans) {
                return $this->sendError('Error!', ['error' => 'Data tidak ditemukan!']);
            }
            
            $loan_details = LoanDetails::
            join('assets', 'loan_details.asset_id', '=', 'assets.id')
            ->join('users as users', 'assets.user_id', '=', 'users.id')
            ->join('category_assets as category_assets', 'assets.category_id', '=', 'category_assets.id')
            ->join('placements as placements', 'assets.placement_id', '=', 'placements.id')
            ->join('study_programs as study_programs', 'assets.study_program_id', '=', 'study_programs.id')
            ->where('loan_details.loan_id', $id)
            ->select(
                'loan_details.id',
                'loan_details.loan_id',
                'loan_details.asset_id',
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
            $date = Carbon::parse($loans->date);
            $due_date = Carbon::parse($loans->due_date);
            $diff = $date->diff($due_date);

            if($diff->d !== 0) {
                $hours = $diff->d * 24;
            } elseif($diff->h !== 0) {
                $hours = $diff->h;
            }
            // dd(\DB::getQueryLog());
            $success['loans'] = $loans;
            $success['hours'] = $hours;
            $success['loan_details'] = $loan_details;
            return $this->sendResponse($success, 'Loans detail by Id');
        } catch (\Throwable $th) {
            return $this->sendError('Error!', ['error' => "Permintaan tidak dapat dilakukan."]);
        }
            
    }

    /** 
     * Get Percentage loans
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
    */

    public function percentage(Request $request){
        try {
            // sleep(1);
            $auth = Auth::user();
            $sleep = $request->sleep;
            if($sleep) {
                sleep($sleep);
            } else {
                sleep(5);
            }
            $dateOne = $request->dateOne;
            $dateTwo = $request->dateTwo;
            $status = $request->status;
            // $return_code = $request->return_code;
            $dueDateOne = $request->dueDateOne;
            $dueDateTwo = $request->dueDateTwo;
            
            $from = date($dateOne);
            $to = date($dateTwo);
            $dueFrom = date($dueDateOne);
            $dueTo = date($dueDateTwo);
            
            // dd(isset($dateTwo));
            if(isset($dateTwo)){
                if($from > $to){
                    return $this->sendError('Error!', [
                        'error' => 
                        'Parameter tanggal salah. Tanggal pertama harus lebih kecil atau sama dengan tanggal kedua!'
                    ]);
                }
            }

            if(isset($dueDateTwo)){
                if($dueFrom > $dueTo){
                    return $this->sendError('Error!', [
                        'error' => 
                        'Parameter tanggal salah. Tanggal tenggang waktu pertama harus lebih kecil atau sama dengan tanggal tenggang waktu kedua!'
                    ]);
                }
            }
            
            $loans = Loans::
            when(isset($dateOne) && !isset($dateTwo))
            ->where('loans.date', $from)
            ->when(isset($dateOne) && isset($dateTwo))
            ->whereBetween('loans.date', [$from.' 00:00:00', $to.' 23:59:59'])
            ->when(isset($dateOne) && !isset($dateTwo))
            ->whereBetween('loans.date', [$from.' 00:00:00', $from.' 23:59:59'])
            ->when(isset($dueDateOne) && isset($dueDateTwo))
            ->whereBetween('loans.due_date', [$dueFrom.' 00:00:00', $dueTo.' 23:59:59'])
            ->when(isset($dueDateOne) && !isset($dueDateTwo))
            ->whereBetween('loans.due_date', [$dueFrom.' 00:00:00', $dueFrom.' 23:59:59'])
            ->when(isset($status))
            ->where('loans.status', $status)
            ->select('id')
            ->get();

            $loans_ids = array();
            foreach ($loans as $rowLoans) {
                $loans_ids[] = $rowLoans->id;
            }
            // dd(\DB::getQueryLog());
            $countAll = Assets::
            when($auth->hasRole('Admin'))
            ->where('assets.study_program_id', $auth->study_program_id)
            ->count();
            // dd($loans);
            $countRequest = LoanDetails::
            join('assets as assets', 'loan_details.asset_id', '=', 'assets.id')
            ->when($auth->hasRole('Admin'))
            ->where('assets.study_program_id', $auth->study_program_id)->whereIn('loan_details.loan_id', $loans_ids)->count();
            if (!$countAll && !$countRequest) {
                return $this->sendError('Error!', ['error' => 'Data tidak ditemukan!']);
            }
            $success['countAll'] = $countAll;
            $success['countRequest'] = $countRequest;
            $success['fraction'] = number_format($countRequest)."/".number_format($countAll);
            $success['percentage'] = number_format((float)$countRequest/$countAll * 100, 0, '.', '');
            return $this->sendResponse($success, 'Displaying all Loans data');
        } catch (\Throwable $th) {
            return $this->sendError('Error!', ['error' => "Permintaan tidak dapat dilakukan."]);
        }
    }

    /** 
     * Get report per week loans
     * 
     *
     * @return \Illuminate\Http\Response
    */

    public function reportWeekly(){
        try {
            $from = Carbon::now()->subWeek()->startOfWeek();
            $to = Carbon::now()->subWeek()->endOfWeek();
            
            $loans = Loans::
            join('users as loaners', 'loans.loaner_id', '=', 'loaners.id')
            ->leftJoin('users as lenders', 'loans.lender_id', '=', 'lenders.id')
            ->leftJoin('returns as returns', 'loans.return_id', '=', 'returns.id')
            ->leftJoin('users as recipients', 'returns.recipient_id', '=', 'recipients.id')
            ->whereIn('loans.status', ['1', '3'])
            ->whereBetween('loans.date', [$from, $to])
            ->select(
                'loans.id as id',
                'loans.code as code',
                'loans.status as status',
                'loans.date as date',
                'loans.due_date as due_date',
                'loans.return_id as return_id',
                'loans.loaner_id as loaner_id', 
                'loaners.name as loaner_name', 
                'loaners.code_type as loaner_code_type', 
                'loaners.code as loaner_code', 
                'loaners.phone as loaner_phone', 
                'loans.lender_id as lender_id', 
                'lenders.name as lender_name',
                'lenders.code_type as lender_code_type', 
                'lenders.code as lender_code',  
                'recipients.id as recipient_id',
                'recipients.name as recipient_name',
                'recipients.code_type as recipient_code_type', 
                'recipients.code as recipient_code',  
            )->orderBy('date')
            ->get();
            if ($loans->isEmpty()) {
                return $this->sendError('Error!', ['error' => 'Data tidak ditemukan!']);
            }
            $count = $loans->count();

            $success['count'] = $count;
            $success['range1'] = $from->format('d/m/Y');
            $success['range2'] = $to->format('d/m/Y');
            $success['loans'] = $loans;
            return $this->sendResponse($success, 'Displaying all Loans data');
        } catch (\Throwable $th) {
            return $this->sendError('Error!', ['error' => "Permintaan tidak dapat dilakukan."]);
        }
    }


    /** 
     * Get report per month loans
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
    */

    public function reportMonthly(Request $request){
        try {
            $month = $request->month;
            $year = $request->year;
            $nameOfMonth = array(
                "1" => "Januari",
                "2" => "Februari",
                "3" => "Maret",
                "4" => "April",
                "5" => "Mei",
                "6" => "Juni",
                "7" => "Juli",
                "8" => "Agustus",
                "9" => "September",
                "10" => "Oktober",
                "11" => "November",
                "12" => "Desember",
            );
            $loans = Loans::
            join('users as loaners', 'loans.loaner_id', '=', 'loaners.id')
            ->leftJoin('users as lenders', 'loans.lender_id', '=', 'lenders.id')
            ->leftJoin('returns as returns', 'loans.return_id', '=', 'returns.id')
            ->leftJoin('users as recipients', 'returns.recipient_id', '=', 'recipients.id')
            ->whereYear('loans.date', '=', $year)
            ->whereMonth('loans.date', '=', $month)
            ->whereIn('loans.status', ['1', '3'])
            ->select(
                'loans.id as id',
                'loans.code as code',
                'loans.status as status',
                'loans.date as date',
                'loans.due_date as due_date',
                'loans.return_id as return_id',
                'loans.loaner_id as loaner_id', 
                'loaners.name as loaner_name', 
                'loaners.code_type as loaner_code_type', 
                'loaners.code as loaner_code', 
                'loaners.phone as loaner_phone', 
                'loans.lender_id as lender_id', 
                'lenders.name as lender_name',
                'lenders.code_type as lender_code_type', 
                'lenders.code as lender_code',  
                'recipients.id as recipient_id',
                'recipients.name as recipient_name',
                'recipients.code_type as recipient_code_type', 
                'recipients.code as recipient_code',  
            )->orderBy('date')
            ->get();
            if ($loans->isEmpty()) {
                return $this->sendError('Error!', ['error' => 'Data tidak ditemukan!']);
            }
            $count = $loans->count();
            $success['count'] = $count;
            $success['month'] = $nameOfMonth[$month];
            $success['year'] = $year;
            $success['loans'] = $loans;
            return $this->sendResponse($success, 'Displaying all Loans data');
        } catch (\Throwable $th) {
            return $this->sendError('Error!', ['error' => "Permintaan tidak dapat dilakukan."]);
        }
    }


    /** 
     * Get report per semester loans
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
    */

    public function reportSemester(Request $request){
        try {
            $semester = $request->semester;
            $year1 = $request->year;
            $year2 = $year1+1;
            $academicYear = $year1."/".$year2;
            $range = '';
            $dateOne = '';
            $dateTwo = '';
            if ($semester == 2) {
                $range = 'Genap';
                $dateOne = $year2.'-01-01 00:00:00';
                $dateTwo = $year2.'-06-30 23:59:59';
            } else if($semester == 1) {
                $range = 'Ganjil';
                $dateOne = $year1.'-07-01 00:00:00';
                $dateTwo = $year2.'-12-31 23:59:59';
            }

            $from = date($dateOne);
            $to = date($dateTwo);

            $loans = Loans::
            join('users as loaners', 'loans.loaner_id', '=', 'loaners.id')
            ->leftJoin('users as lenders', 'loans.lender_id', '=', 'lenders.id')
            ->leftJoin('returns as returns', 'loans.return_id', '=', 'returns.id')
            ->leftJoin('users as recipients', 'returns.recipient_id', '=', 'recipients.id')
            ->whereBetween('loans.date', [$from, $to])
            ->whereIn('loans.status', ['1', '3'])
            ->select(
                'loans.id as id',
                'loans.code as code',
                'loans.status as status',
                'loans.date as date',
                'loans.due_date as due_date',
                'loans.return_id as return_id',
                'loans.loaner_id as loaner_id', 
                'loaners.name as loaner_name', 
                'loaners.code_type as loaner_code_type', 
                'loaners.code as loaner_code', 
                'loaners.phone as loaner_phone', 
                'loans.lender_id as lender_id', 
                'lenders.name as lender_name',
                'lenders.code_type as lender_code_type', 
                'lenders.code as lender_code',  
                'recipients.id as recipient_id',
                'recipients.name as recipient_name',
                'recipients.code_type as recipient_code_type', 
                'recipients.code as recipient_code',  
            )->orderBy('date')  
            ->get();
            if ($loans->isEmpty()) {
                return $this->sendError('Error!', ['error' => 'Data tidak ditemukan!']);
            }
            $count = $loans->count();
            $success['academicYear'] = $academicYear;
            $success['count'] = $count;
            $success['range'] = $range;
            $success['loans'] = $loans;
            return $this->sendResponse($success, 'Displaying all Loans data');
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
        
            $loaner_id = Auth::user()->id;

            // \DB::enableQueryLog();
            $checkStatusLoans = Loans::
            where('loaner_id', $loaner_id)
            ->where(function ($query){
                $query->where('status', "0")->orWhere('status', "1");
            })
            ->first();
            // dd(\DB::getQueryLog());

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
            // \DB::enableQueryLog();
            $checkAssets = Assets::whereIn("id", $asset_ids)
            ->where("status", "0")
            ->where("condition", "0")
            ->get();
            // dd(\DB::getQueryLog());
            if($checkAssets->isEmpty())
            {
                return $this->sendError('Error!', [
                    'error' =>
                    'Aset dalam keadaan tidak tersedia'
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
            $inv = rand(100000, 999999);
            $strInv = "$inv";
            $code = "INV-".$date."-ERK-PINJAM"."/".$strInv;
            
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
            $encodeId = base64_encode($loan_id);
            $link = getenv("APP_URL_FE").'/loans/confirmation?data='.$encodeId;
            $loaner_name = Auth::user()->name;
            $loaner_code = Auth::user()->code;
            $loaner_code_type = Auth::user()->code_type;
            $sortNumber = 0;
            for ($i=0; $i < count($asset_ids) ; $i++) {
                $asset_id = $asset_ids[$i];
                $checkAssets = Assets::find($asset_id);
                // dd($checkAssets);
                if($checkAssets->status == "0" && $checkAssets->condition == "0"){
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
                } else {
                    return $this->sendError('Error!', [
                        'error' =>
                        'Seluruh aset yang dipilih dalam keadaan tidak tersedia!'
                    ]);
                }
            }

            $checkSuperAdmin = User::role('Super-Admin')->get();
            $superAdminPhone = $checkSuperAdmin->pluck('phone');
            if(!($superAdminPhone->isEmpty())) {
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
                        $message = "Anda mendapatkan *Permintaan Peminjaman Baru*!\n\nRincian Permintaan\nNama peminjam: *$loaner_name*\n$strUserCode: *$loaner_code*\nKode: *$code*\nLama Peminjaman: *$range*\n\nLihat detailnya melalui tautan berikut: \n$link";
                        try {
                            $this->loansRequestService->sendWhatsappNotification($message, $strPhone);
                            $success['superAdminWhatsApp'] = "WhatsApp berhasil dikirim!";
                        } catch (\Throwable $th) {
                            $success['superAdminWhatsApp'] = "WhatsApp gagal dikirim. Error: ".$th;
                        }
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
            // sleep(5);
            // dd(Auth::user());
            $loan_id = $request->id;
            $loaner_id = Auth::user()->id;
            // dd($loaner_id);
            if(!$loan_id) {
                return $this->sendError('Error!', ['error' => 'Tidak ada transaksi peminjaman yang dipilih!']);
            }
            
            $checkLoaner = Loans::
            where('id', $loan_id)
            ->where('loaner_id', $loaner_id)
            ->first();
            // dd("test");

            // dd($checkLoaner);
            
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
                    'Peminjaman ini sedang aktif atau sudah selesai, data peminjaman tidak dapat diperbarui.'
                ]);
            }

            $code = $checkStatus->code;
            $asset_ids = $request->asset_ids;
            $checkAssets = Assets::whereIn("id", $asset_ids)
            ->where("condition", "0")
            ->get();
            // dd(\DB::getQueryLog());
            if($checkAssets->isEmpty())
            {
                return $this->sendError('Error!', [
                    'error' =>
                    'Aset dalam keadaan tidak tersedia'
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
            $encodeId = base64_encode($loan_id);
            $link = getenv("APP_URL_FE").'/loans/confirmation?data='.$encodeId;
            // dd(\DB::getQueryLog());
            // dd($getAssetFromLoanDetails[0]['asset_id']);
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            $sortNumber = 0;
            // if($asset_ids)
            $loaner_name = Auth::user()->name;
            $loaner_code = Auth::user()->code;
            $loaner_code_type = Auth::user()->code_type;
            if($getAssetFromLoanDetails) {
                for ($i=0; $i < count($getAssetFromLoanDetails) ; $i++) { 
                    $unSetStatusAssets = Assets::find($getAssetFromLoanDetails[$i]['asset_id']);
                    // if()
                    $unSetStatusAssets->status = "0";
                    $unSetStatusAssets->save();
                    $getAssetFromLoanDetails[$i]->forceDelete();
                    // dd("test");
                    // dd($updateLoanDetails['asset_id']);
                    // dd($getAssetFromLoanDetails[$i]['asset_id']);
                }
                for ($i=0; $i < count($asset_ids); $i++) { 
                    $asset_id = $asset_ids[$i];
                    // dd($asset_id);
                    $checkAssets = Assets::find($asset_id);
                    if($checkAssets->status == "0" && $checkAssets->condition == "0"){
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
                    } else {
                        return $this->sendError('Error!', [
                            'error' =>
                            'Seluruh aset yang dipilih dalam keadaan tidak tersedia!'
                        ]);
                    }
                }
                $checkSuperAdmin = User::role('Super-Admin')->get();
                $superAdminPhone = $checkSuperAdmin->pluck('phone');
                if(!($superAdminPhone->isEmpty())) {
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
                            $message = "Anda mendapatkan *Perubahan Permintaan Peminjaman*!\n\nRincian Permintaan\nNama peminjam: *$loaner_name*\n$strUserCode: *$loaner_code*\nKode: *$code*\nLama Peminjaman: *$range*\n\nLihat detailnya melalui tautan berikut: \n$link";
                            try {
                                $this->loansRequestService->sendWhatsappNotification($message, $strPhone);
                                $success['superAdminWhatsApp'] = '';
                            } catch (\Throwable $th) {
                                $success['superAdminWhatsApp'] = 'Gagal mengirim pesan whatsApp kepada Super-Admin. Error: '.$th;
                            }
                        }
                    }
                }
                $success['message'] = "Permintaan peminjaman berhasil diupdate!";
                return $this->sendResponse($success, 'Update data');
            } else {
                return $this->sendError('Error!', [
                    'error' =>
                    'Seluruh aset yang dipilih dalam keadaan tidak tersedia!'
                ]);
            }
            // $success['data'] = $updateDataAsset;
        } catch (\Throwable $th) {
            return $this->sendError('Error!', ['error' => 'Permintaan tidak dapat dilakukan']);
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
        try {
            sleep(5);
            $id = $request->id;
            if(!$id) {
                return $this->sendError('Error!', ['error' => 'Tidak ada transaksi peminjaman yang dipilih!']);
            }
            $lender_id = Auth::user()->id;
            $status = $request->status;
            if(!$status) {
                return $this->sendError('Error!', ['error' => 'Status konfirmasi tidak diatur!']);
            }
            // \DB::enableQueryLog();
            $checkLoans = Loans::find($id);
            // dd(\DB::getQueryLog());
            
            if(!$checkLoans){
                return $this->sendError('Error!', ['error'=> 'Tidak ada data transaksi peminjaman']);
            }

            // dd(!$checkLoans->status != "0");

            if($checkLoans->status != "0") {
                return $this->sendError('Error!', ['error'=> 'Status peminjaman bukan permintaan!']);
            }

            if($status == "1" || $status == "2") {
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

                $encodeId = base64_encode($id);
                $link = getenv("APP_URL_FE")."/loans/myDetails?data=".$encodeId;
                
                // dd($getLoanerPhone->phone);
                
                if($checkLoans->status == "1") {
                    $confirmation = "Selamat, Permintaan Anda DISETUJUI!";
                    $instruction = "\nSilakan temui Admin dari Sarana dan Prasarana! Terima kasih.\n";
                } elseif($checkLoans->status == "2") {
                    $getAssetFromLoanDetails = LoanDetails::
                    where('loan_id', $id)->get();
                    // dd(\DB::getQueryLog());
                    // dd($getAssetFromLoanDetails[0]['asset_id']);
                    DB::statement('SET FOREIGN_KEY_CHECKS=0;');
                    $sortNumber = 0;
                    // if($asset_ids)
                    if($getAssetFromLoanDetails) {
                        for ($i=0; $i < count($getAssetFromLoanDetails) ; $i++) { 
                            $unSetStatusAssets = Assets::find($getAssetFromLoanDetails[$i]['asset_id']);
                            // if()
                            $unSetStatusAssets->status = "0";
                            $unSetStatusAssets->save();
                        }
                    }
                    $confirmation = "Mohon maaf, Permintaan Anda DITOLAK.";
                    $instruction = "";
                }

                if($loaner_phone) {
                    $message = "Anda mendapatkan *Konfirmasi Permintaan Peminjaman*!\n\nRincian Permintaan\nNama peminjam: *$loaner_name*\nKode Peminjaman: *$code*\nPesan Konfirmasi: \n*$confirmation*$instruction\n\nLihat detailnya melalui tautan berikut: \n$link";
                    try {
                        $this->loansRequestService->sendWhatsappNotification($message, $loaner_phone);
                        $success['whatsapp'] = "Pesan Konfirmasi Berhasil dikirim via WhatsApp";
                    } catch (\Throwable $th) {
                        $success['whatsapp'] = "Pesan Konfirmasi Gagal dikirim via WhatsApp. Error: ".$th;
                    }
                }

                $success['message'] = "Transaksi berhasil dikonfirmasi!";
                return $this->sendResponse($success, 'Konfirmasi Peminjaman Berhasil!');
            } else {
                return $this->sendError('Error!', ['error'=> 'Set Status Konfirmasi salah!']);
            }
        } catch (\Throwable $th) {
            return $this->sendError('Error!', ['error' => 'Permintaan tidak dapat dilakukan']);
        }
    }

    /**
     * Demand for Assets Return
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */

    public function demand(Request $request)
    {
        try {
            // dd(Auth::user());
            sleep(5);
            $id = $request->id;
            if(!$id) {
                return $this->sendError('Error!', ['error' => 'Tidak ada transaksi peminjaman yang dipilih!']);
            }
            $checkLoans = Loans::find($id);
            $status = $checkLoans->status;
            if(!$checkLoans){
                return $this->sendError('Error!', ['error'=> 'Tidak ada data transaksi peminjaman']);
            }
            if($status != "1") {
                return $this->sendError('Error!', ['error' => 'Status peminjaman bukan peminjaman aktif!']);
            }

            if($status == "1") {
                $dateNow = Carbon::now();
                $due_date = Carbon::parse($checkLoans->due_date);
                $diff = $dateNow->diff($due_date);

                // dd($dateNow > $due_date);
                if($dateNow <= $due_date){
                    return $this->sendError('Error!', ['error'=> 'Peminjaman belum melewati tenggat waktu!']);
                }

                $encodeId = base64_encode($id);
                $link = getenv("APP_URL_FE")."/loans/myDetails?data=".$encodeId;
                $code = $checkLoans->code;
                $loaner = User::find($checkLoans->loaner_id);
                $loaner_name = $loaner->name;
                $loaner_code_type = $loaner->code_type;
                // dd($loaner0>);
                $loaner_code = $loaner->code;
                $strUserCode = '';
                if($loaner_code_type == '0') {
                    $strUserCode = 'NISN';
                } elseif($loaner_code_type == '1') {
                    $strUserCode = 'NUPTK';
                } elseif($loaner_code_type == '2') {
                    $strUserCode = 'NIP';
                }
                $loaner_phone = $loaner->phone;
                
                // dd($getLoanerPhone->phone);
                $demand = "*Hai $loaner_name!*\nPeminjaman anda telah *MELAMPAUI BATAS WAKTU*.";
                $instruction = "\nSegera kembalikan setiap aset kepada Admin dari Sarana dan Prasarana! Terima kasih.\n";

                if($loaner_phone) {
                    $message = "$demand $instruction\nRincian Peminjaman\nNama peminjam: *$loaner_name*\n$strUserCode: *$loaner_code*\nKode: *$code*\n\nLihat detailnya melalui tautan berikut: \n$link";
                    try {
                        $this->loansRequestService->sendWhatsappNotification($message, $loaner_phone);
                        $success['whatsapp'] = "Pesan WhatsApp berhasil dikirim. ";
                    } catch (\Throwable $th) {
                        $success['whatsapp'] = "Pesan WhatsApp gagal dikirim. Error: ".$th;
                    }
                }

                $success['message'] = $success['whatsapp'];
                return $this->sendResponse($success, 'Pesan Pengembalian Berhasil dikirim!');
            } else {
                return $this->sendError('Error!', ['error'=> 'Status peminjaman bukan peminjaman aktif!']);
            }
        } catch (\Throwable $th) {
            return $this->sendError('Error!', ['error' => 'Permintaan tidak dapat dilakukan']);
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
            if(!$id) {
                return $this->sendError('Error!', ['error' => 'Tidak ada riwayat peminjaman yang dipilih!']);
            }
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
                DB::statement('SET FOREIGN_KEY_CHECKS=0;');
                $getAssetFromLoanDetails = LoanDetails::
                where('loan_id', $id)->get();
                for($i = 0; $i < count($getAssetFromLoanDetails); $i++) {
                    $unSetStatusAssets = Assets::find($getAssetFromLoanDetails[$i]['asset_id']);
                    if($unSetStatusAssets->status == "1"){
                        $unSetStatusAssets->status = "0";
                        $unSetStatusAssets->save();
                    }
                    // dd($getAssetFromLoanDetails[$i]);
                    // $getAssetFromLoanDetails[$i]['deleted_at'] = Carbon::now();
                    $getAssetFromLoanDetails[$i]->forceDelete();
                }     
                $deleteLoans->forceDelete();
                // $totalDelete++;
    
                $tokenMsg = Str::random(15);
                $success['token'] = $tokenMsg;
                $success['message'] = "Delete selected data";
                // $success['total_deleted'] = $totalDelete;
                return $this->sendResponse($success, 'Data terpilih berhasil dihapus');
            } else {
                return $this->sendError('Error!', ['error' => 'Data tidak ditemukan']);
            }
        } catch (\Throwable $th) {
            return $this->sendError('Error!', ['error' => 'Permintaan tidak dapat dilakukan']);
        }
    }

}
