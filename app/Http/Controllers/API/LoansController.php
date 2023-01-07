<?php

namespace App\Http\Controllers\API;

use DB;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Models\Loans;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\API\BaseController;
use App\Models\Returns;
use App\Models\User;
use Illuminate\Support\Facades\Date;

class LoansController extends BaseController
{
    /** ATTRIVE LOANS DATA */

    /** 
     * Get All loans
     * 
     * @return \Illuminate\Http\Response
    */

    public function index(){
        try {
            sleep(5);
            // dd(Loans::all());
            // dd(Auth::user()->name);
            // \DB::enableQueryLog();
            $loans = Loans::all();
            // dd(\DB::getQueryLog());
            // dd($loans);
            if ($loans->isEmpty()) {
                return $this->sendError('Error!', ['error' => 'Data tidak ditemukan!']);
            }
            return $this->sendResponse($loans, 'Displaying all Loans data');
        } catch (\Throwable $th) {
            return $this->sendError('Error!', ['error' => $th]);
        }
    }

    /** 
     * Get All loans by filter
     * 
     * @return \Illuminate\Http\Response
    */

    public function filterLoans(Request $request){
        try {
            // dd(Loans::all());
            sleep(5);
            $code = $request->code;
            
            $loaner_name = $request->loaner_name;
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
                if($loaner_name != NULL) {
                    $loaner = User::where('name', 'like', '%'.$loaner_name.'%')->get();
                    // dd(\DB::getQueryLog());
                    $loaner_ids = array();
                    foreach ($loaner as $rowLoaner) {
                        $loaner_ids[] = $rowLoaner->id;
                    }
                }
            } else {
                $loaner_ids = $request->loaner_ids;
            }
            // dd($loaner_ids);
            $return = Returns::where('code', 'like', '%'.$return_code.'%')->get();
            // $loans = Loans::whereIn('loaner_id', $loaner_ids)->get();
            $return_ids = array();
            foreach ($return as $rowReturn) {
                $return_ids[] = $rowReturn->id;
            }
            // \DB::enableQueryLog();
            // dd($loaner_ids);
            $loans = Loans::
                    when(isset($code))
                    ->where('code', 'like', '%'.$code.'%')
                    ->when(isset($loaner_ids))
                    ->whereIn('loaner_id', ($loaner_ids))
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
            // dd(Auth::user());
            // dd(\DB::getQueryLog());
            // var_dump(Loans::all());exit();
            if ($loans->isEmpty()) {
                return $this->sendError('Error!', ['error' => 'Data tidak ditemukan!']);
            }
            return $this->sendResponse($loans, 'Displaying all Loans data');
        } catch (\Throwable $th) {
            return $this->sendError('Error!', ['error' => $th]);
        }
    }
}
