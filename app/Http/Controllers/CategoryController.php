<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    public function showCategory(){
        $categories = Category::all();
        return ResponseMethod('Category list',$categories);
    }

    public function showSingleCategory($id){
        $category = Category::where('id',$id)->first();
        if($category){
            return ResponseMethod('Category list',$category);
        }
        else{
            return ResponseMessage('Category Not Exist');
        }

    }

    public function addCategory(Request $request){
        $validator = Validator::make($request->all(),[
            'category_name' => 'required|string|min:2|max:15|unique:categories',
            'category_image' => 'required|mimes:jpg,png,jpeg,gif,svg|max:2048',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(),400);
        }
        $category = new Category();
        $category->category_name = $request->category_name;
        if ($request->file('category_image')){
            $category->category_image = $this->saveImage($request);
        }
        $category->save();
        return ResponseMethod('Category add successfully',$category);
    }
    public function updateCategory(Request $request,$id){
        $category = Category::where('id',$id)->first();
        if ($category) {
            $validator = Validator::make($request->all(),[
                'category_name' => 'string|min:2|max:15',
                'category_image' => 'mimes:jpg,png,jpeg,gif,svg|max:2048',
            ]);
            if ($validator->fails()) {
                return response()->json($validator->errors(),400);
            }

            $category->category_name = $request->category_name;
            if ($request->file('category_image')) {
                unlink($category->category_image);
                $category->category_image = $this->saveImage($request);
            }

            $category->update();
            return responseMethod('Category update successfully', $category);
        } else {
            return responseMessage('Category Not found');
        }
    }

    public function deleteCategory($id){

        $category = Category::where('id',$id)->first();
        if($category){
            if ($category->category_image) {
                unlink($category->category_image);
            }
            $category->delete();
            return responseMessage('Category delete successfully');
        }
        return responseMessage('Category Not Found');
    }

    protected function saveImage($request){
        $image = $request->file('category_image');
        $imageName = rand() . '.' . $image->getClientOriginalExtension();
        $directory = 'adminAsset/category-image/';
        $imgUrl = $directory . $imageName;
        $image->move($directory, $imageName);
        return $imgUrl;
    }

}
