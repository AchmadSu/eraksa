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
     * @return \Illuminate\Http\Response
    */

    public function index(){
        try {
            sleep(5);
            // dd(Auth::user());
            // dd(Auth::user()->name);
            // \DB::enableQueryLog();
            $studyPrograms = StudyPrograms::all();
            // dd(\DB::getQueryLog());
            // dd($studyPrograms);
            if ($studyPrograms->isEmpty()) {
                return $this->sendError('Error!', ['error' => 'Data tidak ditemukan!']);
            }
            return $this->sendResponse($studyPrograms, 'Displaying all assets data');
        } catch (\Throwable $th) {
            return $this->sendError('Error!', ['error' => $th]);
        }
    }

    /** 
     * Get All Study Programs in Trash
     * 
     * @return \Illuminate\Http\Response
    */

    public function trash(){
        try {
            sleep(5);
            // dd(Auth::user());
            // dd(Auth::user());
            // \DB::enableQueryLog();
            $studyPrograms =StudyPrograms::onlyTrashed()->get();
            // dd(\DB::getQueryLog());
            if ($studyPrograms->isEmpty()) {
                return $this->sendError('Error!', ['error' => 'Data tidak ditemukan!']);
            }
            return $this->sendResponse($studyPrograms, 'Displaying all trash data');
        } catch (\Throwable $th) {
            return $this->sendError('Error!', ['error' => $th]);
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
            $studyPrograms = StudyPrograms::where('id', $id)->first();
            // dd(\DB::getQueryLog());
            if (!$studyPrograms) {
                return $this->sendError('Error!', ['error' => 'Data tidak ditemukan!']);
            }
            return $this->sendResponse($studyPrograms, 'Program Studi detail');
        } catch (\Throwable $th) {
            return $this->sendError('Error!', ['error' => $th]);
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
                return $this->sendError('Validator Error.', $validator->errors());
            }
    
            $input = $request->all();
            $createStudyPrograms = StudyPrograms::create($input);
            $success['token'] = Str::random(15);
            return $this->sendResponse($success, 'Program Studi ditambahkan!');    
        } catch (\Throwable $th) {
            return $this->sendError('Error!'.$th, ['error'=>$th]);
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
            $name = $request->name;
            $validator = Validator::make($request->all(),[
                'name' => 'required|unique:study_programs,name|min:3'
            ]);
                
            if ($validator->fails()) {
                return $this->sendError('Error!', $validator->errors());
            }

            // dd($data);exit();

            $updateDataStudyProgram = StudyPrograms::where('id', $id)->update(['name' => $name]);
            $tokenMsg = Str::random(15);
            $success['token'] = $tokenMsg;
            $success['message'] = "Update data";
            $success['data'] = $updateDataStudyProgram;
            return $this->sendResponse($success, 'Program Studi berhasil diupdate!');
        } catch (\Throwable $th) {
            return $this->sendError('Error!', $th);
        }
    }

    /**
     * Put Study Programs into trash
     * 
     * @param int $id
     * @return \Illuminate\Http\Response
     */

    public function delete(Int $id)
    {
        try {
            sleep(5);
            // \DB::enableQueryLog();
            $checkStudyPrograms = StudyPrograms::where('id', $id)->first();
            // dd(\DB::getQueryLog());
            if(!$checkStudyPrograms){
                return $this->sendError('Error!', ['error'=> 'Tidak ada data yang dihapus!']);
            }
            $deleteStudyPrograms = StudyPrograms::where('id', $id)->update(['deleted_at' => Carbon::now()]);
            $tokenMsg = Str::random(15);
            $success['token'] = $tokenMsg;
            $success['message'] = "Delete data";
            $success['data'] = $deleteStudyPrograms;
            return $this->sendResponse($success, 'Data berhasil dihapus');
        } catch (\Throwable $th) {
            return $this->sendError('Error!', $th);
        }
    }

    /**
     * Put Multiple Study Programs into trash
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
            return $this->sendError('Error!', $th);
        }
    }

    /**
     * Restore Program Study from trash
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
            $checkStudyPrograms =StudyPrograms::onlyTrashed()->where('id', $id)->get();
            // dd(\DB::getQueryLog());
            
            if($checkStudyPrograms->isEmpty()){
                return $this->sendError('Error!', ['error'=> 'Tidak ada data yang dipulihkan']);
            }
            $restoreCategoryAsset =StudyPrograms::onlyTrashed()->where('id', $id)->update(['deleted_at' => null]);
            $tokenMsg = Str::random(15);
            $success['token'] = $tokenMsg;
            $success['message'] = "Restore data";
            $success['data'] = $restoreCategoryAsset;
            return $this->sendResponse($success, 'Data berhasil dipulihkan');
        } catch (\Throwable $th) {
            return $this->sendError('Error!', $th);
        }
    }

    /**
     * Restore Multiple Study Programs from trash
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
            return $this->sendError('Error!', $th);
        }
    }
}
