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
            $trash = $request->trash;
            $skip = $request->skip;
            $take = $request->take;
            $placements = Placements::
            when(isset($name))
            ->where('name', 'like', '%'.$name.'%')
            ->orderBy('name', 'ASC')
            ->when($trash == 1)
            ->onlyTrashed()
            ->get();
            // dd(\DB::getQueryLog());
            // dd($placements);
            if ($placements->isEmpty()) {
                return $this->sendError('Error!', ['error' => 'Data tidak ditemukan!']);
            }
            $count = $placements->count();
            $countDelete = Placements::onlyTrashed()->count();
            // dd(\DB::getQueryLog());
            $success['count'] = $count;
            $success['countDelete'] = $countDelete;
            $success['placements']= $placements
                ->when(isset($skip))
                ->skip($skip)
                ->when(isset($take))
                ->take($take)
            ;
            return $this->sendResponse($success, 'Displaying all placements data');
        } catch (\Throwable $th) {
            return $this->sendError('Error!', ['error' => "Permintaan tidak dapat dilakukan"]);
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
            $placements = Placements::find($id);
            // dd(\DB::getQueryLog());
            if (!$placements) {
                return $this->sendError('Error!', ['error' => 'Data tidak ditemukan!']);
            }
            return $this->sendResponse($placements, 'Placement detail');
        } catch (\Throwable $th) {
            return $this->sendError('Error!', ['error' => "Permintaan tidak dapat dilakukan"]);
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
                return $this->sendError('Error!', ['error'=>'Nama penempatan sudah tersedia. Gunakan nama yang lain!']);
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
            return $this->sendError('Error!', ['error' => "Permintaan tidak dapat dilakukan"]);
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
            if(!$id) {
                return $this->sendError('Error!', ['error' => 'Tidak ada penempatan yang dipilih!']);
            }
            $validator = Validator::make($request->all(),[
                'name' => 'required|unique:placements,name|min:3'
            ]);
                
            if ($validator->fails()) {
                return $this->sendError('Error!', ['error'=>'Nama penempatan sudah tersedia. Gunakan nama tempat yang lain!']);
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
            return $this->sendError('Error!', ['error' => "Permintaan tidak dapat dilakukan"]);
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
            return $this->sendError('Error!', ['error' => "Permintaan tidak dapat dilakukan"]);
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
            return $this->sendError('Error!', ['error' => "Permintaan tidak dapat dilakukan"]);
        }
    }
}
