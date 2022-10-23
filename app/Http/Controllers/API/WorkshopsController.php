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
     * @return \Illuminate\Http\Response
    */

    public function index(){
        try {
            // dd(Auth::user());
            if (Auth::user()) {
                // dd(Auth::user()->name);
                // \DB::enableQueryLog();
                $workshops = Workshops::all();
                // dd(\DB::getQueryLog());
                // dd($workshops);
                if ($workshops->isEmpty()) {
                    return $this->sendError('Error!', ['error' => 'Data tidak ditemukan!']);
                }
                return $this->sendResponse($workshops, 'Displaying all workshops data');
            } else {
                return $this->sendError('Account is not login.', ['error' => 'Silakan login terlebih dulu!']);
            }
        } catch (\Throwable $th) {
            return $this->sendError('Error!', ['error' => $th]);
        }
    }

    /** 
     * Get All Workshops in Trash
     * 
     * @return \Illuminate\Http\Response
    */

    public function trash(){
        try {
            // dd(Auth::user());
            if (Auth::user()) {
                // dd(Auth::user());
                // \DB::enableQueryLog();
                $workshops = Workshops::onlyTrashed()->get();
                // dd(\DB::getQueryLog());
                if ($workshops->isEmpty()) {
                    return $this->sendError('Error!', ['error' => 'Data tidak ditemukan!']);
                }
                return $this->sendResponse($workshops, 'Displaying all trash data');

            } else {
                return $this->sendError('Account is not login.', ['error' => 'Silakan login terlebih dulu!']);
            }
        } catch (\Throwable $th) {
            return $this->sendError('Error!', ['error' => $th]);
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
            if (Auth::user()) {
                // \DB::enableQueryLog();
                $workshop = Workshops::where('id', $id)->first();
                // dd(\DB::getQueryLog());
                if (!$workshop) {
                    return $this->sendError('Error!', ['error' => 'Data tidak ditemukan!']);
                }
                return $this->sendResponse($workshop, 'Workshop detail by Id');
            } else {
                return $this->sendError('Account is not login.', ['error' => 'Silakan login terlebih dulu!']);
            }
        } catch (\Throwable $th) {
            return $this->sendError('Error!', ['error' => $th]);
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
            if (Auth::user()) {
                $validator = Validator::make($request->all(),[
                    'name' => 'required|unique:workshops,name|min:3',
                    'phone' => 'required|numeric|unique:workshops,phone'
                ]);
        
                if ($validator->fails()){
                    return $this->sendError('Validator Error.', $validator->errors());
                }
        
                $input = $request->all();
                $createWorkshop = Workshops::create($input);
                $success['token'] = Str::random(15);
        
                return $this->sendResponse($success, 'Workshop ditambahkan!');
            } else {
                return $this->sendError('Account is not login.', ['error' => 'Anda harus masuk terlebih dulu!']);
            }    
        } catch (\Throwable $th) {
            return $this->sendError('Error!'.$th, ['error'=>$th]);
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
            if (Auth::user()) {
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
                    return $this->sendError('Error!', $validator->errors());
                }

                // dd($data);exit();

                $updateDataWorkshops = Workshops::where('id', $id)->update($data);
                $tokenMsg = Str::random(15);
                $success['token'] = $tokenMsg;
                $success['message'] = "Workshop berhasil diupdate!";
                $success['data'] = $updateDataWorkshops;
                return $this->sendResponse($success, 'Update data');
            } else {
                return $this->sendError('Account is not login.', ['error' => 'Silakan login terlebih dulu!']);
            }
        } catch (\Throwable $th) {
            return $this->sendError('Error!', $th);
        }
    }

    /**
     * Put Workshops into trash
     * 
     * @param int $id
     * @return \Illuminate\Http\Response
     */

    public function delete(Int $id)
    {
        try {
            if (Auth::user()) {
                // \DB::enableQueryLog();
                $checkWorkshops = Workshops::where('id', $id)->first();
                // dd(\DB::getQueryLog());
                if(!$checkWorkshops){
                    return $this->sendError('Error!', ['error'=> 'Tidak ada data yang dihapus!']);
                }
                $deleteWorkshops = Workshops::where('id', $id)->update(['deleted_at' => Carbon::now()]);
                $tokenMsg = Str::random(15);
                $success['token'] = $tokenMsg;
                $success['message'] = "Delete data";
                $success['data'] = $deleteWorkshops;
                return $this->sendResponse($success, 'Data berhasil dihapus');
            } else {
                return $this->sendError('Account is not login.', ['error' => 'Silakan login terlebih dulu!']);
            }
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

    public function deleteMultiple(Request $request)
    {
        try {
            if (Auth::user()) {
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
            } else {
                return $this->sendError('Account is not login.', ['error' => 'Silakan login terlebih dulu!']);
            }
        } catch (\Throwable $th) {
            return $this->sendError('Error!', $th);
        }
    }

    /**
     * Restore Workshops from trash
     * 
     * @param int $id
     * @return \Illuminate\Http\Response
     */

    public function restore(Int $id)
    {
        // return "Cek";exit();
        try {
            if (Auth::user()) {
                // \DB::enableQueryLog();
                $checkWorkshops = Workshops::onlyTrashed()->where('id', $id)->get();
                // dd(\DB::getQueryLog());
                
                if($checkWorkshops->isEmpty()){
                    return $this->sendError('Error!', ['error'=> 'Tidak ada data yang dipulihkan']);
                }
                $restoreWorkshops = Workshops::onlyTrashed()->where('id', $id)->update(['deleted_at' => null]);
                $tokenMsg = Str::random(15);
                $success['token'] = $tokenMsg;
                $success['message'] = "Restore Workshops data";
                $success['data'] = $restoreWorkshops;
                return $this->sendResponse($success, 'Data dipulihkan');
            } else {
                return $this->sendError('Account is not login.', ['error' => 'Silakan login terlebih dulu!']);
            }
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

    public function restoreMultiple(Request $request)
    {
        // return "Cek";exit();
        try {
            if (Auth::user()) {
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
                $success['message'] = "Restore category asset data";
                $success['data'] = $restoreWorkshops;
                return $this->sendResponse($success, 'Data dipulihkan');
            } else {
                return $this->sendError('Account is not login.', ['error' => 'Silakan login terlebih dulu!']);
            }
        } catch (\Throwable $th) {
            return $this->sendError('Error!', $th);
        }
    }
}
