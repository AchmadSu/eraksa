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
     * Get All loans by loaner
     * 
     * @return \Illuminate\Http\Response
    */

    public function getLoansByLoanerId(Int $loaner_id){
        try {
            // dd(Loans::all());
            sleep(5);
            // dd(Auth::user());
            // var_dump(Loans::all());exit();
            // \DB::enableQueryLog();
            $loans = Loans::where('loaner_id', $loaner_id)->get();
            // dd(\DB::getQueryLog());
            if ($loans->isEmpty()) {
                return $this->sendError('Error!', ['error' => 'Data tidak ditemukan!']);
            }
            return $this->sendResponse($loans, 'Displaying all Loans data');
        } catch (\Throwable $th) {
            return $this->sendError('Error!', ['error' => $th]);
        }
    }

    /** 
     * Get All loans by lender
     * 
     * @return \Illuminate\Http\Response
    */

    public function getLoansByLenderId(Int $lender_id){
        try {
            // dd(Loans::all());
            sleep(5);
            // dd(Auth::user());
            // var_dump(Loans::all());exit();
            // \DB::enableQueryLog();
            $loans = Loans::where('lender_id', $lender_id)->get();
            // dd(\DB::getQueryLog());
            if ($loans->isEmpty()) {
                return $this->sendError('Error!', ['error' => 'Data tidak ditemukan!']);
            }
            return $this->sendResponse($loans, 'Displaying all Loans data');
        } catch (\Throwable $th) {
            return $this->sendError('Error!', ['error' => $th]);
        }
    }

    /** 
     * Get All loans Code
     * 
     * @return \Illuminate\Http\Response
    */

    public function getLoansByCode(String $code){
        try {
            // dd(Loans::all());
            sleep(5);
            // dd(Auth::user());
            // var_dump(Loans::all());exit();
            // \DB::enableQueryLog();
            $loans = Loans::where('code', $code)->first();
            // dd(\DB::getQueryLog());
            if (!$loans) {
                return $this->sendError('Error!', ['error' => 'Data tidak ditemukan!']);
            }
            return $this->sendResponse($loans, 'Displaying all Loans data');
        } catch (\Throwable $th) {
            return $this->sendError('Error!', ['error' => $th]);
        }
    }

    /** 
     * Get All loans date
     * 
     * @return \Illuminate\Http\Response
    */

    public function getLoansByDate(Request $request){
        try {
            // dd(Loans::all());
            sleep(5);
            $dateOne = $request->dateOne;
            $dateTwo = $request->dateTwo;
            $from = date($dateOne);
            $to = date($dateTwo);
            if($to != NULL){
                // dd("test");
                $loans = Loans::whereBetween('date', [$from, $to])->get();
            } else {
                // dd("test2");
                $loans = Loans::where('date', $from)->get();
            }
            // dd(\DB::getQueryLog());
            if (!$loans) {
                return $this->sendError('Error!', ['error' => 'Data tidak ditemukan!']);
            }
            return $this->sendResponse($loans, 'Displaying all Loans data');
        } catch (\Throwable $th) {
            return $this->sendError('Error!', ['error' => $th]);
        }
    }

    /** 
     * Get All loans due date
     * 
     * @return \Illuminate\Http\Response
    */

    public function getLoansByDueDate(Request $request){
        try {
            // dd(Loans::all());
            sleep(5);
            $dateOne = $request->dateOne;
            $dateTwo = $request->dateTwo;
            $from = date($dateOne);
            $to = date($dateTwo);
            if($to != NULL){
                $loans = Loans::whereBetween('due_date', [$from, $to])->get();
            } else {
                $loans = Loans::where('due_date', $from)->get();
            }
            // dd(\DB::getQueryLog());
            if (!$loans) {
                return $this->sendError('Error!', ['error' => 'Data tidak ditemukan!']);
            }
            return $this->sendResponse($loans, 'Displaying all Loans data');
        } catch (\Throwable $th) {
            return $this->sendError('Error!', ['error' => $th]);
        }
    }
}
