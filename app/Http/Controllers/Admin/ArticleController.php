<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ArticleController extends Controller
{
    public function index(Request $request)
    {
        $query = Article::with('author');
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }
        $articles = $query->orderBy('created_at', 'desc')->paginate(10);
        return view('admin.articles.index', compact('articles'));
    }

    public function create()
    {
        return view('admin.articles.form');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'category' => 'nullable|string|max:100',
            'status' => 'required|in:public,draft,scheduled',
            'published_at' => 'nullable|date',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $validated['slug'] = Str::slug($validated['title']);
        $validated['author_id'] = auth()->id();

        if ($request->hasFile('thumbnail')) {
            $uploadedFileUrl = \CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary::upload($request->file('thumbnail')->getRealPath(), [
                'folder' => 'renta/articles'
            ])->getSecurePath();
            $validated['thumbnail'] = $uploadedFileUrl;
        }

        if (Article::where('slug', $validated['slug'])->exists()) {
            $validated['slug'] .= '-' . time();
        }

        Article::create($validated);
        return redirect()->route('admin.articles.index')->with('success', 'Artikel berhasil dibuat.');
    }

    public function edit(Article $article)
    {
        return view('admin.articles.form', compact('article'));
    }

    public function update(Request $request, Article $article)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'category' => 'nullable|string|max:100',
            'status' => 'required|in:public,draft,scheduled',
            'published_at' => 'nullable|date',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $validated['slug'] = Str::slug($validated['title']);

        if ($request->hasFile('thumbnail')) {
            // Kita bisa juga menambahkan proses menghapus gambar lama dari Cloudinary di sini jika punya public_id, namun dibiarkan upload/overwrite baru agar simpel.
            $uploadedFileUrl = \CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary::upload($request->file('thumbnail')->getRealPath(), [
                'folder' => 'renta/articles'
            ])->getSecurePath();
            $validated['thumbnail'] = $uploadedFileUrl;
        }

        if (Article::where('slug', $validated['slug'])->where('id', '!=', $article->id)->exists()) {
            $validated['slug'] .= '-' . time();
        }

        $article->update($validated);
        return redirect()->route('admin.articles.index')->with('success', 'Artikel berhasil diperbarui.');
    }

    public function destroy(Article $article)
    {
        $article->delete();
        return back()->with('success', 'Artikel berhasil dihapus.');
    }
}
