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
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
    */

    public function index(Request $request){
        try {
            sleep(5);
            // dd(Auth::user());
            // dd(Auth::user()->name);
            // \DB::enableQueryLog();
            $name = $request->name;
            $placements = Placements::when(isset($name))
            ->where('name', 'like', '%'.$name.'%')
            ->get();
            // dd(\DB::getQueryLog());
            // dd($placements);
            if ($placements->isEmpty()) {
                return $this->sendError('Error!', ['error' => 'Data tidak ditemukan!']);
            }
            return $this->sendResponse($placements, 'Displaying all placements data');
        } catch (\Throwable $th) {
            return $this->sendError('Error!', ['error' => $th]);
        }
    }

    /** 
     * Get All Placement Assets in Trash
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
    */

    public function trash(Request $request){
        try {
            sleep(5);
            // dd(Auth::user());
            // dd(Auth::user());
            // \DB::enableQueryLog();
            $name = $request->name;
            $placements = Placements::onlyTrashed()
            ->when(isset($name))
            ->where('name', 'like', '%'.$name.'%')
            ->get();
            // dd(\DB::getQueryLog());
            if ($placements->isEmpty()) {
                return $this->sendError('Error!', ['error' => 'Data tidak ditemukan!']);
            }
            return $this->sendResponse($placements, 'Displaying all trash data');
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
            sleep(5);
            // \DB::enableQueryLog();
            $placements = Placements::where('id', $id)->first();
            // dd(\DB::getQueryLog());
            if (!$placements) {
                return $this->sendError('Error!', ['error' => 'Data tidak ditemukan!']);
            }
            return $this->sendResponse($placements, 'Placement detail');
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
            sleep(5);
            $validator = Validator::make($request->all(),[
                'name' => 'required|unique:placements,name|min:3',
            ]);
    
            if ($validator->fails()){
                return $this->sendError('Validator Error.', $validator->errors());
            }
    
            $name = $request->name;
            $input = array(
                "name" => ucwords(strtolower($name))
            );
            
            // dd(ucwords(strtolower($name)));
            $createCategory = Placements::create($input);
            $success['token'] = Str::random(15);
            return $this->sendResponse($success, 'Placement ditambahkan!');    
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
            sleep(5);
            $id = $request->id;
            $validator = Validator::make($request->all(),[
                'name' => 'required|unique:placements,name|min:3'
            ]);
                
            if ($validator->fails()) {
                return $this->sendError('Error!', $validator->errors());
            }

            // dd($data);exit();
            $name = ucwords(strtolower($request->name));

            $updateDataPlacements = Placements::where('id', $id)->update(['name' => $name]);
            $tokenMsg = Str::random(15);
            $success['token'] = $tokenMsg;
            $success['message'] = "Placement berhasil diupdate!";
            $success['data'] = $updateDataPlacements;
            return $this->sendResponse($success, 'Update data');
        } catch (\Throwable $th) {
            return $this->sendError('Error!', $th);
        }
    }

    /**
     * Put Multiple Placements into trash
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
        } catch (\Throwable $th) {
            return $this->sendError('Error!', $th);
        }
    }

    /**
     * Restore Multiple Placements from trash
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
            $checkPlacements = Placements::onlyTrashed()->whereIn('id', $ids)->get();
            // dd(\DB::getQueryLog());
            
            if($checkPlacements->isEmpty()){
                return $this->sendError('Error!', ['error'=> 'Tidak ada data yang dipulihkan']);
            }
            $restorePlacements = Placements::onlyTrashed()->whereIn('id', $ids)->update(['deleted_at' => null]);
            $tokenMsg = Str::random(15);
            $success['token'] = $tokenMsg;
            $success['message'] = "Restore selected data";
            $success['data'] = $restorePlacements;
            return $this->sendResponse($success, 'Data terpilih berhasil dipulihkan');
        } catch (\Throwable $th) {
            return $this->sendError('Error!', $th);
        }
    }
}
