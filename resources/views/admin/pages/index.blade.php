@extends('layouts.admin')

@section('title', 'Kelola Halaman')

@push('styles')
<style>
    .product-list-card { background-color:var(--white); border-radius:16px; box-shadow:0 4px 6px -1px rgba(0,0,0,0.05); padding:24px; }
    .btn-primary { background-color:var(--primary-color); color:var(--white); border:none; padding:10px 20px; border-radius:8px; font-weight:500; cursor:pointer; display:inline-flex; align-items:center; gap:8px; text-decoration:none; transition:background-color 0.2s; }
    .btn-primary:hover { background-color:#b71c1c; }
</style>
@endpush

@section('content')
<div class="page-header">
    <h1>Kelola Halaman Statis</h1>
    <p>Halaman informasi seperti Tentang Kami, Syarat & Ketentuan, dll.</p>
</div>

<div class="product-actions-top" style="display:flex; justify-content:flex-end; gap:10px; margin-bottom:20px;">
    <a href="{{ route('admin.pages.create') }}" class="btn-primary">
        <i class="ti ti-plus"></i> Tambah Halaman
    </a>
</div>

<div class="product-list-card">
    <div class="table-header-actions" style="margin-bottom:20px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:15px;">
        <div class="filter-toolbar" style="display:flex; gap:10px;">
            <span style="font-size:0.9rem; color:var(--text-light);">Menampilkan <strong>{{ $pages->count() }}</strong> halaman</span>
        </div>
        <div class="search-control">
            <form method="GET" action="{{ route('admin.pages.index') }}">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari halaman..." style="background:#f8fafc; border:1px solid #e2e8f0; width:250px; padding:8px 16px; border-radius:6px; outline:none; font-family:inherit;">
            </form>
        </div>
    </div>
    <div class="table-responsive">
        <table class="admin-table" id="pagesTable" style="width:100%">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Judul Halaman</th>
                    <th>Slug URL</th>
                    <th>Penulis</th>
                    <th>Terakhir Diubah</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pages as $i => $page)
                <tr>
                    <td>{{ $pages->firstItem() + $i }}</td>
                    <td><strong>{{ $page->title }}</strong></td>
                    <td><a href="{{ url($page->slug) }}" target="_blank" style="color:var(--text-light); text-decoration:none;">/{{ $page->slug }}</a></td>
                    <td>{{ $page->author->name ?? 'Admin Utama' }}</td>
                    <td>{{ $page->updated_at->format('d M Y') }}</td>
                    <td>
                        @if($page->status === 'public')
                            <span class="badge-status active">Publik</span>
                        @else
                            <span class="badge-status pending">Draft</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('admin.pages.edit', $page->id) }}" class="action-btn view" title="Edit"><i class="ti ti-edit"></i></a>
                        <form action="{{ route('admin.pages.destroy', $page->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Hapus halaman ini?');">
                            @csrf @method('DELETE')
                            <button type="submit" class="action-btn delete" title="Hapus"><i class="ti ti-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align:center; padding:40px; color:var(--text-light);">
                        <i class="ti ti-notebook" style="font-size:2rem; display:block; margin-bottom:10px; opacity:0.3;"></i>
                        Belum ada halaman statis. Klik "Tambah Halaman" untuk mulai membuat konten.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($pages->hasPages())
    <div style="padding:15px 0; border-top:1px solid var(--border-color); margin-top:15px;">
        {{ $pages->appends(request()->query())->links('pagination::bootstrap-4') }}
    </div>
    @endif
</div>
@endsection
