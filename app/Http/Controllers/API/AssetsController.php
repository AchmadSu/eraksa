<?php

namespace App\Http\Controllers\API;

use Carbon\Carbon;
use App\Models\Assets;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Auth;
use function PHPUnit\Framework\isEmpty;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\API\BaseController;

class AssetsController extends BaseController
{
    /** ATTRIVE ASSETS DATA */

    /** 
     * Get All Assets
     * 
     * @return \Illuminate\Http\Response
    */

    public function index(){
        try {
            // dd(Auth::user());
            if (Auth::user()) {
                // dd(Auth::user()->name);
                // \DB::enableQueryLog();
                $assets = Assets::all();
                // dd(\DB::getQueryLog());
                // dd($assets);
                if ($assets->isEmpty()) {
                    return $this->sendError('Error!', ['error' => 'Data tidak ditemukan!']);
                }
                return $this->sendResponse($assets, 'Displaying all assets data');
            } else {
                return $this->sendError('Account is not login.', ['error' => 'Silakan login terlebih dulu!']);
            }
        } catch (\Throwable $th) {
            return $this->sendError('Error!', ['error' => $th]);
        }
    }

    /** 
     * Get All Assets in Trash
     * 
     * @return \Illuminate\Http\Response
    */

    public function trash(){
        try {
            // dd(Auth::user());
            if (Auth::user()) {
                // dd(Auth::user());
                // \DB::enableQueryLog();
                $assets = Assets::onlyTrashed()->get();
                // dd(\DB::getQueryLog());
                if ($assets->isEmpty()) {
                    return $this->sendError('Error!', ['error' => 'Data tidak ditemukan!']);
                }
                return $this->sendResponse($assets, 'Displaying all trash data');

            } else {
                return $this->sendError('Account is not login.', ['error' => 'Silakan login terlebih dulu!']);
            }
        } catch (\Throwable $th) {
            return $this->sendError('Error!', ['error' => $th]);
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
            if (Auth::user()) {
                // \DB::enableQueryLog();
                $asset = Assets::where('id', $id)->first();
                // dd(\DB::getQueryLog());
                if (!$asset) {
                    return $this->sendError('Error!', ['error' => 'Data tidak ditemukan!']);
                }
                return $this->sendResponse($asset, 'Asset detail by Id');
            } else {
                return $this->sendError('Account is not login.', ['error' => 'Silakan login terlebih dulu!']);
            }
        } catch (\Throwable $th) {
            return $this->sendError('Error!', ['error' => $th]);
        }
        
    }

    /** CRUD ASSETS */
    
    /**
     * Register API
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */

    public function create(Request $request){
        try {
            if (Auth::user()) {
                $validator = Validator::make($request->all(),[
                    'name' => 'required',
                    'code' => 'required|unique:assets,code',
                    'user_id' => 'required',
                    'date' => 'required',
                    'condition' => 'required',
                    'status' => 'required',
                ]);
        
                if ($validator->fails()){
                    return $this->sendError('Validator Error.', $validator->errors());
                }
        
                $input = $request->all();
                $createAsset = Assets::create($input);
                $success['token'] = Str::random(15);
        
                return $this->sendResponse($success, 'Asset ditambahkan!');
            } else {
                return $this->sendError('Account is not login.', ['error' => 'Anda harus masuk terlebih dulu!']);
            }    
        } catch (\Throwable $th) {
            return $this->sendError('Error!'.$th, ['error'=>$th]);
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
            if (Auth::user()) {
                $id = $request->id;
                $name = $request->name;
                $new_code = $request->new_code;
                $category_id = $request->category_id;
                $user_id = $request->user_id;
                $placement_id = $request->placement_id;
                $date = $request->date;
                $condition = $request->condition;
                $status = $request->status;
                
                if ($new_code == NULL) {
                    $validator = Validator::make($request->all(), [
                        'name' => 'required',
                        'user_id' => 'required',
                        'date' => 'required',
                        'condition' => 'required',
                        'status' => 'required',
                    ]);
                                
                    $data = array(
                        'name' => $name,
                        'category_id' => $category_id,
                        'user_id' => $user_id,
                        'placement_id' => $placement_id,
                        'date' => $date,
                        'condition' => $condition,
                        'status' => $status,
                    );
                    
                } elseif ($new_code != NULL) {
                    $validator = Validator::make($request->all(),[
                        'name' => 'required',
                        'user_id' => 'required',
                        'new_code' => 'required|unique:assets,code',
                        'date' => 'required',
                        'condition' => 'required',
                        'status' => 'required',
                    ]);
                                
                    $data = array(
                        'name' => $name,
                        'code' => $new_code,
                        'category_id' => $category_id,
                        'user_id' => $user_id,
                        'placement_id' => $placement_id,
                        'date' => $date,
                        'condition' => $condition,
                        'status' => $status,
                    );
                }
                if ($validator->fails()) {
                    return $this->sendError('Error!', $validator->errors());
                }

                // dd($data);exit();

                $updateDataUser = Assets::where('id', $id)->update($data);
                $tokenMsg = Str::random(15);
                $success['token'] = $tokenMsg;
                $success['message'] = "Asset berhasil diupdate!";
                $success['data'] = $updateDataUser;
                return $this->sendResponse($success, 'Update data');
            } else {
                return $this->sendError('Account is not login.', ['error' => 'Silakan login terlebih dulu!']);
            }
        } catch (\Throwable $th) {
            return $this->sendError('Error!', $th);
        }
    }

