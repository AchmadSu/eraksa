<?php

namespace App\Http\Controllers\API;

use Carbon\Carbon;
use App\Models\Assets;
use App\Models\CategoryAssets;
use App\Models\Placements;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Auth;
use function PHPUnit\Framework\isEmpty;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\API\BaseController;

class PlacementsController extends BaseController
{
    /** ATTRIVE PLACEMENT ASSETS DATA */

    /** 
     * Get All Placement Assets
     * 
     * @return \Illuminate\Http\Response
    */

    public function index(){
        try {
            // dd(Auth::user());
            if (Auth::user()) {
                // dd(Auth::user()->name);
                // \DB::enableQueryLog();
                $placements = Placements::all();
                // dd(\DB::getQueryLog());
                // dd($placements);
                if ($placements->isEmpty()) {
                    return $this->sendError('Error!', ['error' => 'Data tidak ditemukan!']);
                }
                return $this->sendResponse($placements, 'Displaying all assets data');
            } else {
                return $this->sendError('Account is not login.', ['error' => 'Silakan login terlebih dulu!']);
            }
        } catch (\Throwable $th) {
            return $this->sendError('Error!', ['error' => $th]);
        }
    }

    /** 
     * Get All Placement Assets in Trash
     * 
     * @return \Illuminate\Http\Response
    */

    public function trash(){
        try {
            // dd(Auth::user());
            if (Auth::user()) {
                // dd(Auth::user());
                // \DB::enableQueryLog();
                $placements = Placements::onlyTrashed()->get();
                // dd(\DB::getQueryLog());
                if ($placements->isEmpty()) {
                    return $this->sendError('Error!', ['error' => 'Data tidak ditemukan!']);
                }
                return $this->sendResponse($placements, 'Displaying all trash data');

            } else {
                return $this->sendError('Account is not login.', ['error' => 'Silakan login terlebih dulu!']);
            }
        } catch (\Throwable $th) {
            return $this->sendError('Error!', ['error' => $th]);
        }
    }

    /** 
     * Get Placement Asset By Id
     * 
     * @param Int $id
     * @return \Illuminate\Http\Response
    */

    public function show(Int $id)
    {
        try {
            if (Auth::user()) {
                // \DB::enableQueryLog();
                $placements = Placements::where('id', $id)->first();
                // dd(\DB::getQueryLog());
                if (!$placements) {
                    return $this->sendError('Error!', ['error' => 'Data tidak ditemukan!']);
                }
                return $this->sendResponse($placements, 'Asset detail by Id');
            } else {
                return $this->sendError('Account is not login.', ['error' => 'Silakan login terlebih dulu!']);
            }
        } catch (\Throwable $th) {
            return $this->sendError('Error!', ['error' => $th]);
        }
        
    }

    /** CRUD PLACEMENT ASSETS */
    
    /**
     * Create Placement Asset
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */

    public function create(Request $request){
        try {
            if (Auth::user()) {
                $validator = Validator::make($request->all(),[
                    'name' => 'required|unique:placements,name|min:3',
                ]);
        
                if ($validator->fails()){
                    return $this->sendError('Validator Error.', $validator->errors());
                }
        
                $input = $request->all();
                $createCategory = Placements::create($input);
                $success['token'] = Str::random(15);
        
                return $this->sendResponse($success, 'Placement ditambahkan!');
            } else {
                return $this->sendError('Account is not login.', ['error' => 'Anda harus masuk terlebih dulu!']);
            }    
        } catch (\Throwable $th) {
            return $this->sendError('Error!'.$th, ['error'=>$th]);
        } 
    }

    /**
     * Update Placement Asset
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    
    public function update(Request $request)
    {
        try {
            if (Auth::user()) {
                $id = $request->id;
                $name = $request->name;
                $validator = Validator::make($request->all(),[
                    'name' => 'required|unique:placements,name|min:3'
                ]);
                    
                if ($validator->fails()) {
                    return $this->sendError('Error!', $validator->errors());
                }

                // dd($data);exit();

                $updateDataPlacements = Placements::where('id', $id)->update(['name' => $name]);
                $tokenMsg = Str::random(15);
                $success['token'] = $tokenMsg;
                $success['message'] = "Placement berhasil diupdate!";
                $success['data'] = $updateDataPlacements;
                return $this->sendResponse($success, 'Update data');
            } else {
                return $this->sendError('Account is not login.', ['error' => 'Silakan login terlebih dulu!']);
            }
        } catch (\Throwable $th) {
            return $this->sendError('Error!', $th);
        }
    }

    /**
     * Put Category Asset into trash
     * 
     * @param int $id
     * @return \Illuminate\Http\Response
     */

