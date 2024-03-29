<?php

namespace App\Http\Controllers\API;

use DB;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Assets;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use function PHPUnit\Framework\isEmpty;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\API\BaseController;
use App\Models\CategoryAssets;

class AssetsController extends BaseController
{
    /** ATTRIVE ASSETS DATA */

    /** 
     * Get All Assets
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
    */

    public function index(Request $request){
        try {
            $negation_ids = $request->negation_ids;
            $order = $request->order;
            $keyWords = $request->keyWords;
            $category_id = $request->category_id;
            $user_keyWords = $request->user_keyWords;
            $dateOne = $request->dateOne;
            $dateTwo = $request->dateTwo;
            $condition = $request->condition;
            $status = $request->status;
            $placement_id = $request->placement_id;
            $study_program_id = $request->study_program_id;
            $skip = $request->skip;
            $take = $request->take;
            
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
            
            $user = User::where('name', 'like', '%'.$user_keyWords.'%')
            ->orWhere('email', 'like', '%'.$user_keyWords.'%')
            ->get();
            $user_ids = array();
            foreach ($user as $rowUser) {
                $user_ids[] = $rowUser->id;
            }
            // dd("test");
            // \DB::enableQueryLog();
            // dd($negation_ids);
            $assets = Assets::join('users as users', 'assets.user_id', '=', 'users.id')
            ->join('category_assets as category_assets', 'assets.category_id', '=', 'category_assets.id')
            ->join('placements as placements', 'assets.placement_id', '=', 'placements.id')
            ->join('study_programs as study_programs', 'assets.study_program_id', '=', 'study_programs.id')
            ->when(isset($keyWords))
            ->where(function ($query) use ($keyWords){
                $query->where('assets.code', 'like', '%'.$keyWords.'%')->orWhere('assets.name', 'like', '%'.$keyWords.'%');
            })
            ->when(isset($user_ids))
            ->whereIn('assets.user_id', $user_ids)
            ->when(isset($category_id))
            ->where('assets.category_id', $category_id)
            ->when(isset($study_program_id))
            ->where('assets.study_program_id', $study_program_id)
            ->when(isset($placement_id))
            ->where('assets.placement_id', $placement_id)
            ->when(isset($dateOne) && !isset($dateTwo))
            ->where('assets.date', $from)
            ->when(isset($dateOne) && isset($dateTwo))
            ->whereBetween('date', [$from, $to])
            ->when(isset($status))
            ->where('assets.status', $status)
            ->when(isset($condition))
            ->where('assets.condition', $condition)
            ->select(
                'assets.id as id',
                'assets.name as name',
                'assets.code as code',
                'assets.condition as condition',
                'assets.status as status',
                'assets.deleted_at as deleted_at',
                'assets.date as date',
                'assets.placement_id as placement_id', 
                'placements.name as placement_name', 
                'assets.category_id as category_id', 
                'category_assets.name as category_name', 
                'assets.user_id as user_id',
                'users.name as user_name',
                'assets.study_program_id as study_program_id',
                'study_programs.name as study_program_name',
            )
            ->when($order)
            ->orderBy($order, 'ASC')
            ->get();
            // dd(\DB::getQueryLog());
            // dd($assets);
            if ($assets->isEmpty()) {
                return $this->sendError('Error!', ['error' => 'Data tidak ditemukan!']);
            }
            
            if(isset($negation_ids)){
                $assets = $assets->whereNotIn('id', $negation_ids);
            }

            $countDelete = Assets::onlyTrashed()->count();
            // dd(\DB::getQueryLog());
            $success['countDelete'] = $countDelete;
            $success['count'] = $assets->count();
            // dd($success['count']);
            $success['assets']= $assets
                ->when(isset($skip))
                ->skip($skip)
                ->when(isset($take))
                ->take($take)
                ->values()
            ;
            return $this->sendResponse($success, 'Displaying all assets data');
        } catch (\Throwable $th) {
            return $this->sendError('Error!', ['error' => 'Permintaan tidak dapat dilakukan']);
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
            $negation_ids = $request->negation_ids;
            $order = $request->order;
            $keyWords = $request->keyWords;
            $category_id = $request->category_id;
            $user_keyWords = $request->user_keyWords;
            $dateOne = $request->dateOne;
            $dateTwo = $request->dateTwo;
            $condition = $request->condition;
            $status = $request->status;
            $placement_id = $request->placement_id;
            $study_program_id = $request->study_program_id;
            $skip = $request->skip;
            $take = $request->take;
            

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

            $user = User::where('name', 'like', '%'.$user_keyWords.'%')
            ->orWhere('email', 'like', '%'.$user_keyWords.'%')
            ->get();
            $user_ids = array();
            foreach ($user as $rowUser) {
                $user_ids[] = $rowUser->id;
            }

            $auth = Auth::user();
            \DB::enableQueryLog();
            $assets = Assets::
            join('users as users', 'assets.user_id', '=', 'users.id')
            ->join('category_assets as category_assets', 'assets.category_id', '=', 'category_assets.id')
            ->join('placements as placements', 'assets.placement_id', '=', 'placements.id')
            ->join('study_programs as study_programs', 'assets.study_program_id', '=', 'study_programs.id')
            ->when(isset($keyWords))
            ->where(function ($query) use ($keyWords){
                $query->where('assets.code', 'like', '%'.$keyWords.'%')->orWhere('assets.name', 'like', '%'.$keyWords.'%');
            })
            ->when(isset($user_ids))
            ->whereIn('assets.user_id', $user_ids)
            ->when(isset($category_id))
            ->where('assets.category_id', $category_id)
            ->when($auth->hasRole('Admin'))
            ->where('assets.study_program_id', $auth->study_program_id)
            ->when(isset($study_program_id))
            ->where('assets.study_program_id', $study_program_id)
            ->when(isset($placement_id))
            ->where('assets.placement_id', $placement_id)
            ->when(isset($dateOne) && !isset($dateTwo))
            ->where('assets.date', $from)
            ->when(isset($dateOne) && isset($dateTwo))
            ->whereBetween('date', [$from, $to])
            ->when(isset($status))
            ->where('assets.status', $status)
            ->when(isset($condition))
            ->where('assets.condition', $condition)
            ->select(
                'assets.id as id',
                'assets.name as name',
                'assets.code as code',
                'assets.condition as condition',
                'assets.status as status',
                'assets.date as date',
                'assets.placement_id as placement_id', 
                'placements.name as placement_name', 
                'assets.category_id as category_id', 
                'category_assets.name as category_name', 
                'assets.user_id as user_id',
                'users.name as user_name',
                'assets.study_program_id as study_program_id',
                'study_programs.name as study_program_name',
            )
            ->when($order)
            ->orderBy($order, 'ASC')
            // ->when($order)
            ->onlyTrashed()
            ->get();
            // dd(\DB::getQueryLog());
            if ($assets->isEmpty()) {
                return $this->sendError('Error!', ['error' => 'Data tidak ditemukan!']);
            }

            if(isset($negation_ids)){
                $assets = $assets->whereNotIn('id', $negation_ids);
            }
            // dd($assets);
            $success['count'] = $assets->count();
            // dd($success['count']);
            $success['assets']= $assets
                ->when(isset($skip))
                ->skip($skip)
                ->when(isset($take))
                ->take($take)
                ->values()
            ;
            return $this->sendResponse($success, 'Displaying all trash data');

        } catch (\Throwable $th) {
            return $this->sendError('Error!', ['error' => 'Permintaan tidak dapat dilakukan']);
        }
    }

