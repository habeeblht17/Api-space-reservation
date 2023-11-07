<?php

namespace App\Http\Controllers\Admin;

use App\Models\Category;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\CategoryResource;
use App\Http\Requests\Admin\Category\StoreRequest;
use App\Http\Requests\Admin\Category\UpdateRequest;
use App\Traits\FileUploads;

class CategoryController extends Controller
{
    use FileUploads;

    /**
     * List all Category
     */
    public function index()
    {
        $categories = Category::with('spaces')->latest()->paginate();
        return CategoryResource::collection($categories);
    }

    /**
     * Store Category
     */
    public function store(StoreRequest $request)
    {
        $directory = 'categories/images';

        $request->validated($request->all());

        $imagePath = $this->handleImageUpload($request, $directory);

        $category = Category::create([
            'title' => $request->title,
            'description' => $request->description,
            'image' => $imagePath,
            'status' => 'active'
        ]);

        return (new CategoryResource($category))->additional([
            'message' => "Category Created Successfully",
        ]);
    }

    /**
     * show Category logic
     */
    public function show(Category $category)
    {
        return new CategoryResource($category);
    }

    /**
     * Update Category logic
     */
    public function update(UpdateRequest $request, Category $category)
    {
        $directory = 'categories/images';
        //Check for new file
        if($request->hasFile('image')) {

            $this->deleteImage($category->image);

            $imagePath = $this->handleImageUpload($request, $directory);
            $category->image = $imagePath;
        }

        $category->update($request->validated());
        return (new CategoryResource($category))->additional([
            'message' => 'Category Updated Successfully'
        ]);

    }

    /**
     * Delete Category logic
     */
    public function destroy(Category $category)
    {
        $this->deleteImage($category->image);
        $category->delete();
        return response()->json(['message' => 'Category deleted successfully']);
    }

}
