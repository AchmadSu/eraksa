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
            return $this->sendError('Error!', ['error' => $th]);
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
            return $this->sendError('Error!', ['error' => $th]);
        }
        
    }
    
}
