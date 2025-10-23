<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    public function index()
    {
        return Category::all();
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $category = Category::create($request->all());
        return response()->json(['id' => $category->id], 201);
    }

    public function show(Category $category): CategoryResource
    {
        $category->load('parent');
        return new CategoryResource($category);
    }

    public function update(UpdateCategoryRequest $request, Category $category): Category
    {
        $category->update($request->all());
        return $category;
    }

    public function destroy(Category $category): JsonResponse
    {
        $category->delete();
        return response()->json([], 204);
    }
}