    /** 
     * Get Asset By Id
     * 
     * @param Int $id
     * @return \Illuminate\Http\Response
    */

    public function show(Int $id)
    {
        try {
            sleep(5);
            // \DB::enableQueryLog();
            $asset = Assets::join('users as users', 'assets.user_id', '=', 'users.id')
            ->join('category_assets as category_assets', 'assets.category_id', '=', 'category_assets.id')
            ->join('placements as placements', 'assets.placement_id', '=', 'placements.id')
            ->join('study_programs as study_programs', 'assets.study_program_id', '=', 'study_programs.id')
            ->select(
                'assets.id as id',
                'assets.name as name',
                'assets.code as code',
                'assets.condition as condition',
                'assets.status as status',
                'assets.deleted_at as deleted_at',
                'assets.date as date',
                'assets.placement_id as placement_id', 
                'placements.name as placement_name', 
                'assets.category_id as category_id', 
                'category_assets.name as category_name', 
                'assets.user_id as user_id',
                'users.name as user_name',
                'assets.study_program_id as study_program_id',
                'study_programs.name as study_program_name',
            )
            ->find($id);
            // dd(\DB::getQueryLog());
            if (!$asset) {
                return $this->sendError('Error!', ['error' => 'Data tidak ditemukan!']);
            }
            return $this->sendResponse($asset, 'Asset detail');
        } catch (\Throwable $th) {
            return $this->sendError('Error!', ['error' => 'Permintaan tidak dapat dilakukan']);
        }
        
    }

