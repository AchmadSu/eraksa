<?php

namespace App\Http\Controllers\API;

use Carbon\Carbon;
use App\Models\Assets;
use App\Models\CategoryAssets;
use App\Models\StudyPrograms;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Auth;
use function PHPUnit\Framework\isEmpty;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\API\BaseController;

class StudyProgramsController extends BaseController
{
    /** ATTRIVE STUDY PROGRAMS */

    /** 
     * Get All Study Programs
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
    */

    public function index(Request $request){
        try {
            // dd(base64_encode(2));
            $sleep = $request->sleep;
            if($sleep) {
                sleep($sleep);
            } else {
                sleep(5);
            }
            // dd(Auth::user());
            // dd(Auth::user()->name);
            $ids = $request->ids;
            $name = $request->name;
            $skip = $request->skip;
            $take = $request->take;
            $trash = $request->trash;
            $studyPrograms = StudyPrograms::
            when(isset($ids))
            ->whereIn('id', $ids)
            ->when(isset($name))
            ->where('name', 'like', '%'.$name.'%')
            ->orderBy('name', 'ASC')
            ->when($trash == 1)
            ->onlyTrashed()
            ->get();
            // dd($studyPrograms);
            if ($studyPrograms->isEmpty()) {
                return $this->sendError('Error!', ['error' => 'Data tidak ditemukan!']);
            }
            // dd(\DB::getQueryLog());
            // \DB::enableQueryLog();
            $countDelete = StudyPrograms::onlyTrashed()->count();
            // dd(\DB::getQueryLog());
            $success['count'] = $studyPrograms->count();
            $success['countDelete'] = $countDelete;
            $success['study_programs'] = $studyPrograms
                ->when(isset($skip))
                ->skip($skip)
                ->when(isset($take))
                ->take($take)
            ;
            return $this->sendResponse($success, 'Displaying all assets data');
        } catch (\Throwable $th) {
            return $this->sendError('Error!', ['error' => "Permintaan tidak dapat dilakukan"]);
        }
    }

    /** 
     * Get Study Programs By Id
     * 
     * @param Int $id
     * @return \Illuminate\Http\Response
    */

    public function show(Int $id)
    {
        try {
            sleep(5);
            // \DB::enableQueryLog();
            $studyPrograms = StudyPrograms::find($id);
            // dd(\DB::getQueryLog());
            if (!$studyPrograms) {
                return $this->sendError('Error!', ['error' => 'Data tidak ditemukan!']);
            }
            return $this->sendResponse($studyPrograms, 'Program Studi detail');
        } catch (\Throwable $th) {
            return $this->sendError('Error!', ['error' => "Permintaan tidak dapat dilakukan"]);
        }
        
    }

    /** CRUD STUDY PROGRAMS */
    
    /**
     * Create Study Programs
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */

    public function create(Request $request){
        try {
            sleep(5);
            $validator = Validator::make($request->all(),[
                'name' => 'required|unique:study_programs,name|min:3',
            ]);
    
            if ($validator->fails()){
                return $this->sendError('Error!', ['error' => 'Nama sudah tersedia. Gunakan nama yang lain!']);
            }
    
            $name = ucwords(strtolower($request->name));

            $input = array(
                "name" => $name
            );
            $createStudyPrograms = StudyPrograms::create($input);
            $success['token'] = Str::random(15);
            return $this->sendResponse($success, 'Program Studi ditambahkan!');    
        } catch (\Throwable $th) {
            return $this->sendError('Error!', ['error' => "Permintaan tidak dapat dilakukan"]);
        } 
    }

    /**
     * Update Study Programs
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
                return $this->sendError('Error!', ['error' => 'Tidak ada program studi yang dipilih!']);
            }
            $name = $request->name;
            $validator = Validator::make($request->all(),[
                'name' => 'required|unique:study_programs,name|min:3'
            ]);
                
            if ($validator->fails()) {
                return $this->sendError('Error!', ['error'=>'Nama sudah tersedia. Gunakan nama yang lain!']);
            }

            // dd($data);exit();

            $updateDataStudyProgram = StudyPrograms::where('id', $id)->update(['name' => $name]);
            $tokenMsg = Str::random(15);
            $success['token'] = $tokenMsg;
            $success['message'] = "Update data";
            $success['data'] = $updateDataStudyProgram;
            return $this->sendResponse($success, 'Program Studi berhasil diupdate!');
        } catch (\Throwable $th) {
            return $this->sendError('Error!', ['error' => "Permintaan tidak dapat dilakukan"]);
        }
    }

    /**
     * Put Multiple Study Programs into trash
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */

    public function delete(Request $request)
    {
        try {
            sleep(5);
            $ids = $request->ids;
            if(!$ids) {
                return $this->sendError('Error!', ['error' => 'Tidak ada program studi yang dipilih!']);
            }
            // dd($ids);
            // \DB::enableQueryLog();
            $checkStudyPrograms = StudyPrograms::whereIn('id', $ids)->get();
            // dd(\DB::getQueryLog());
            // dd($checkStudyPrograms);
            if($checkStudyPrograms->isEmpty()){
                return $this->sendError('Error!', ['error'=> 'Tidak ada data yang dihapus!']);
            }
            $deleteStudyPrograms = StudyPrograms::whereIn('id', $ids)->update(['deleted_at' => Carbon::now()]);
            $tokenMsg = Str::random(15);
            $success['token'] = $tokenMsg;
            $success['message'] = "Delete selected data";
            $success['data'] = $deleteStudyPrograms;
            return $this->sendResponse($success, 'Data terpilih berhasil dihapus');
        } catch (\Throwable $th) {
            return $this->sendError('Error!', ['error' => "Permintaan tidak dapat dilakukan"]);
        }
    }

    /**
     * Restore Multiple Study Programs from trash
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
            $checkStudyPrograms =StudyPrograms::onlyTrashed()->whereIn('id', $ids)->get();
            // dd(\DB::getQueryLog());
            
            if($checkStudyPrograms->isEmpty()){
                return $this->sendError('Error!', ['error'=> 'Tidak ada data yang dipulihkan']);
            }
            $restoreCategoryAsset = StudyPrograms::onlyTrashed()->whereIn('id', $ids)->update(['deleted_at' => null]);
            $tokenMsg = Str::random(15);
            $success['token'] = $tokenMsg;
            $success['message'] = "Restore selected data";
            $success['data'] = $restoreCategoryAsset;
            return $this->sendResponse($success, 'Data terpilih berhasil dipulihkan');
        } catch (\Throwable $th) {
            return $this->sendError('Error!', ['error' => "Permintaan tidak dapat dilakukan"]);
        }
    }
}
