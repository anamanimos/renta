@extends('layouts.admin')

@section('title', 'Kelola Produk')

@push('styles')
<style>
    .product-actions { display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:15px; }
    .filter-group { display:flex; gap:10px; flex-wrap:wrap; }
    .filter-select { padding:8px 16px; border:1px solid var(--border-color); border-radius:8px; outline:none; background-color:var(--white); color:var(--text-color); font-family:inherit; font-size:0.9rem; }
    .btn-primary { background-color:var(--primary-color); color:var(--white); border:none; padding:10px 20px; border-radius:8px; font-weight:500; cursor:pointer; display:inline-flex; align-items:center; gap:8px; text-decoration:none; transition:background-color 0.2s; }
    .btn-primary:hover { background-color:#b71c1c; }
    .product-list-card { background-color:var(--white); border-radius:16px; box-shadow:0 4px 6px -1px rgba(0,0,0,0.05); padding:24px; }
    .product-img-cell { display:flex; align-items:center; gap:12px; }
    .product-img-cell img { width:48px; height:48px; object-fit:cover; border-radius:8px; border:1px solid var(--border-color); }
    .product-img-cell .product-name { font-weight:600; color:var(--text-color); display:block; }
    .product-img-cell .product-category { font-size:0.8rem; color:var(--text-light); }
    .action-btn.edit { background-color:#eff6ff; color:#3b82f6; }
    .action-btn.delete { background-color:#fef2f2; color:var(--danger); }
    @media (max-width:768px) {
        .product-actions { flex-direction:column; align-items:stretch; }
        .btn-primary { justify-content:center; }
        .filter-select { flex:1; }
    }
</style>
@endpush

@section('content')
<div class="page-header">
    <h1>Kelola Produk</h1>
    <p>Manajemen inventaris peralatan sewa</p>
</div>

<!-- Actions Top -->
<div class="product-actions-top" style="display:flex; justify-content:flex-end; gap:10px; margin-bottom:20px;">
    <a href="{{ route('admin.categories.index') }}" class="btn btn-outline" style="background-color:#eff6ff; color:#3b82f6; border-color:#bfdbfe; display:inline-flex; align-items:center; gap:8px; padding:10px 20px; border-radius:8px; font-weight:500; text-decoration:none;">
        <i class="ti ti-category"></i> Kelola Kategori
    </a>
    <a href="{{ route('admin.products.create') }}" class="btn btn-primary" style="background-color:#3b82f6">
        <i class="ti ti-plus"></i> Tambah
    </a>
</div>

<!-- Filter and Search Row inside the card -->
<div class="product-list-card">
    <div class="table-header-actions" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:15px;">
        <div class="filter-controls" style="display:flex; gap:10px;">
            <span style="font-size:0.9rem; color:var(--text-light);">Menampilkan <strong>{{ $products->total() }}</strong> produk</span>
        </div>
        <div class="search-control">
            <input type="text" class="form-control" id="customSearch" placeholder="Cari data..." style="background-color:#f8fafc; border:1px solid var(--border-color); padding:10px 15px; border-radius:8px; font-size:0.9rem; width:250px;">
        </div>
    </div>
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Produk</th>
                    <th>SKU</th>
                    <th>Harga Sewa</th>
                    <th>Stok Tersedia</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                <tr>
                    <td>
                        <div class="product-img-cell">
                            @if($product->image)
                            <img src="{{ Str::startsWith($product->image, 'http') ? $product->image : asset($product->image) }}" alt="{{ $product->name }}">
                            @else
                            <img src="https://placehold.co/48x48?text=No+Img" alt="No Image">
                            @endif
                            <div>
                                <span class="product-name">{{ $product->name }}</span>
                                <span class="product-category">{{ $product->category->name ?? '-' }}</span>
                            </div>
                        </div>
                    </td>
                    <td>{{ $product->slug ?? '-' }}</td>
                    <td>Rp {{ number_format($product->price_per_day, 0, ',', '.') }} <small class="text-muted">/ hari</small></td>
                    <td>{{ $product->stock_quantity }} unit</td>
                    <td>
                        @if($product->is_active)
                        <span class="badge-status completed">Tersedia</span>
                        @else
                        <span class="badge-status reject">Nonaktif</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('admin.products.edit', $product->id) }}">
                            <button class="action-btn edit" title="Edit"><i class="ti ti-edit"></i></button>
                        </a>
                        <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Produk akan dihapus permanen. Yakin?');">
                            @csrf @method('DELETE')
                            <button type="submit" class="action-btn delete" title="Hapus"><i class="ti ti-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align:center; padding:40px; color:var(--text-light);">
                        <i class="ti ti-box" style="font-size:2rem; display:block; margin-bottom:10px; opacity:0.3;"></i>
                        Belum ada produk. Klik "Tambah" untuk menambahkan peralatan sewa pertama Anda.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($products->hasPages())
    <div style="padding:15px 0; border-top:1px solid var(--border-color); margin-top:15px;">
        {{ $products->links('pagination::bootstrap-4') }}
    </div>
    @endif
</div>
@endsection
