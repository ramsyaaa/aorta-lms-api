<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Test;
use App\Models\Question;
use App\Models\QuestionTest;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Facades\Validator;

class TestController extends Controller
{
    public function index(){
        try{
            $tests = DB::table('tests')
                ->select('tests.uuid', 'tests.test_type', 'tests.name', 'tests.test_category')
                ->get();

            return response()->json([
                'message' => 'Success get data',
                'tests' => $tests,
            ], 200);
        }
        catch(\Exception $e){
            return response()->json([
                'message' => $e,
            ], 404);
        }
    }

    public function show(Request $request, $uuid){
        try{
            $test = Test::where([
                'uuid' => $uuid
            ])->with(['questions'])->first();

            if(!$test){
                return response()->json([
                    'message' => 'Data not found',
                ], 404);
            }
            return response()->json([
                'message' => 'Success get data',
                'test' => $test,
            ], 200);
        }
        catch(\Exception $e){
            return response()->json([
                'message' => $e,
            ], 404);
        }
    }

    public function store(Request $request): JsonResponse{
        $validate = [
            'test_type' => 'required|in:classical,IRT',
            'name' => 'required',
            'test_category' => 'required|in:quiz,tryout',
        ];

        $validator = Validator::make($request->all(), $validate);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = [
            'test_type' => $request->test_type,
            'name' => $request->name,
            'test_category' => $request->test_category,
        ];

        Test::create($validated);

        return response()->json([
            'message' => 'Success create new test'
        ], 200);
    }

    public function addQuestions(Request $request, $uuid): JsonResponse{
        $test = Test::where(['uuid' => $uuid])->first();
        if(!$test){
            return response()->json([
                'message' => 'Test not found',
            ], 404);
        }
        $validate = [
            'questions' => 'required|array',
            'questions.*' => 'required',
            'questions.*.uuid' => 'required',
            'questions.*.question_uuid' => 'required',
        ];

        $validator = Validator::make($request->all(), $validate);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $newQuestions = [];
        $allQuestionsUuid = [];
        foreach ($request->questions as $index => $question) {
            $checkQuestion = Question::where('uuid', $question['question_uuid'])->first();
            if(!$checkQuestion){
                return response()->json([
                    'message' => 'Question not found'
                ], 404);
            }

            $checkQuestionTest = QuestionTest::where('uuid', $question['uuid'])->first();

            if(!$checkQuestionTest){
                $newQuestions[] = [
                    'uuid' => Uuid::uuid4()->toString(),
                    'test_uuid' => $test->uuid,
                    'question_uuid' => $question['question_uuid'],
                ];
            }else{
                $allQuestionsUuid[] = $checkQuestionTest->uuid;
            }
        }

        QuestionTest::whereNotIn('uuid', $allQuestionsUuid)->delete();

        if(count($newQuestions) > 0){
            QuestionTest::insert($newQuestions);
        }

        return response()->json([
            'message' => 'Success update questions'
        ], 200);
    }

    public function update(Request $request, $uuid): JsonResponse{
        $test = Test::where(['uuid' => $uuid])->first();
        if(!$test){
            return response()->json([
                'message' => 'Test not found',
            ], 404);
        }
        $validate = [
            'test_type' => 'required|in:classical,IRT',
            'name' => 'required',
            'test_category' => 'required|in:quiz,tryout',
        ];

        $validator = Validator::make($request->all(), $validate);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        Test::where(['uuid' => $uuid])->update([
            'test_type' => $request->test_type,
            'name' => $request->name,
            'test_category' => $request->test_category,
        ]);

        return response()->json([
            'message' => 'Success update test'
        ], 200);
    }
}