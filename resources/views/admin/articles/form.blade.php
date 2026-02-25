@extends('layouts.admin')

@section('title', isset($article) ? 'Edit Artikel' : 'Tulis Artikel')

@push('styles')
<!-- TinyMCE -->
<script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<style>
    .form-card { background-color:var(--white); border-radius:16px; box-shadow:0 4px 6px -1px rgba(0,0,0,0.05); padding:24px; }
    .btn-primary { background-color:var(--primary-color); color:var(--white); border:none; padding:10px 20px; border-radius:8px; font-weight:500; cursor:pointer; display:inline-flex; align-items:center; gap:8px; text-decoration:none; transition:background-color 0.2s; }
    .btn-primary:hover { background-color:#b71c1c; }
    .btn-secondary { background-color:#f1f5f9; color:#475569; border:none; padding:10px 20px; border-radius:8px; font-weight:500; cursor:pointer; display:inline-flex; align-items:center; gap:8px; text-decoration:none; transition:0.2s; }
    .form-group { margin-bottom:20px; }
    .form-label { display:block; margin-bottom:8px; font-weight:500; }
    .form-control, .form-select { width:100%; padding:10px 12px; border-radius:8px; border:1px solid var(--border-color); outline:none; font-family:inherit; transition:0.2s; }
    .form-control:focus, .form-select:focus { border-color:var(--primary-color) !important; box-shadow:0 0 0 3px rgba(211,47,47,0.1); }
</style>
@endpush

@section('content')
<div class="page-header" style="display:flex; justify-content:space-between; align-items:center;">
    <div>
        <h1>{{ isset($article) ? 'Edit Artikel' : 'Tulis Artikel Baru' }}</h1>
        <p>Tulis informasi, tips, atau berita promo.</p>
    </div>
    <a href="{{ route('admin.articles.index') }}" class="btn-secondary">
        <i class="ti ti-arrow-left"></i> Kembali
    </a>
</div>

<div class="form-card">
    <form action="{{ isset($article) ? route('admin.articles.update', $article->id) : route('admin.articles.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @if(isset($article)) @method('PUT') @endif
        
        <div class="form-group">
            <label class="form-label">Judul Artikel *</label>
            <input type="text" name="title" class="form-control" value="{{ old('title', $article->title ?? '') }}" placeholder="Misal: 5 Tips Memilih Sound System" required>
        </div>

        <div style="display:flex; gap:15px;">
            <div class="form-group" style="flex:1">
                <label class="form-label">Kategori</label>
                <input type="text" name="category" class="form-control" value="{{ old('category', $article->category ?? '') }}" placeholder="Misal: Tips & Trik">
            </div>
            <div class="form-group" style="flex:1">
                <label class="form-label">Tanggal Tayang (Opsional)</label>
                <input type="date" name="published_at" class="form-control" value="{{ old('published_at', isset($article) && $article->published_at ? $article->published_at->format('Y-m-d') : '') }}">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Thumbnail Artikel</label>
            @if(isset($article) && $article->thumbnail)
                <div style="margin-bottom:10px;">
                    <img src="{{ $article->thumbnail }}" alt="Thumbnail" style="height:120px; border-radius:8px; object-fit:cover;">
                </div>
            @endif
            <input type="file" name="thumbnail" class="form-control" accept="image/jpeg,image/png,image/jpg,image/webp">
            <small style="color:var(--text-light); display:block; margin-top:5px;">Isi jika ingin mengganti/menambahkan gambar tajuk. Maks 2MB.</small>
        </div>

        <div class="form-group">
            <label class="form-label">Isi Artikel *</label>
            <textarea id="tinymce-editor" name="content">{{ old('content', $article->content ?? '') }}</textarea>
        </div>

        <div style="display:flex; gap:15px; margin-top:20px;">
            <div class="form-group" style="flex:1">
                <label class="form-label">Status Tayang *</label>
                <select name="status" class="form-select">
                    <option value="draft" {{ old('status', $article->status ?? 'draft') === 'draft' ? 'selected' : '' }}>Simpan sebagai Draft</option>
                    <option value="scheduled" {{ old('status', $article->status ?? '') === 'scheduled' ? 'selected' : '' }}>Terjadwal</option>
                    <option value="public" {{ old('status', $article->status ?? '') === 'public' ? 'selected' : '' }}>Publikasikan Sekarang</option>
                </select>
            </div>
        </div>

        <div class="form-actions" style="margin-top:20px; text-align:right;">
            <button type="submit" class="btn-primary">
                <i class="ti ti-device-floppy"></i> Simpan Artikel
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    tinymce.init({
        selector: '#tinymce-editor',
        height: 500,
        menubar: false,
        plugins: [
            'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
            'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
            'insertdatetime', 'media', 'table', 'code', 'help', 'wordcount'
        ],
        toolbar: 'undo redo | blocks | ' +
        'bold italic forecolor | alignleft aligncenter ' +
        'alignright alignjustify | bullist numlist outdent indent | ' +
        'removeformat | image | help',
        content_style: 'body { font-family:Inter,Helvetica,Arial,sans-serif; font-size:14px }'
    });
</script>
@endpush
