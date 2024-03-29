<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use App\Models\PretestPosttest;
use App\Models\CourseLesson;
use App\Models\Course;
use App\Models\LessonLecture;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid;
use File;

class LessonLectureController extends Controller
{
    public function show($uuid){
        try{
            $lecture = LessonLecture::select('uuid', 'lesson_uuid', 'title', 'body', 'file_path', 'url_path', 'file_size', 'file_duration', 'type')->where(['uuid' => $uuid])->first();

            return response()->json([
                'message' => 'Success get data',
                'lecture' => $lecture,
            ], 200);
        }
        catch(\Exception $e){
            return response()->json([
                'message' => $e,
            ], 404);
        }
    }

    public function store(Request $request){
        $checkLesson = CourseLesson::where(['uuid' => $request->lesson_uuid])->first();
        if(!$checkLesson){
            return response()->json([
                'message' => 'Lesson not found',
            ], 404);
        }
        $validate = [
            'lesson_uuid' => 'required|string',
            'title' => 'required|string',
        ];

        $validator = Validator::make($request->all(), $validate);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = [
            'lesson_uuid' => $request->lesson_uuid,
            'title' => $request->title,
        ];

        $lecture = LessonLecture::create($validated);

        return response()->json([
            'message' => 'Success create new lecture',
            'lecture' => [
                'lecture_uuid' => $lecture->uuid,
                'title' => $lecture->title,
            ],
        ], 200);

    }

    public function update(Request $request, $uuid){
        $checkLecture = LessonLecture::where(['uuid' => $uuid])->first();
        if(!$checkLecture){
            return response()->json([
                'message' => 'Lecture not found',
            ], 404);
        }

        $validate = [
            'title' => 'required|string',
            'body' => 'required|string',
            'type' => 'required|in:video,youtube,text,image,pdf,slide document,audio',
        ];

        if($request->type == "youtube"){
            $validate['url_path'] = "required";
        }

        if($request->type == "youtube" || $request->type == "video" || $request->type == "audio"){
            $validate['file_duration'] = "required";
        }

        $validator = Validator::make($request->all(), $validate);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $file_path = null;
        $url_path = null;
        $file_size = null;
        $file_duration = null;

        if($request->type == "text" || $request->type == "youtube"){
            if (File::exists(public_path('storage/'.$checkLecture->file_path))) {
                File::delete(public_path('storage/'.$checkLecture->file_path));
            }
        }

        if($request->type != "youtube" && $request->type != "text"){
            if(!is_string($request->file)){
                $file_size = $request->file->getSize();
                $file_path = $request->file->store('lectures', 'public');
                $file_size = round($file_size / (1024 * 1024), 2);
                if (File::exists(public_path('storage/'.$checkLecture->file_path))) {
                    File::delete(public_path('storage/'.$checkLecture->file_path));
                }
            }else{
                $file_path = $checkLecture->file_path;
                $file_size = $checkLecture->file_size;
            }
        }

        if($request->type == "youtube"){
            $url_path = $request->url_path;
        }

        if($request->type == "youtube" || $request->type == "video" || $request->type == "audio"){
            $file_duration = $request->file_duration;
        }

        $validated = [
            'title' => $request->title,
            'body' => $request->body,
            'type' => $request->type,
            'file_path' => $file_path,
            'url_path' => $url_path,
            'file_size' => $file_size,
            'file_duration' => $file_duration,
        ];

        $lecture = LessonLecture::where(['uuid' => $uuid])->update($validated);

        return response()->json([
            'message' => 'Success update lecture'
        ], 200);

    }

    public function delete(Request $request, $uuid){
        $get_lecture = LessonLecture::where([
            'uuid' => $uuid,
        ])->first();

        if($get_lecture == null){
            return response()->json([
                'message' => 'Data not found',
            ]);
        }

        $get_lesson = CourseLesson::where([
            'uuid' => $get_lecture->lesson_uuid,
        ])->first();

        $get_course = Course::where([
            'uuid' => $get_lesson->course_uuid,
        ])->first();

        // if($get_course->status == 'Published'){
        //     return response()->json([
        //         'message' => 'You cannot delete it, this course has published'
        //     ]);
        // }

        if($get_lecture->file_path){
            if (File::exists(public_path('storage/'.$get_lecture->file_path))) {
                File::delete(public_path('storage/'.$get_lecture->file_path));
            }
        }

        LessonLecture::where([
            'uuid' => $uuid,
        ])->delete();

        return response()->json([
            'message' => 'success delete lecture'
        ], 200);
    }
}
