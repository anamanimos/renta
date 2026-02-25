@extends('layouts.admin')

@section('title', 'Kelola Artikel')

@push('styles')
<style>
    .product-list-card { background-color:var(--white); border-radius:16px; box-shadow:0 4px 6px -1px rgba(0,0,0,0.05); padding:24px; }
    .btn-primary { background-color:var(--primary-color); color:var(--white); border:none; padding:10px 20px; border-radius:8px; font-weight:500; cursor:pointer; display:inline-flex; align-items:center; gap:8px; text-decoration:none; transition:background-color 0.2s; }
    .btn-primary:hover { background-color:#b71c1c; }
    .table-thumbnail { display:flex; align-items:center; gap:12px; }
    .table-thumbnail img { width:60px; height:60px; object-fit:cover; border-radius:8px; border:1px solid var(--border-color); }
</style>
@endpush

@section('content')
<div class="page-header">
    <h1>Kelola Artikel Blog</h1>
    <p>Tulis informasi, tips, dan promo untuk pengunjung website.</p>
</div>

<div class="product-actions-top" style="display:flex; justify-content:flex-end; gap:10px; margin-bottom:20px;">
    <a href="{{ route('admin.articles.create') }}" class="btn-primary">
        <i class="ti ti-plus"></i> Tulis Artikel
    </a>
</div>

<div class="product-list-card">
    <div class="table-header-actions" style="margin-bottom:20px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:15px;">
        <div class="filter-toolbar" style="display:flex; gap:10px;">
            <span style="font-size:0.9rem; color:var(--text-light);">Menampilkan <strong>{{ $articles->count() }}</strong> artikel</span>
        </div>
        <div class="search-control">
            <form method="GET" action="{{ route('admin.articles.index') }}">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari artikel..." style="background:#f8fafc; border:1px solid #e2e8f0; width:250px; padding:8px 16px; border-radius:6px; outline:none; font-family:inherit;">
            </form>
        </div>
    </div>
    <div class="table-responsive">
        <table class="admin-table" id="articlesTable" style="width:100%">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Artikel Utama</th>
                    <th>Kategori</th>
                    <th>Tanggal Tayang</th>
                    <th>Views</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($articles as $i => $article)
                <tr>
                    <td>{{ $articles->firstItem() + $i }}</td>
                    <td>
                        <div class="table-thumbnail">
                            <img src="{{ $article->thumbnail ?: 'https://placehold.co/100x100?text=Logo' }}" alt="Thumb">
                            <div>
                                <strong style="display:block; font-size:1rem; margin-bottom:4px;">{{ $article->title }}</strong>
                                <span style="font-size:0.8rem; color:var(--text-light);">Oleh: {{ $article->author->name ?? 'Admin' }}</span>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="badge" style="background:#f1f5f9; color:#475569;">{{ $article->category ?: 'Uncategorized' }}</span>
                    </td>
                    <td>{{ $article->published_at ? $article->published_at->format('d M Y') : '-' }}</td>
                    <td>{{ number_format($article->views, 0, ',', '.') }}</td>
                    <td>
                        @if($article->status === 'public')
                            <span class="badge-status active">Publik</span>
                        @elseif($article->status === 'scheduled')
                            <span class="badge-status pending">Terjadwal</span>
                        @else
                            <span class="badge-status" style="border: 1px solid #bfdbfe; color: #2563eb; background:transparent;">Draft</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('admin.articles.edit', $article->id) }}" class="action-btn view" title="Edit"><i class="ti ti-edit"></i></a>
                        <form action="{{ route('admin.articles.destroy', $article->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Hapus artikel ini?');">
                            @csrf @method('DELETE')
                            <button type="submit" class="action-btn delete" title="Hapus"><i class="ti ti-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align:center; padding:40px; color:var(--text-light);">
                        <i class="ti ti-article" style="font-size:2rem; display:block; margin-bottom:10px; opacity:0.3;"></i>
                        Belum ada artikel. Klik "Tulis Artikel" untuk membuat konten blog pertama.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($articles->hasPages())
    <div style="padding:15px 0; border-top:1px solid var(--border-color); margin-top:15px;">
        {{ $articles->appends(request()->query())->links('pagination::bootstrap-4') }}
    </div>
    @endif
</div>
@endsection
