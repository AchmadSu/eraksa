<?php

namespace App\Http\Controllers\API;

use Carbon\Carbon;
use App\Models\Assets;
use App\Models\CategoryAssets;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Auth;
use function PHPUnit\Framework\isEmpty;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\API\BaseController;

class CategoryAssetsController extends BaseController
{
    /** ATTRIVE CATEGORY ASSETS DATA */

    /** 
     * Get All Category Assets
     * 
     * @return \Illuminate\Http\Response
    */

    public function index(){
        try {
            sleep(5);
            // dd(Auth::user());
            // dd(Auth::user()->name);
            // \DB::enableQueryLog();
            $categoryAssets = CategoryAssets::all();
            // dd(\DB::getQueryLog());
            // dd($categoryAssets);
            if ($categoryAssets->isEmpty()) {
                return $this->sendError('Error!', ['error' => 'Data tidak ditemukan!']);
            }
            return $this->sendResponse($categoryAssets, 'Displaying all category assets data');
        } catch (\Throwable $th) {
            return $this->sendError('Error!', ['error' => $th]);
        }
    }

    /** 
     * Get All Category Assets in Trash
     * 
     * @return \Illuminate\Http\Response
    */

    public function trash(){
        try {
            sleep(5);
            // dd(Auth::user());
            // dd(Auth::user());
            // \DB::enableQueryLog();
            $categoryAssets = CategoryAssets::onlyTrashed()->get();
            // dd(\DB::getQueryLog());
            if ($categoryAssets->isEmpty()) {
                return $this->sendError('Error!', ['error' => 'Data tidak ditemukan!']);
            }
            return $this->sendResponse($categoryAssets, 'Displaying all trash data');

        } catch (\Throwable $th) {
            return $this->sendError('Error!', ['error' => $th]);
        }
    }

    /** 
     * Get Category Asset By Id
     * 
     * @param Int $id
     * @return \Illuminate\Http\Response
    */

    public function show(Int $id)
    {
        try {
            sleep(5);
            // \DB::enableQueryLog();
            $categoryAsset = CategoryAssets::where('id', $id)->first();
            // dd(\DB::getQueryLog());
            if (!$categoryAsset) {
                return $this->sendError('Error!', ['error' => 'Data tidak ditemukan!']);
            }
            return $this->sendResponse($categoryAsset, 'Category Asset detail');
        } catch (\Throwable $th) {
            return $this->sendError('Error!', ['error' => $th]);
        }
        
    }

    /** CRUD CATEGORY ASSETS */
    
    /**
     * Create Category Asset
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */

    public function create(Request $request){
        try {
            sleep(5);
            $validator = Validator::make($request->all(),[
                'name' => 'required|unique:category_assets,name',
                'description' => 'required|min:5'
            ]);
    
            if ($validator->fails()){
                return $this->sendError('Validator Error.', $validator->errors());
            }
    
            $input = $request->all();
            $createCategory = CategoryAssets::create($input);
            $success['token'] = Str::random(15);
    
            return $this->sendResponse($success, 'Category Asset ditambahkan!');    
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
            sleep(5);
            $id = $request->id;
            $new_name = $request->new_name;
            $description = $request->description;
            
            if ($new_name == NULL) {
                $validator = Validator::make($request->all(), [
                    'description' => 'required|min:5',
                ]);
                            
                $data = array(
                    'description' => $description
                );
                
            } elseif ($new_name != NULL) {
                $validator = Validator::make($request->all(),[
                    'new_name' => 'required|unique:category_assets,name',
                    'description' => 'required|min:5'
                ]);
                            
                $data = array(
                    'name' => $new_name,
                    'description' => $description
                );
            }
            if ($validator->fails()) {
                return $this->sendError('Error!', $validator->errors());
            }

            // dd($data);exit();

            $updateDataCategoryAssets = CategoryAssets::where('id', $id)->update($data);
            $tokenMsg = Str::random(15);
            $success['token'] = $tokenMsg;
            $success['message'] = "Category Asset berhasil diupdate!";
            $success['data'] = $updateDataCategoryAssets;
            return $this->sendResponse($success, 'Update data');
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
            sleep(5);
            // \DB::enableQueryLog();
            $checkCategoryAsset = CategoryAssets::where('id', $id)->first();
            // dd(\DB::getQueryLog());
            if(!$checkCategoryAsset){
                return $this->sendError('Error!', ['error'=> 'Tidak ada data yang dihapus!']);
            }
            $deleteCategoryAsset = CategoryAssets::where('id', $id)->update(['deleted_at' => Carbon::now()]);
            $tokenMsg = Str::random(15);
            $success['token'] = $tokenMsg;
            $success['message'] = "Delete data";
            $success['data'] = $deleteCategoryAsset;
            return $this->sendResponse($success, 'Data berhasil dihapus');
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
            sleep(5);
            $ids = $request->ids;
            // dd($ids);
            // \DB::enableQueryLog();
            $checkCategoryAssets = CategoryAssets::whereIn('id', $ids)->get();
            // dd(\DB::getQueryLog());
            // dd($checkCategoryAssets);
            if($checkCategoryAssets->isEmpty()){
                return $this->sendError('Error!', ['error'=> 'Tidak ada data yang dihapus!']);
            }
            $deleteCategoryAssets = CategoryAssets::whereIn('id', $ids)->update(['deleted_at' => Carbon::now()]);
            $tokenMsg = Str::random(15);
            $success['token'] = $tokenMsg;
            $success['message'] = "Delete selected data";
            $success['data'] = $deleteCategoryAssets;
            return $this->sendResponse($success, 'Data terpilih berhasil dihapus');
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
            sleep(5);
            // \DB::enableQueryLog();
            $checkCatgeoryAsset = CategoryAssets::onlyTrashed()->where('id', $id)->get();
            // dd(\DB::getQueryLog());
            
            if($checkCatgeoryAsset->isEmpty()){
                return $this->sendError('Error!', ['error'=> 'Tidak ada data yang dipulihkan']);
            }
            $restoreCategoryAsset = CategoryAssets::onlyTrashed()->where('id', $id)->update(['deleted_at' => null]);
            $tokenMsg = Str::random(15);
            $success['token'] = $tokenMsg;
            $success['message'] = "Restore category asset data";
            $success['data'] = $restoreCategoryAsset;
            return $this->sendResponse($success, 'Data dipulihkan');
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
            sleep(5);
            $ids = $request->ids;
            // \DB::enableQueryLog();
            $checkCategoryAssets = CategoryAssets::onlyTrashed()->whereIn('id', $ids)->get();
            // dd(\DB::getQueryLog());
            
            if($checkCategoryAssets->isEmpty()){
                return $this->sendError('Error!', ['error'=> 'Tidak ada data yang dipulihkan']);
            }
            $restoreCategoryAsset = CategoryAssets::onlyTrashed()->whereIn('id', $ids)->update(['deleted_at' => null]);
            $tokenMsg = Str::random(15);
            $success['token'] = $tokenMsg;
            $success['message'] = "Restore category asset data";
            $success['data'] = $restoreCategoryAsset;
            return $this->sendResponse($success, 'Data dipulihkan');
        } catch (\Throwable $th) {
            return $this->sendError('Error!', $th);
        }
    }
}