    /** 
     * Get Assets Percentage
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
    */

    public function percentage(Request $request)
    {
        try {
            // \DB::enableQueryLog();
            $auth = Auth::user();
            $category_id = $request->category_id;
            $dateOne = $request->dateOne;
            $dateTwo = $request->dateTwo;
            $updated1 = $request->updated1;
            $updated2 = $request->updated2;
            $condition = $request->condition;
            $status = $request->status;
            $study_program_id = $request->study_program_id;
            
            $from = date($dateOne);
            $to = date($dateTwo);

            $fromUpdated = date($updated1." 00:00:00");
            // dd($fromUpdated);
            $toUpdated = date($updated2." 23:59:59");

            if(isset($dateTwo)){
                if($from > $to){
                    return $this->sendError('Error!', [
                        'error' => 
                        'Parameter tanggal salah. Tanggal pertama harus lebih kecil atau sama dengan tanggal kedua!'
                    ]);
                }
            }

            if(isset($updated2)){
                if($fromUpdated > $toUpdated){
                    return $this->sendError('Error!', [
                        'error' => 
                        'Parameter tanggal salah. Tanggal pertama harus lebih kecil atau sama dengan tanggal kedua!'
                    ]);
                }
            }
            
            $countAll = Assets::
            when($auth->hasRole('Admin'))
            ->where('assets.study_program_id', $auth->study_program_id)
            ->count();
            $countRequest = Assets::
            when(isset($category_id))
            ->where('assets.category_id', $category_id)
            ->when(isset($study_program_id))
            ->where('assets.study_program_id', $study_program_id)
            ->when(isset($dateOne) && !isset($dateTwo))
            ->where('assets.date', $from)
            ->when(isset($dateOne) && isset($dateTwo))
            ->whereBetween('date', [$from, $to])
            ->when(isset($updated1) && !isset($updated2))
            ->where('assets.updated_at', $fromUpdated)
            ->when(isset($updated1) && isset($updated2))
            ->whereBetween('updated_at', [$fromUpdated, $toUpdated])
            ->when(isset($status))
            ->where('assets.status', $status)
            ->when(isset($condition))
            ->where('assets.condition', $condition)
            ->when($auth->hasRole('Admin'))
            ->where('assets.study_program_id', $auth->study_program_id)
            ->count();
            // dd(\DB::getQueryLog());
            if (!$countAll && !$countRequest) {
                return $this->sendError('Error!', ['error' => 'Data tidak ditemukan!']);
            }
            $success['countAll'] = $countAll;
            $success['countRequest'] = $countRequest;
            $success['fraction'] = number_format($countRequest)."/".number_format($countAll);
            $success['percentage'] = number_format((float)$countRequest/$countAll * 100, 0, '.', '');
            return $this->sendResponse($success, 'Count Asset by Status and Condition');
        } catch (\Throwable $th) {
            return $this->sendError('Error!', ['error' => 'Permintaan tidak dapat dilakukan']);
        }
        
    }

    /** 
     * Get report per week Assets
     * 
     *
     * @return \Illuminate\Http\Response
    */

