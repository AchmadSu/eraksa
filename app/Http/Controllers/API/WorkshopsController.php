<?php

namespace App\Http\Controllers\API;

use Carbon\Carbon;
use App\Models\Workshops;
// use App\Models\Workshops;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Auth;
use function PHPUnit\Framework\isEmpty;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\API\BaseController;

class WorkshopsController extends BaseController
{
    /** ATTRIVE WORKSHOPS DATA */

    /** 
     * Get All Workshops
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
            // dd(Auth::user());
            // dd(Auth::user()->name);
            // \DB::enableQueryLog();
            $name = $request->name;
            $phone = $request->phone;
            $order = $request->order;
            $skip = $request->skip;
            $take = $request->take;
            $trash = $request->trash;
            
            if(isset($phone)) {
                $spiltPhone = str_split($phone);
                // dd($spiltPhone);
                if($spiltPhone[0] === '8'){
                    $phone = '+62'.$phone;
                }
                // dd($spiltPhone[0].$spiltPhone[1]);
                if($spiltPhone[0].$spiltPhone[1] === '62'){
                    $phone = '+'.$phone;
                }
            }

            $workshops = Workshops::
            when($trash == 1)
            ->onlyTrashed()
            ->when(isset($name))
            ->where('name', 'like', '%'.$name.'%')
            ->when(isset($phone))
            ->where('phone', 'like', '%'.$phone.'%')
            ->when($order)
            ->orderBy($order, 'ASC')
            ->get();
            // dd(\DB::getQueryLog());
            // dd($workshops);
            if ($workshops->isEmpty()) {
                return $this->sendError('Error!', ['error' => 'Data tidak ditemukan!']);
            }
            $count = $workshops->count();
            $countDelete = Workshops::onlyTrashed()->count();
            // dd(\DB::getQueryLog());
            $success['count'] = $count;
            $success['countDelete'] = $countDelete;
            $success['workshops']= $workshops
                ->when(isset($skip))
                ->skip($skip)
                ->when(isset($take))
                ->take($take)
            ;
            return $this->sendResponse($success, 'Displaying all workshops data');
        } catch (\Throwable $th) {
            return $this->sendError('Error!', ['error' => "Permintaan tidak dapat dilakukan"]);
        }
    }

    /** 
     * Get Workshop By Id
     * 
     * @param Int $id
     * @return \Illuminate\Http\Response
    */

    public function show(Int $id)
    {
        try {
            sleep(5);
            // \DB::enableQueryLog();
            $workshop = Workshops::find($id);
            // dd(\DB::getQueryLog());
            if (!$workshop) {
                return $this->sendError('Error!', ['error' => 'Data tidak ditemukan!']);
            }
            return $this->sendResponse($workshop, 'Workshop detail');
        } catch (\Throwable $th) {
            return $this->sendError('Error!', ['error' => "Permintaan tidak dapat dilakukan"]);
        }
        
    }

    /** CRUD WORKSHOPS */
    
    /**
     * Create Workshops
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */

    public function create(Request $request){
        try {
            sleep(5);
            $validator = Validator::make($request->all(),[
                'name' => 'required|unique:workshops,name|min:3',
                'phone' => 'required|numeric|unique:workshops,phone'
            ]);
    
            if ($validator->fails()){
                return $this->sendError('Error!', ['error'=>'Data tidak valid. Nama atau nomor ponsel sudah tersedia!']);
            }
    
            $input = $request->all();
            $createWorkshop = Workshops::create($input);
            $success['token'] = Str::random(15);
    
            return $this->sendResponse($success, 'Workshop ditambahkan!');    
        } catch (\Throwable $th) {
            return $this->sendError('Error!', ['error' => "Permintaan tidak dapat dilakukan"]);
        } 
    }

    /**
     * Update Category Asset
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    
    public function update(Request $request)
    {
        try {
            sleep(5);
            $id = $request->id;
            $new_name = $request->new_name;
            $new_phone = $request->new_phone;
            
            if ($new_name == NULL) {
                $validator = Validator::make($request->all(), [
                    'new_phone' => 'required|numeric|unique:workshops,phone',
                ]);
                            
                $data = array(
                    'phone' => $new_phone
                );
                
            } elseif ($new_name != NULL) {
                $validator = Validator::make($request->all(),[
                    'new_name' => 'required|unique:workshops,name|min:3'
                ]);
                            
                $data = array(
                    'name' => $new_name
                );
            } elseif ($new_name AND $new_phone != NULL) {
                $validator = Validator::make($request->all(),[
                    'new_name' => 'required|unique:workshops,name|min:3',
                    'new_phone' => 'required|numeric|unique:workshops,phone'
                ]);
                            
                $data = array(
                    'name' => $new_name,
                    'phone' => $new_phone
                );
            }

            if ($validator->fails()) {
                return $this->sendError('Error!', ['error'=>'Data tidak valid. Nama atau nomor ponsel sudah tersedia!']);
            }

            // dd($data);exit();

            $updateDataWorkshops = Workshops::where('id', $id)->update($data);
            $tokenMsg = Str::random(15);
            $success['token'] = $tokenMsg;
            $success['message'] = "Workshop berhasil diupdate!";
            $success['data'] = $updateDataWorkshops;
            return $this->sendResponse($success, 'Update data');
        } catch (\Throwable $th) {
            return $this->sendError('Error!', $th);
        }
    }

    /**
     * Put Multiple Workshops into trash
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
            $checkWorkshops = Workshops::whereIn('id', $ids)->get();
            // dd(\DB::getQueryLog());
            // dd($checkWorkshops);
            if($checkWorkshops->isEmpty()){
                return $this->sendError('Error!', ['error'=> 'Tidak ada data yang dihapus!']);
            }
            $deleteWorkshops = Workshops::whereIn('id', $ids)->update(['deleted_at' => Carbon::now()]);
            $tokenMsg = Str::random(15);
            $success['token'] = $tokenMsg;
            $success['message'] = "Delete selected data";
            $success['data'] = $deleteWorkshops;
            return $this->sendResponse($success, 'Data terpilih berhasil dihapus');
        } catch (\Throwable $th) {
            return $this->sendError('Error!', $th);
        }
    }

    /**
     * Restore Multiple Workshops from trash
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
            $checkWorkshops = Workshops::onlyTrashed()->whereIn('id', $ids)->get();
            // dd(\DB::getQueryLog());
            
            if($checkWorkshops->isEmpty()){
                return $this->sendError('Error!', ['error'=> 'Tidak ada data yang dipulihkan']);
            }
            $restoreWorkshops = Workshops::onlyTrashed()->whereIn('id', $ids)->update(['deleted_at' => null]);
            $tokenMsg = Str::random(15);
            $success['token'] = $tokenMsg;
            $success['message'] = "Restore selected data";
            $success['data'] = $restoreWorkshops;
            return $this->sendResponse($success, 'Data terpilih berhasil dipulihkan');
        } catch (\Throwable $th) {
            return $this->sendError('Error!', $th);
        }
    }
}
