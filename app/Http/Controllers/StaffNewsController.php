<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveNewsPostRequest;
use App\Models\NewsAttachment;
use App\Models\NewsCategory;
use App\Models\NewsPost;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Throwable;

class StaffNewsController extends Controller
{
    public function index(): View
    {
        return view('staff.index', [
            'posts' => NewsPost::with('category')->latest()->latest('id')->paginate(15),
            'categories' => NewsCategory::withCount('posts')->orderBy('id')->get(),
        ]);
    }

    public function create(): View
    {
        return view('staff.form', ['post' => new NewsPost, 'categories' => NewsCategory::orderBy('id')->get()]);
    }

    public function store(SaveNewsPostRequest $request): RedirectResponse
    {
        $post = new NewsPost;
        $this->save($request, $post);

        return redirect()->route('staff.posts.edit', $post)->with('status', 'เพิ่มข่าวเรียบร้อยแล้ว');
    }

    public function edit(NewsPost $post): View
    {
        return view('staff.form', ['post' => $post->load('attachments'), 'categories' => NewsCategory::orderBy('id')->get()]);
    }

    public function update(SaveNewsPostRequest $request, NewsPost $post): RedirectResponse
    {
        $this->save($request, $post);

        return redirect()->route('staff.posts.edit', $post)->with('status', 'บันทึกการเปลี่ยนแปลงแล้ว');
    }

    private function save(SaveNewsPostRequest $request, NewsPost $post): void
    {
        $newPaths = [];
        try {
            DB::transaction(function () use ($request, $post, &$newPaths): void {
                $post->fill($request->safe()->only(['title', 'body', 'news_category_id', 'is_published']));
                $post->save();
                foreach ($request->file('attachments', []) as $file) {
                    $path = $file->store('news', 'local');
                    if ($path === false) {
                        throw new \RuntimeException('ไม่สามารถจัดเก็บไฟล์ได้ กรุณาลองใหม่');
                    }
                    $newPaths[] = $path;
                    $post->attachments()->create(['path' => $path, 'name' => $file->getClientOriginalName(), 'size' => $file->getSize()]);
                }
            });
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($newPaths);
            throw $exception;
        }
    }

    public function destroy(NewsPost $post): RedirectResponse
    {
        $paths = $post->attachments()->pluck('path')->all();
        $post->delete();
        Storage::disk('local')->delete($paths);

        return redirect()->route('staff.posts.index')->with('status', 'ลบข่าวและไฟล์แนบแล้ว');
    }

    public function destroyAttachment(NewsPost $post, NewsAttachment $attachment): RedirectResponse
    {
        abort_unless($attachment->news_post_id === $post->id, 404);
        $path = $attachment->path;
        $attachment->delete();
        Storage::disk('local')->delete($path);

        return back()->with('status', 'ลบไฟล์แนบแล้ว');
    }
}