    public function reportWeekly()
    {
        try {
            $auth = Auth::user();
            $from = Carbon::now()->subWeek()->startOfWeek();
            $to = Carbon::now()->subWeek()->endOfWeek();
            $assets = Assets::
            join('users as users', 'assets.user_id', '=', 'users.id')
            ->join('category_assets as category_assets', 'assets.category_id', '=', 'category_assets.id')
            ->join('placements as placements', 'assets.placement_id', '=', 'placements.id')
            ->join('study_programs as study_programs', 'assets.study_program_id', '=', 'study_programs.id')
            ->where('assets.condition', '1')
            ->whereBetween('assets.updated_at', [$from, $to])
            ->when($auth->hasRole('Admin'))
            ->where('assets.study_program_id', $auth->study_program_id)
            ->select(
                'assets.id as id',
                'assets.name as name',
                'assets.code as code',
                'assets.condition as condition',
                'assets.status as status',
                'assets.date as date',
                'assets.placement_id as placement_id', 
                'placements.name as placement_name', 
                'assets.category_id as category_id', 
                'category_assets.name as category_name', 
                'assets.user_id as user_id',
                'users.name as user_name',
                'assets.study_program_id as study_program_id',
                'study_programs.name as study_program_name',
            )
            ->orderBy('study_program_name', 'ASC')
            ->get();

            if ($assets->isEmpty()) {
                return $this->sendError('Error!', ['error' => 'Data tidak ditemukan!']);
            }
            
            $success['range1'] = $from->format('d/m/Y');
            $success['range2'] = $to->format('d/m/Y');
            $success['count'] = $assets->count();
            $success['assets']= $assets->values();

            return $this->sendResponse($success, 'Displaying all Loans Data');
        } catch (\Throwable $th) {
            return $this->sendError('Error!', ['error' => 'Permintaan tidak dapat dilakukan']);
        }
        
    }

    /** 
     * Get report per month Assets
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
    */

    public function reportMonthly(Request $request)
    {
        try {
            $auth = Auth::user();

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

            $assets = Assets::
            join('users as users', 'assets.user_id', '=', 'users.id')
            ->join('category_assets as category_assets', 'assets.category_id', '=', 'category_assets.id')
            ->join('placements as placements', 'assets.placement_id', '=', 'placements.id')
            ->join('study_programs as study_programs', 'assets.study_program_id', '=', 'study_programs.id')
            ->where('assets.condition', '1')
            ->whereYear('assets.updated_at', '=', $year)
            ->whereMonth('assets.updated_at', '=', $month)
            ->when($auth->hasRole('Admin'))
            ->where('assets.study_program_id', $auth->study_program_id)
            ->select(
                'assets.id as id',
                'assets.name as name',
                'assets.code as code',
                'assets.condition as condition',
                'assets.status as status',
                'assets.date as date',
                'assets.placement_id as placement_id', 
                'placements.name as placement_name', 
                'assets.category_id as category_id', 
                'category_assets.name as category_name', 
                'assets.user_id as user_id',
                'users.name as user_name',
                'assets.study_program_id as study_program_id',
                'study_programs.name as study_program_name',
            )
            ->orderBy('study_program_name', 'ASC')
            ->get();

            if ($assets->isEmpty()) {
                return $this->sendError('Error!', ['error' => 'Data tidak ditemukan!']);
            }

            $success['count'] = $assets->count();
            $success['assets']= $assets->values();
            $success['month'] = $nameOfMonth[$month];
            $success['year'] = $year;
            return $this->sendResponse($success, 'Displaying all Loans Data');
        } catch (\Throwable $th) {
            return $this->sendError('Error!', ['error' => 'Permintaan tidak dapat dilakukan']);
        }
        
    }

    /** 
     * Get report per month Assets
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
    */

    public function reportSemester(Request $request)
    {
        try {
            $auth = Auth::user();

            $semester = $request->semester;
            $year1 = $request->year;
            $year2 = $year1+1;
            $academicYear = $year1."/".$year2;
            $range = '';
            $dateOne = '';
            $dateTwo = '';
            if ($semester == 2) {
                $range = 'Genap';
                $dateOne = $year2.'-02-01 00:00:00';
                $dateTwo = $year2.'-07-31 23:59:59';
            } else if($semester == 1) {
                $range = 'Ganjil';
                $dateOne = $year1.'-08-01 00:00:00';
                $dateTwo = $year2.'-01-31 23:59:59';
            }

            $from = date($dateOne);
            $to = date($dateTwo);

            $assets = Assets::
            join('users as users', 'assets.user_id', '=', 'users.id')
            ->join('category_assets as category_assets', 'assets.category_id', '=', 'category_assets.id')
            ->join('placements as placements', 'assets.placement_id', '=', 'placements.id')
            ->join('study_programs as study_programs', 'assets.study_program_id', '=', 'study_programs.id')
            ->where('assets.condition', '1')
            ->whereBetween('assets.updated_at', [$from, $to])
            ->when($auth->hasRole('Admin'))
            ->where('assets.study_program_id', $auth->study_program_id)
            ->select(
                'assets.id as id',
                'assets.name as name',
                'assets.code as code',
                'assets.condition as condition',
                'assets.status as status',
                'assets.date as date',
                'assets.placement_id as placement_id', 
                'placements.name as placement_name', 
                'assets.category_id as category_id', 
                'category_assets.name as category_name', 
                'assets.user_id as user_id',
                'users.name as user_name',
                'assets.study_program_id as study_program_id',
                'study_programs.name as study_program_name',
            )
            ->orderBy('study_program_name', 'ASC')
            ->get();

            if ($assets->isEmpty()) {
                return $this->sendError('Error!', ['error' => 'Data tidak ditemukan!']);
            }

            $success['count'] = $assets->count();
            $success['assets']= $assets->values();
            $success['academicYear'] = $academicYear;
            $success['range'] = $range;

            return $this->sendResponse($success, 'Displaying all Loans Data');
        } catch (\Throwable $th) {
            return $this->sendError('Error!', ['error' => 'Permintaan tidak dapat dilakukan']);
        }
        
    }