    public function delete(Int $id)
    {
        try {
            if (Auth::user()) {
                // \DB::enableQueryLog();
                $checkCategoryAsset = Placements::where('id', $id)->first();
                // dd(\DB::getQueryLog());
                if(!$checkCategoryAsset){
                    return $this->sendError('Error!', ['error'=> 'Tidak ada data yang dihapus!']);
                }
                $deleteCategoryAsset = Placements::where('id', $id)->update(['deleted_at' => Carbon::now()]);
                $tokenMsg = Str::random(15);
                $success['token'] = $tokenMsg;
                $success['message'] = "Delete data";
                $success['data'] = $deleteCategoryAsset;
                return $this->sendResponse($success, 'Data berhasil dihapus');
            } else {
                return $this->sendError('Account is not login.', ['error' => 'Silakan login terlebih dulu!']);
            }
        } catch (\Throwable $th) {
            return $this->sendError('Error!', $th);
        }
    }

    /**
     * Put Multiple Category Asset into trash
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
                $checkPlacements = Placements::whereIn('id', $ids)->get();
                // dd(\DB::getQueryLog());
                // dd($checkPlacements);
                if($checkPlacements->isEmpty()){
                    return $this->sendError('Error!', ['error'=> 'Tidak ada data yang dihapus!']);
                }
                $deletePlacements = Placements::whereIn('id', $ids)->update(['deleted_at' => Carbon::now()]);
                $tokenMsg = Str::random(15);
                $success['token'] = $tokenMsg;
                $success['message'] = "Delete selected data";
                $success['data'] = $deletePlacements;
                return $this->sendResponse($success, 'Data terpilih berhasil dihapus');
            } else {
                return $this->sendError('Account is not login.', ['error' => 'Silakan login terlebih dulu!']);
            }
        } catch (\Throwable $th) {
            return $this->sendError('Error!', $th);
        }
    }

    /**
     * Restore Category Asset from trash
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
                $checkCatgeoryAsset = Placements::onlyTrashed()->where('id', $id)->get();
                // dd(\DB::getQueryLog());
                
                if($checkCatgeoryAsset->isEmpty()){
                    return $this->sendError('Error!', ['error'=> 'Tidak ada data yang dipulihkan']);
                }
                $restoreCategoryAsset = Placements::onlyTrashed()->where('id', $id)->update(['deleted_at' => null]);
                $tokenMsg = Str::random(15);
                $success['token'] = $tokenMsg;
                $success['message'] = "Restore placement data";
                $success['data'] = $restoreCategoryAsset;
                return $this->sendResponse($success, 'Data dipulihkan');
            } else {
                return $this->sendError('Account is not login.', ['error' => 'Silakan login terlebih dulu!']);
            }
        } catch (\Throwable $th) {
            return $this->sendError('Error!', $th);
        }
    }

    /**
     * Restore Multiple Category Asset from trash
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
                $checkPlacements = Placements::onlyTrashed()->whereIn('id', $ids)->get();
                // dd(\DB::getQueryLog());
                
                if($checkPlacements->isEmpty()){
                    return $this->sendError('Error!', ['error'=> 'Tidak ada data yang dipulihkan']);
                }
                $restoreCategoryAsset = Placements::onlyTrashed()->whereIn('id', $ids)->update(['deleted_at' => null]);
                $tokenMsg = Str::random(15);
                $success['token'] = $tokenMsg;
                $success['message'] = "Restore placement data";
                $success['data'] = $restoreCategoryAsset;
                return $this->sendResponse($success, 'Data dipulihkan');
            } else {
                return $this->sendError('Account is not login.', ['error' => 'Silakan login terlebih dulu!']);
            }
        } catch (\Throwable $th) {
            return $this->sendError('Error!', $th);
        }
    }
}
