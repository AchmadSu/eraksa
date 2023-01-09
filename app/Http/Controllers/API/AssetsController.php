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
            sleep(5);

            $keyWords = $request->keyWords;
            $category_id = $request->category_id;
            $user_keyWords = $request->user_keyWords;
            $dateOne = $request->dateOne;
            $dateTwo = $request->dateTwo;
            $condition = $request->condition;
            $status = $request->status;
            $placement_id = $request->placement_id;
            $study_program_id = $request->study_program_id;

            $from = date($dateOne);
            $to = date($dateTwo);

            $user = User::where('name', 'like', '%'.$user_keyWords.'%')
            ->orWhere('email', 'like', '%'.$user_keyWords.'%')
            ->get();
            $user_ids = array();
            foreach ($user as $rowUser) {
                $user_ids[] = $rowUser->id;
            }

            $assets = Assets::when(isset($keyWords))
            ->where('code', 'like', '%'.$keyWords.'%')
            ->orWhere('name', 'like', '%'.$keyWords.'%')
            ->when(isset($user_ids))
            ->whereIn('user_id', $user_ids)
            ->when(isset($category_id))
            ->where('category_id', $category_id)
            ->when(isset($study_program_id))
            ->where('study_program_id', $study_program_id)
            ->when(isset($placement_id))
            ->where('placement_id', $placement_id)
            ->when(isset($dateOne) && !isset($dateTwo))
            ->where('date', $from)
            ->when(isset($dateOne) && isset($dateTwo))
            ->whereBetween('date', [$from, $to])
            ->when(isset($dueDateOne) && !isset($dueDateTwo))
            ->when(isset($status))
            ->where('status', $status)
            ->when(isset($condition))
            ->where('condition', $condition)
            ->get();
            // dd(\DB::getQueryLog());
            // dd($assets);
            if ($assets->isEmpty()) {
                return $this->sendError('Error!', ['error' => 'Data tidak ditemukan!']);
            }
            return $this->sendResponse($assets, 'Displaying all assets data');
        } catch (\Throwable $th) {
            return $this->sendError('Error!', ['error' => $th]);
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
            sleep(5);
            $keyWords = $request->keyWords;
            $category_id = $request->category_id;
            $user_keyWords = $request->user_keyWords;
            $dateOne = $request->dateOne;
            $dateTwo = $request->dateTwo;
            $condition = $request->condition;
            $status = $request->status;
            $placement_id = $request->placement_id;
            $study_program_id = $request->study_program_id;

            $from = date($dateOne);
            $to = date($dateTwo);

            $user = User::where('name', 'like', '%'.$user_keyWords.'%')
            ->orWhere('email', 'like', '%'.$user_keyWords.'%')
            ->get();
            $user_ids = array();
            foreach ($user as $rowUser) {
                $user_ids[] = $rowUser->id;
            }

            $auth = Auth::user();

            $assets = Assets::onlyTrashed()->when(isset($keyWords))
            ->where('code', 'like', '%'.$keyWords.'%')
            ->orWhere('name', 'like', '%'.$keyWords.'%')
            ->when(isset($user_ids))
            ->whereIn('user_id', $user_ids)
            ->when(isset($category_id))
            ->where('category_id', $category_id)
            ->when($auth->hasRole('Admin'))
            ->where('study_program_id', $auth->study_program_id)
            ->when(isset($study_program_id))
            ->where('study_program_id', $study_program_id)
            ->when(isset($placement_id))
            ->where('placement_id', $placement_id)
            ->when(isset($dateOne) && !isset($dateTwo))
            ->where('date', $from)
            ->when(isset($dateOne) && isset($dateTwo))
            ->whereBetween('date', [$from, $to])
            ->when(isset($dueDateOne) && !isset($dueDateTwo))
            ->when(isset($status))
            ->where('status', $status)
            ->when(isset($condition))
            ->where('condition', $condition)
            ->get();
            // dd(\DB::getQueryLog());
            if ($assets->isEmpty()) {
                return $this->sendError('Error!', ['error' => 'Data tidak ditemukan!']);
            }
            return $this->sendResponse($assets, 'Displaying all trash data');

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
            sleep(5);
            // \DB::enableQueryLog();
            $asset = Assets::where('id', $id)->first();
            // dd(\DB::getQueryLog());
            if (!$asset) {
                return $this->sendError('Error!', ['error' => 'Data tidak ditemukan!']);
            }
            return $this->sendResponse($asset, 'Asset detail');
        } catch (\Throwable $th) {
            return $this->sendError('Error!', ['error' => $th]);
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
                return $this->sendError('Validator Error.', $validator->errors());
            }

            $user_id =  Auth::user()->id;
            $date = date("d/m/Y");
            $category_name = CategoryAssets::find($request->category_id)->pluck('name');
            $category_name = Str::upper(str_replace(array('["','"]'), '', $category_name));
            $inv = rand(100000, 999999);
            $strInv = "$inv";
            $code = "ERK-ASSETS-".$category_name."-".$date."-".$strInv;
            
            $input = array(
                "user_id" => $user_id,
                "date" => Carbon::now(),
                "status" => "0",
                "code" => $code,
                "name" => ucwords(strtolower($request->name)),
                "study_program_id" => (int)$request->study_program_id,
                "category_id" => (int)$request->category_id,
                "placement_id" => (int)$request->placement_id,
            );

            // dd($input);
            
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            $createAsset = Assets::create($input);
            $success['token'] = Str::random(15);
    
            return $this->sendResponse($success, 'Asset ditambahkan!');    
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
            sleep(5);
            $id = $request->id;
            $updateDataAsset = Assets::find($id);
            
            $name = $request->name;
            $new_code = $request->new_code;
            $category_id = $request->category_id;
            $user_id = $request->user_id;
            $placement_id = $request->placement_id;
            $date = $request->date;
            $condition = $request->condition;
            $status = $request->status;
            $study_program_id = $request->study_program_id;
            
            if ($new_code == NULL) {
                $validator = Validator::make($request->all(), [
                    'name' => 'required',
                    'user_id' => 'required|numeric',
                    'date' => 'required',
                    'condition' => 'required',
                    'status' => 'required',
                    'category_id' => 'required|numeric',
                    'placement_id' => 'required|numeric',
                    'study_program_id' => 'required|numeric',
                ]);
                
            } elseif ($new_code != NULL) {
                $validator = Validator::make($request->all(),[
                    'name' => 'required',
                    'user_id' => 'required',
                    'new_code' => 'required|unique:assets,code',
                    'date' => 'required',
                    'condition' => 'required',
                    'status' => 'required',
                    'category_id' => 'required|numeric',
                    'placement_id' => 'required|numeric',
                    'study_program_id' => 'required|numeric',
                ]);
                $updateDataAsset->code = $new_code;
            }

            if ($validator->fails()) {
                return $this->sendError('Error!', $validator->errors());
            }

            $updateDataAsset->name = $name;
            $updateDataAsset->user_id = $user_id;
            $updateDataAsset->date = $date;
            $updateDataAsset->placement_id = $placement_id;
            $updateDataAsset->condition = $condition;
            $updateDataAsset->status = $status;
            $updateDataAsset->category_id = $category_id;
            $updateDataAsset->study_program_id = $study_program_id;
            // dd($data);exit();

            $updateDataAsset->save();
            $tokenMsg = Str::random(15);
            $success['token'] = $tokenMsg;
            $success['message'] = "Asset berhasil diupdate!";
            $success['data'] = $updateDataAsset;
            return $this->sendResponse($success, 'Update data');
        } catch (\Throwable $th) {
            return $this->sendError('Error!', $th);
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
            // \DB::enableQueryLog();
            $checkAssets = Assets::whereIn('id', $ids)->get();
            // dd(\DB::getQueryLog());
            // dd($checkAssets);
            if($checkAssets->isEmpty()){
                return $this->sendError('Error!', ['error'=> 'Tidak ada data yang dihapus!']);
            }
            // \DB::enableQueryLog();
            $deleteAssets = Assets::findMany($ids);
            // dd(\DB::getQueryLog());\
            $totalDelete = 0;
            foreach($deleteAssets as $rowAssets){
                // dd($rowAssets->id);
                // if($rowAssets > 0){
                // dd($rowAssets->id);
                $deleteAssets = Assets::find($rowAssets->id);
                $deleteAssets->deleted_at = Carbon::now();
                $deleteAssets->delete();
                $totalDelete++;
                // }
            }

            $tokenMsg = Str::random(15);
            $success['token'] = $tokenMsg;
            $success['message'] = "Delete selected data";
            $success['total_deleted'] = $totalDelete;
            return $this->sendResponse($success, 'Data terpilih berhasil dihapus');
        } catch (\Throwable $th) {
            return $this->sendError('Error!', $th);
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
            return $this->sendError('Error!', $th);
        }
    }
}