    /** CRUD ASSETS */
    
    /**
     * Create Assets
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */

    public function create(Request $request){
        try {
            sleep(5);
            $validator = Validator::make($request->all(),[
                'name' => 'required',
                'study_program_id' => 'required|numeric',
                'category_id' => 'required|numeric',
                'placement_id' => 'required|numeric',
            ]);
    
            if ($validator->fails()){
                return $this->sendError('Error!', ['error'=>'Data tidak valid!']);
            }
            $auth = Auth::user();
            // dd($auth);
            $user_id =  $auth->id;
            $study_program_id = (int)$request->study_program_id;
            if($auth->hasRole('Admin')){
                $study_program_id = $auth->study_program_id;
            }
            $date = date("d/m/Y");
            $category_name = CategoryAssets::where('id', $request->category_id)->pluck('name');
            $category_name = Str::upper(str_replace(array('["','"]'), '', $category_name));
            // dd($category_name);
            $inv = rand(100000, 999999);
            $strInv = "$inv";
            $code = "ERK-ASSETS-".$category_name."-".$date."-".$strInv;
            
            $input = array(
                "user_id" => $user_id,
                "date" => Carbon::now(),
                "status" => "0",
                "condition" => "0",
                "code" => $code,
                "name" => ucwords($request->name),
                "study_program_id" => $study_program_id,
                "category_id" => (int)$request->category_id,
                "placement_id" => (int)$request->placement_id,
            );

            // dd($input);
            
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            $createAsset = Assets::create($input);
            $success['code'] = "Kode Aset: ".$createAsset->code;
            $success['token'] = Str::random(15);
    
            return $this->sendResponse($success, 'Asset ditambahkan!');    
        } catch (\Throwable $th) {
            return $this->sendError('Error!', ['error' => 'Permintaan tidak dapat dilakukan']);
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
            if(!$id) {
                return $this->sendError('Error!', ['error' => 'Tidak ada aset yang dipilih!']);
            }
            $updateDataAsset = Assets::find($id);
            if(!$updateDataAsset) {
                return $this->sendError('Error!', ['error' => 'Data aset tidak ada!']);
            }
            if($updateDataAsset->status != "0"){
                return $this->sendError('Error!', ['error' => 'Aset sedang dalam transaksi peminjaman atau perbaikan. Data tidak dapat diperbarui!']);
            }
            $name = ucwords($request->name);
            $date = $updateDataAsset->date;
            $category_id = $request->category_id;
            // dd($updateDataAsset->category_id != $category_id);
            if($updateDataAsset->category_id != $category_id) {
                $category_name = CategoryAssets::where('id', $request->category_id)->pluck('name');
                $category_name = Str::upper(str_replace(array('["','"]'), '', $category_name));
                $inv = rand(100000, 999999);
                $strInv = "$inv";
                $code = "ERK-ASSETS-".$category_name."-".date("d/m/Y", strtotime($date))."-".$strInv;
                
                $checkCode = Assets::where('code', $code)->get();
                // dd($checkCode);
                if($checkCode->isEmpty()) {
                    $updateDataAsset->code = $code;
                } else {
                    return $this->sendError('Error!', ['error'=>'Kode sudah tersedia!']);
                }
            }

            $placement_id = $request->placement_id;
            $condition = $request->condition;
            $study_program_id = $request->study_program_id;
            
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'condition' => 'required',
                'category_id' => 'required|numeric',
                'placement_id' => 'required|numeric',
                'study_program_id' => 'required|numeric',
            ]);
            
