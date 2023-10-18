<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use App\Models\Category;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid;

class CategoryController extends Controller
{
    public function index(){
        try{
            $categories = Category::select('uuid', 'name')->get();
            return response()->json([
                'message' => 'Success get data',
                'categories' => $categories,
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
            $category = Category::select('uuid', 'name')->where(['uuid' => $uuid])->first();

            if(!$category){
                return response()->json([
                    'message' => 'Data not found',
                ], 404);
            }
            return response()->json([
                'message' => 'Success get data',
                'category' => $category,
            ], 200);
        }
        catch(\Exception $e){
            return response()->json([
                'message' => $e,
            ], 404);
        }
    }

    public function store(Request $request): JsonResponse{
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:categories',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        Category::create([
            'name' => $request->name,
        ]);

        return response()->json([
            'message' => 'Success create new category'
        ], 200);
    }

    public function update(Request $request, $uuid): JsonResponse{
        $checkCategory = Category::where(['uuid' => $uuid])->first();
        if(!$checkCategory){
            return response()->json([
                'message' => 'Data not found',
            ], 404);
        }

        if($checkCategory->name != $request->name){
            $validator = Validator::make($request->all(), [
                'name' => 'required|unique:categories',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            Category::where(['uuid' => $uuid])->update([
                'name' => $request->name,
            ]);
        }


        return response()->json([
            'message' => 'Success update category'
        ], 200);
    }

    public function delete(Request $request, $uuid){
        $checkCategory = Category::where(['uuid' => $uuid])->first();
        if(!$checkCategory){
            return response()->json([
                'message' => 'Data not found',
            ], 404);
        }

        Category::where(['uuid' => $uuid])->delete();


        return response()->json([
            'message' => 'Success delete category'
        ], 200);
    }
}
