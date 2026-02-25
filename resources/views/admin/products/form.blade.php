@extends('layouts.admin')

@section('title', isset($product) ? 'Edit Produk' : 'Tambah Produk Baru')

@section('header-left-extra')
<a href="{{ route('admin.products.index') }}" class="icon-btn" style="margin-right:15px; color:var(--text-color)">
    <i class="ti ti-arrow-left"></i> Kembali
</a>
@endsection

@push('styles')
<style>
    .form-card { background-color:var(--white); border-radius:16px; box-shadow:0 4px 6px -1px rgba(0,0,0,0.05); padding:30px; max-width:900px; }
    .form-section-title { font-size:1.1rem; color:var(--text-color); margin-bottom:20px; padding-bottom:10px; border-bottom:1px solid var(--border-color); display:flex; align-items:center; gap:8px; }
    .form-section-title i { color:var(--primary-color); }
    .form-group { margin-bottom:20px; }
    .form-row { display:grid; grid-template-columns:1fr 1fr; gap:20px; }
    .form-label { display:block; margin-bottom:8px; font-weight:500; font-size:0.95rem; color:var(--text-color); }
    .form-control, .form-select { width:100%; padding:12px 16px; border:1px solid var(--border-color); border-radius:8px; font-family:inherit; font-size:0.95rem; color:var(--text-color); background-color:var(--bg-color); transition:border-color 0.2s; }
    .form-control:focus, .form-select:focus { outline:none; border-color:var(--primary-color); box-shadow:0 0 0 3px rgba(211,47,47,0.1); }
    textarea.form-control { resize:vertical; min-height:120px; }
    .input-group { display:flex; align-items:stretch; }
    .input-group-text { background-color:var(--border-color); padding:12px 16px; border:1px solid var(--border-color); border-radius:8px 0 0 8px; color:var(--text-light); display:flex; align-items:center; }
    .input-group .form-control { border-radius:0 8px 8px 0; }
    .image-upload-wrapper { border:2px dashed var(--border-color); border-radius:12px; padding:40px 20px; text-align:center; cursor:pointer; transition:all 0.2s; background-color:var(--bg-color); }
    .image-upload-wrapper:hover { border-color:var(--primary-color); background-color:var(--primary-light); }
    .image-upload-wrapper i { font-size:2.5rem; color:var(--text-light); margin-bottom:10px; display:block; }
    .image-upload-wrapper p { color:var(--text-light); margin:0; font-size:0.9rem; }
    .form-actions { display:flex; justify-content:flex-end; gap:15px; margin-top:30px; padding-top:20px; border-top:1px solid var(--border-color); }
    .btn { padding:12px 24px; border-radius:8px; font-weight:500; cursor:pointer; font-family:inherit; font-size:0.95rem; transition:all 0.2s; display:inline-flex; align-items:center; gap:8px; text-decoration:none; }
    .btn-primary { background-color:var(--primary-color); color:var(--white); border:none; }
    .btn-primary:hover { background-color:#b71c1c; }
    .btn-outline { background-color:transparent; color:var(--text-color); border:1px solid var(--border-color); }
    .btn-outline:hover { background-color:var(--bg-color); }
    .current-image { max-width:150px; border-radius:8px; border:1px solid var(--border-color); margin-top:10px; }
    @media (max-width:768px) {
        .form-row { grid-template-columns:1fr; gap:0; }
        .form-actions { flex-direction:column; }
        .btn { width:100%; justify-content:center; }
    }
</style>
@endpush

@section('content')
<div class="page-header">
    <h1>{{ isset($product) ? 'Edit Produk' : 'Tambah Produk Baru' }}</h1>
    <p>{{ isset($product) ? 'Perbarui informasi peralatan sewa' : 'Masukkan informasi peralatan sewa secara detail' }}</p>
</div>

<div class="form-card">
    <form action="{{ isset($product) ? route('admin.products.update', $product->id) : route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @if(isset($product)) @method('PUT') @endif

        <!-- Informasi Dasar -->
        <h3 class="form-section-title">
            <i class="ti ti-info-circle"></i> Informasi Dasar
        </h3>

        <div class="form-group">
            <label class="form-label">Nama Produk</label>
            <input type="text" name="name" class="form-control" placeholder="Contoh: Stage (t: 40-60cm) + Karpet" value="{{ old('name', $product->name ?? '') }}" required>
            @error('name') <small style="color:var(--danger);">{{ $message }}</small> @enderror
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Kategori</label>
                <select name="category_id" class="form-select" required>
                    <option value="">Pilih Kategori</option>
                    @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ old('category_id', $product->category_id ?? '') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                    @endforeach
                </select>
                @error('category_id') <small style="color:var(--danger);">{{ $message }}</small> @enderror
            </div>
            <div class="form-group">
                <label class="form-label">SKU / Kode Barang</label>
                <input type="text" name="slug" class="form-control" placeholder="Contoh: STG-040-KPT" value="{{ old('slug', $product->slug ?? '') }}">
            </div>
        </div>

        <!-- Harga & Stok -->
        <h3 class="form-section-title" style="margin-top:20px;">
            <i class="ti ti-currency-dollar"></i> Harga & Ketersediaan
        </h3>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Tipe Harga</label>
                <select name="price_type" class="form-select" id="priceType" onchange="togglePriceFields()" required>
                    <option value="rental_flat" {{ old('price_type', $product->price_type ?? '') == 'rental_flat' ? 'selected' : '' }}>Sewa Flat (Per Hari)</option>
                    <option value="rental_tiered" {{ old('price_type', $product->price_type ?? '') == 'rental_tiered' ? 'selected' : '' }}>Sewa Tiered (Lebih dari 1 Hari lebih murah)</option>
                    <option value="sell_once" {{ old('price_type', $product->price_type ?? '') == 'sell_once' ? 'selected' : '' }}>Jual Putus (Sekali Beli)</option>
                </select>
                @error('price_type') <small style="color:var(--danger);">{{ $message }}</small> @enderror
            </div>
            <div class="form-group">
                <label class="form-label">Total Stok Tersedia</label>
                <input type="number" name="stock_quantity" class="form-control" placeholder="Contoh: 10" value="{{ old('stock_quantity', $product->stock_quantity ?? '') }}" required>
                @error('stock_quantity') <small style="color:var(--danger);">{{ $message }}</small> @enderror
            </div>
        </div>

        <div class="form-row" id="basePriceRow">
            <div class="form-group">
                <label class="form-label" id="basePriceLabel">Harga (Sewa/Beli)</label>
                <div class="input-group">
                    <span class="input-group-text">Rp</span>
                    <input type="number" name="price_per_day" class="form-control" id="basePrice" placeholder="0" value="{{ old('price_per_day', $product->price_per_day ?? '') }}" required>
                </div>
                @error('price_per_day') <small style="color:var(--danger);">{{ $message }}</small> @enderror
            </div>
            <div class="form-group">
                <label class="form-label">Harga Promo (Opsional)</label>
                <div class="input-group">
                    <span class="input-group-text">Rp</span>
                    <input type="number" name="promo_price" class="form-control" id="promoPrice" placeholder="Bila kosong berarti no-promo" value="{{ old('promo_price', $product->promo_price ?? '') }}">
                </div>
            </div>
        </div>

        <div class="form-row" id="tierPriceGroup" style="display:none;">
            <div class="form-group">
                <label class="form-label">Harga Hari ke-2 dan Seterusnya</label>
                <div class="input-group">
                    <span class="input-group-text">Rp</span>
                    <input type="number" name="tier_price" class="form-control" id="tierPrice" placeholder="0" value="{{ old('tier_price', $product->tier_price ?? '') }}">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Harga Promo Hari Ke-2 (Opsional)</label>
                <div class="input-group">
                    <span class="input-group-text">Rp</span>
                    <input type="number" name="tier_promo_price" class="form-control" id="tierPromoPrice" placeholder="Bila kosong berarti no-promo" value="{{ old('tier_promo_price', $product->tier_promo_price ?? '') }}">
                </div>
            </div>
        </div>

        <div id="priceExplanation" style="background-color:var(--bg-color); border:1px solid var(--border-color); border-radius:8px; padding:15px; margin-bottom:20px; font-size:0.9rem; color:var(--text-light);">
            <strong><i class="ti ti-info-circle"></i> Penjelasan Sistem Harga:</strong>
            <p style="margin-top:5px; margin-bottom:0;" id="explainText">
                <strong>Sewa Flat:</strong> Harga konstan setiap hari. <br><em>Rumus: Harga × Jumlah Hari × Kuantitas</em>
            </p>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Status Produk</label>
                <select name="is_active" class="form-select">
                    <option value="1" {{ old('is_active', $product->is_active ?? 1) == 1 ? 'selected' : '' }}>Aktif (Tersedia untuk disewa)</option>
                    <option value="0" {{ old('is_active', $product->is_active ?? 1) == 0 ? 'selected' : '' }}>Draft (Sembunyikan sementara)</option>
                </select>
            </div>
        </div>

        <!-- Media & Deskripsi -->
        <h3 class="form-section-title" style="margin-top:20px;">
            <i class="ti ti-photo"></i> Media & Deskripsi Lengkap
        </h3>

        <div class="form-group">
            <label class="form-label">Foto Produk</label>
            <div class="image-upload-wrapper" onclick="document.getElementById('fileInput').click()">
                <i class="ti ti-cloud-upload"></i>
                <p>Klik atau drop gambar ke sini untuk mengunggah</p>
                <p style="font-size:0.8rem; margin-top:5px;">Format: JPG, PNG, WebP. Maks: 2MB</p>
                <input type="file" id="fileInput" name="image" style="display:none" accept="image/*">
            </div>
            @if(isset($product) && $product->image)
            <div style="margin-top:15px;">
                <p style="font-size:0.85rem; color:var(--text-light); margin-bottom:5px;">Foto saat ini:</p>
                <img src="{{ Str::startsWith($product->image, 'http') ? $product->image : asset($product->image) }}" alt="Current" class="current-image">
            </div>
            @endif
            @error('image') <small style="color:var(--danger);">{{ $message }}</small> @enderror
        </div>

        <div class="form-group">
            <label class="form-label">Deskripsi Lengkap & Spesifikasi</label>
            <textarea name="description" class="form-control" placeholder="Tuliskan spesifikasi detail alat, brand, kapasitas...">{{ old('description', $product->description ?? '') }}</textarea>
            @error('description') <small style="color:var(--danger);">{{ $message }}</small> @enderror
        </div>

        <div class="form-actions">
            <a href="{{ route('admin.products.index') }}" class="btn btn-outline">Batal</a>
            <button type="submit" class="btn btn-primary">
                <i class="ti ti-device-floppy"></i> {{ isset($product) ? 'Simpan Perubahan' : 'Simpan Produk' }}
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    function togglePriceFields() {
        const type = document.getElementById('priceType').value;
        const tierGroup = document.getElementById('tierPriceGroup');
        const explainText = document.getElementById('explainText');
        const baseLabel = document.getElementById('basePriceLabel');

        if (type === 'rental_flat') {
            tierGroup.style.display = 'none';
            baseLabel.innerText = 'Harga Sewa per Hari';
            explainText.innerHTML = '<strong>Sewa Flat:</strong> Harga konstan setiap hari. <br><em>Rumus: Harga × Jumlah Hari × Kuantitas</em>';
        } else if (type === 'rental_tiered') {
            tierGroup.style.display = 'grid'; // because it's a form-row usually grid
            baseLabel.innerText = 'Harga Sewa Hari ke-1 (Base)';
            explainText.innerHTML = '<strong>Sewa Tiered:</strong> Hari pertama harga normal, hari berikutnya harga lebih murah. <br><em>Rumus: (Harga Base × Kuantitas) + (Harga Tier × (Jumlah Hari - 1) × Kuantitas)</em>';
        } else if (type === 'sell_once') {
            tierGroup.style.display = 'none';
            baseLabel.innerText = 'Harga Jual per Pcs/Unit';
            explainText.innerHTML = '<strong>Jual Putus:</strong> Pembelian aset sekali bayar tanpa hitungan durasi hari. <br><em>Rumus: Harga × Kuantitas</em>';
        }
    }

    // Initialize state on load
    document.addEventListener('DOMContentLoaded', function() {
        togglePriceFields();
    });
</script>
@endpush
