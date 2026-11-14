<?php

namespace App\Http\Controllers;

use App\Models\NewsCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class NewsCategoryController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:100', 'unique:news_categories,name']], ['name.unique' => 'มีหมวดหมู่นี้แล้ว']);
        NewsCategory::create($data);

        return back()->with('status', 'เพิ่มหมวดหมู่แล้ว');
    }

    public function update(Request $request, NewsCategory $category): RedirectResponse
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:100', Rule::unique('news_categories')->ignore($category->id)]]);
        $category->update($data);

        return back()->with('status', 'เปลี่ยนชื่อหมวดหมู่แล้ว');
    }

    public function destroy(NewsCategory $category): RedirectResponse
    {
        if ($category->posts()->exists()) {
            return back()->withErrors(['category' => 'หมวดนี้มีข่าวอยู่ กรุณาย้ายหรือลบข่าวก่อนลบหมวดหมู่']);
        }
        $category->delete();

        return back()->with('status', 'ลบหมวดหมู่แล้ว');
    }
}