            if ($validator->fails()){
                return $this->sendError('Error!', ['error'=>'Data tidak valid!']);
            }

            $updateDataAsset->name = $name;
            $updateDataAsset->date = $date;
            $updateDataAsset->placement_id = $placement_id;
            $updateDataAsset->condition = $condition;
            $updateDataAsset->category_id = $category_id;
            $updateDataAsset->study_program_id = $study_program_id;
            // dd($data);exit();
            
            // dd($updateDataAsset);
            // $updateDataAsset->fill($updateDataAsset);
            // $updateDataAsset = new Assets;
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            $updateDataAsset->save();
            $tokenMsg = Str::random(15);
            $success['token'] = $tokenMsg;
            $success['message'] = "Asset berhasil diupdate!";
            $success['data'] = $updateDataAsset;
            // $success['code'] = "Kode Aset: ".$updateDataAsset->code;
            return $this->sendResponse($success, 'Update data');
        } catch (\Throwable $th) {
            return $this->sendError('Error!', ['error' => 'Permintaan tidak dapat dilakukan']);
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
            if($ids == NULL) {
                return $this->sendError('Error!', ['error' => 'Tidak ada aset yang dipilih!']);
            }
            // \DB::enableQueryLog();
            $checkAssets = Assets::whereIn('id', $ids)->where("status", "0")->get();
            // dd(\DB::getQueryLog());
            // dd($checkAssets);
            if($checkAssets->isEmpty()){
                return $this->sendError('Error!', ['error'=> 'Aset sedang dalam transaksi peminjaman atau perbaikan, permintaan tidak dapat dilakukan!']);
            }
            // \DB::enableQueryLog();
            $deleteAssets = Assets::findMany($ids);
            // dd(\DB::getQueryLog());
            $totalDelete = 0;
            foreach($deleteAssets as $rowAssets){
                // dd($rowAssets->id);
                // if($rowAssets > 0){
                // dd($rowAssets->id);
                $deleteAssets = Assets::find($rowAssets->id);
                if($deleteAssets->status == "0"){
                    $deleteAssets->deleted_at = Carbon::now();
                    $deleteAssets->delete();
                    $totalDelete++;
                }
                // }
            }

            $tokenMsg = Str::random(15);
            $success['token'] = $tokenMsg;
            $success['message'] = "Delete selected data";
            $success['total_deleted'] = $totalDelete;
            return $this->sendResponse($success, 'Data terpilih berhasil dihapus');
        } catch (\Throwable $th) {
            return $this->sendError('Error!', ['error' => 'Permintaan tidak dapat dilakukan']);
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
            return $this->sendError('Error!', ['error' => 'Permintaan tidak dapat dilakukan']);
        }
    }

    /**
     * Delete Multiple Assets permanently
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */

    public function deletePermanently(Request $request)
    {
        try {
            sleep(5);
            $ids = $request->ids;
            // dd($ids);
            if($ids == NULL) {
                return $this->sendError('Error!', ['error' => 'Tidak ada aset yang dipilih!']);
            }
            // \DB::enableQueryLog();
            $checkAssets = Assets::whereIn('id', $ids)->onlyTrashed()->get();
            // dd(\DB::getQueryLog());
            if($checkAssets->isEmpty()){
                return $this->sendError('Error!', ['error'=> 'Data tidak ditemukan!']);
            }

            // dd($checkAssets);
            // \DB::enableQueryLog();
        //  $deleteAssets = Assets::findMany($ids);
            // dd(\DB::getQueryLog());
            $totalDelete = 0;
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            foreach($checkAssets as $rowAssets){
                $rowAssets->forceDelete();  
                $totalDelete++;
            }

            $tokenMsg = Str::random(15);
            $success['token'] = $tokenMsg;
            $success['message'] = "Delete selected data";
            $success['total_deleted'] = $totalDelete;
            return $this->sendResponse($success, 'Data terpilih berhasil dihapus');
        } catch (\Throwable $th) {
            return $this->sendError('Error!', ['error' => 'Permintaan tidak dapat dilakukan']);
        }
    }
}