    /**
     * Put Asset into trash
     * 
     * @param int $id
     * @return \Illuminate\Http\Response
     */

    public function delete(Int $id)
    {
        try {
            if (Auth::user()) {
                // \DB::enableQueryLog();
                $checkAsset = Assets::where('id', $id)->first();
                // dd(\DB::getQueryLog());
                if(!$checkAsset){
                    return $this->sendError('Error!', ['error'=> 'Tidak ada data yang dihapus!']);
                }
                $deleteAsset = Assets::where('id', $id)->update(['deleted_at' => Carbon::now()]);
                $tokenMsg = Str::random(15);
                $success['token'] = $tokenMsg;
                $success['message'] = "Delete data";
                $success['data'] = $deleteAsset;
                return $this->sendResponse($success, 'Data berhasil dihapus');
            } else {
                return $this->sendError('Account is not login.', ['error' => 'Silakan login terlebih dulu!']);
            }
        } catch (\Throwable $th) {
            return $this->sendError('Error!', $th);
        }
    }

    /**
     * Put Multiple Asset into trash
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
                $checkAssets = Assets::whereIn('id', $ids)->get();
                // dd(\DB::getQueryLog());
                // dd($checkAssets);
                if($checkAssets->isEmpty()){
                    return $this->sendError('Error!', ['error'=> 'Tidak ada data yang dihapus!']);
                }
                $deleteAssets = Assets::whereIn('id', $ids)->update(['deleted_at' => Carbon::now()]);
                $tokenMsg = Str::random(15);
                $success['token'] = $tokenMsg;
                $success['message'] = "Delete selected data";
                $success['data'] = $deleteAssets;
                return $this->sendResponse($success, 'Data terpilih berhasil dihapus');
            } else {
                return $this->sendError('Account is not login.', ['error' => 'Silakan login terlebih dulu!']);
            }
        } catch (\Throwable $th) {
            return $this->sendError('Error!', $th);
        }
    }

    /**
     * Restore Asset from trash
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
                $checkAsset = Assets::onlyTrashed()->where('id', $id)->get();
                // dd(\DB::getQueryLog());
                
                if($checkAsset->isEmpty()){
                    return $this->sendError('Error!', ['error'=> 'Tidak ada data yang dipulihkan']);
                }
                $restoreAsset = Assets::onlyTrashed()->where('id', $id)->update(['deleted_at' => null]);
                $tokenMsg = Str::random(15);
                $success['token'] = $tokenMsg;
                $success['message'] = "Restore asset data";
                $success['data'] = $restoreAsset;
                return $this->sendResponse($success, 'Data dipulihkan');
            } else {
                return $this->sendError('Account is not login.', ['error' => 'Silakan login terlebih dulu!']);
            }
        } catch (\Throwable $th) {
            return $this->sendError('Error!', $th);
        }
    }

    /**
     * Restore Multiple Asset from trash
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
                $checkAssets = Assets::onlyTrashed()->whereIn('id', $ids)->get();
                // dd(\DB::getQueryLog());
                
                if($checkAssets->isEmpty()){
                    return $this->sendError('Error!', ['error'=> 'Tidak ada data yang dipulihkan']);
                }
                $restoreAsset = Assets::onlyTrashed()->whereIn('id', $ids)->update(['deleted_at' => null]);
                $tokenMsg = Str::random(15);
                $success['token'] = $tokenMsg;
                $success['message'] = "Restore asset data";
                $success['data'] = $restoreAsset;
                return $this->sendResponse($success, 'Data dipulihkan');
            } else {
                return $this->sendError('Account is not login.', ['error' => 'Silakan login terlebih dulu!']);
            }
        } catch (\Throwable $th) {
            return $this->sendError('Error!', $th);
        }
    }
}
