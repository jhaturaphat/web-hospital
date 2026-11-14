<?php

namespace App\Http\Controllers;

use App\Models\NewsAttachment;
use App\Models\NewsCategory;
use App\Models\NewsPost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class NewsController extends Controller
{
    public function home(): View
    {
        return view('welcome', [
            'categories' => NewsCategory::orderBy('id')->get(),
            'newsPosts' => NewsPost::with('category')->withCount('attachments')->where('is_published', true)->latest()->latest('id')->limit(6)->get(),
        ]);
    }

    public function index(Request $request): View
    {
        $filters = $request->validate(['category' => ['nullable', 'integer', 'exists:news_categories,id'], 'q' => ['nullable', 'string', 'max:150']]);
        $query = NewsPost::with('category')->withCount('attachments')->where('is_published', true);
        if (!empty($filters['category'])) {
            $query->where('news_category_id', $filters['category']);
        }
        if (!empty($filters['q'])) {
            $query->where('title', 'like', '%'.$filters['q'].'%');
        }

        return view('news.index', [
            'categories' => NewsCategory::orderBy('id')->get(),
            'posts' => $query->latest()->latest('id')->paginate(12)->withQueryString(),
        ]);
    }

    public function show(NewsPost $post): View
    {
        abort_unless($post->is_published || auth()->user()?->can('manage-news'), 404);

        return view('news.show', ['post' => $post->load('category', 'attachments')]);
    }

    public function download(NewsAttachment $attachment): StreamedResponse
    {
        abort_unless($attachment->post->is_published || auth()->user()?->can('manage-news'), 404);
        abort_unless(Storage::disk('local')->exists($attachment->path), 404);

        // Force download; uploaded documents never execute in a public folder.
        return Storage::disk('local')->download($attachment->path, $attachment->name, ['X-Content-Type-Options' => 'nosniff']);
    }
}