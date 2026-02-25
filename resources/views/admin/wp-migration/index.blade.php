@extends('layouts.admin')

@section('title', 'Impor Katalog Historis WordPress')

@push('styles')
<style>
    .migration-card { background: var(--white); border-radius: 16px; padding: 24px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); margin-bottom: 24px; }
    .status-badge { display: inline-flex; align-items: center; gap: 5px; padding: 6px 12px; border-radius: 50px; font-size: 0.85rem; font-weight: 500; }
    .status-success { background: #dcfce7; color: #166534; }
    .status-error { background: #fee2e2; color: #991b1b; }
    .status-warning { background: #fef08a; color: #854d0e; }
    
    .table-responsive { overflow-x: auto; margin-top: 15px; border-radius: 8px; border: 1px solid var(--border-color); }
    .preview-table { width: 100%; border-collapse: collapse; min-width: 600px; }
    .preview-table th, .preview-table td { padding: 12px 15px; text-align: left; border-bottom: 1px solid var(--border-color); font-size: 0.9rem; }
    .preview-table th { background-color: #f8fafc; font-weight: 600; color: #475569; }
    .preview-table tbody tr:hover { background-color: #f1f5f9; }
    
    .btn-sm { padding: 6px 12px; font-size: 0.85rem; border-radius: 6px; cursor: pointer; border: none; font-weight: 600; display: inline-flex; align-items: center; gap: 5px; }
    .btn-preview { background-color: #3b82f6; color: white; }
    .btn-preview:hover { background-color: #2563eb; }
    .btn-imported { background-color: #cbd5e1; color: #475569; cursor: not-allowed; }
    
    /* Pagination Styles */
    .pagination-wrapper { margin-top: 20px; display: flex; justify-content: space-between; align-items: center; }
    
    /* Modal Styles */
    .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: flex; justify-content: center; align-items: center; z-index: 1000; opacity: 0; visibility: hidden; transition: 0.2s; }
    .modal-overlay.active { opacity: 1; visibility: visible; }
    .modal-box { background: white; padding: 24px; border-radius: 12px; width: 90%; max-width: 500px; transform: translateY(20px); transition: 0.3s; }
    .modal-overlay.active .modal-box { transform: translateY(0); }
    .preview-img { width: 100%; height: 200px; object-fit: cover; border-radius: 8px; background: #e2e8f0; margin-bottom: 15px; display: flex; align-items: center; justify-content: center; color: #94a3b8; }
    .preview-meta { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px; }
    .meta-box { background: #f8fafc; padding: 12px; border-radius: 8px; border: 1px solid #e2e8f0; }
    .meta-label { font-size: 0.75rem; color: #64748b; text-transform: uppercase; font-weight: 600; margin-bottom: 4px; }
    .meta-value { font-size: 1rem; font-weight: 600; color: #0f172a; }
</style>
@endpush

@section('content')
<div class="page-header">
    <h1>Migrasi Data Katalog (WooCommerce)</h1>
    <p>Pratinjau detail produk dari WordPress (<code>rentaenterprise</code>) dan impor secara satuan beserta kategorinya secara otomatis.</p>
</div>

@if(session('success'))
<div style="background-color: #dcfce7; color: #166534; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #22c55e;">
    <i class="ti ti-check"></i> {{ session('success') }}
</div>
@endif

@if(session('error'))
<div style="background-color: #fee2e2; color: #b91c1c; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #ef4444;">
    <i class="ti ti-alert-triangle"></i> {{ session('error') }}
</div>
@endif

<div class="migration-card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <div>
            <h3 style="margin-bottom: 5px;">Status Koneksi Database WP</h3>
            <p style="color: var(--text-light); font-size: 0.9rem; margin: 0;">Memeriksa sambungan ke skema taxonomi dan post.</p>
        </div>
        
        @if($connectionStatus === 'connected')
            <span class="status-badge status-success"><i class="ti ti-plug-connected"></i> Terhubung</span>
        @else
            <span class="status-badge status-error"><i class="ti ti-plug-x"></i> Gagal: {{ Str::limit($connectionStatus, 30) }}</span>
        @endif
    </div>

    @if($connectionStatus === 'connected')
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
        <div style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 15px; border-radius: 8px; text-align: center;">
            <p style="color: #64748b; font-size: 0.85rem; text-transform: uppercase; font-weight: 600; margin-bottom: 5px;">Total Kategori Terdeteksi</p>
            <h2 style="font-size: 2rem; color: #0f172a; margin: 0;">{{ $totalWpCat }}</h2>
        </div>
        <div style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 15px; border-radius: 8px; text-align: center;">
            <p style="color: #64748b; font-size: 0.85rem; text-transform: uppercase; font-weight: 600; margin-bottom: 5px;">Total Produk Terdeteksi</p>
            <h2 style="font-size: 2rem; color: #0f172a; margin: 0;">{{ $totalWpProd }}</h2>
        </div>
    </div>
    @else
    <p style="color: #b91c1c; font-size: 0.9rem;">Periksa kembali variabel <code>DB_WP_*</code> di file <code>.env</code> Anda untuk memperbaiki koneksi.</p>
    @endif
</div>

@if($connectionStatus === 'connected')
<div class="migration-card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
        <div>
            <h3 style="margin: 0;">Daftar Produk WordPress</h3>
            <p style="font-size: 0.85rem; color: var(--text-light); margin: 5px 0 0 0;">Klik tombol Pratinjau untuk melihat detail Harga, Stok, dan Kategori sebelum mengimpor.</p>
        </div>
        
        <form action="{{ route('admin.migration.index') }}" method="GET" style="display: flex; gap: 10px;">
            <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Cari nama produk WP..." style="padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.9rem; width: 250px;">
            <button type="submit" style="padding: 8px 15px; background: var(--primary-color); color: white; border: none; border-radius: 6px; cursor: pointer; display: inline-flex; align-items: center; gap: 5px;"><i class="ti ti-search"></i> Cari</button>
            @if(!empty($search))
                <a href="{{ route('admin.migration.index') }}" style="padding: 8px 15px; background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; border-radius: 6px; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center;">Reset</a>
            @endif
        </form>
    </div>
    
    <div class="table-responsive">
        <table class="preview-table">
            <thead>
                <tr>
                    <th width="80">WP_ID</th>
                    <th>Judul Produk</th>
                    <th width="100">Status</th>
                    <th width="150" style="text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($wpProducts as $prod)
                <tr>
                    <td>{{ $prod->ID }}</td>
                    <td>{{ $prod->post_title }}</td>
                    <td><span style="font-size: 0.8rem; padding: 2px 6px; border-radius: 4px; background: {{ $prod->post_status == 'publish' ? '#dcfce7' : '#f1f5f9' }}; color: {{ $prod->post_status == 'publish' ? '#166534' : '#475569' }}">{{ $prod->post_status }}</span></td>
                    <td style="text-align: center;">
                        @if(in_array($prod->ID, $importedProductIds))
                            <span class="btn-sm btn-imported"><i class="ti ti-check"></i> Diimpor</span>
                        @else
                            <button class="btn-sm btn-preview" onclick="openPreview({{ $prod->ID }})"><i class="ti ti-eye"></i> Detail & Impor</button>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" style="text-align: center;">Tidak ada produk {{ !empty($search) ? 'dengan kata kunci tersebut' : '' }} ditemukan</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <div class="pagination-wrapper">
        <div style="font-size: 0.85rem; color: #64748b;">
            Menampilkan {{ $wpProducts->firstItem() ?? 0 }} - {{ $wpProducts->lastItem() ?? 0 }} dari {{ $wpProducts->total() }} produk
        </div>
        <div>
            {{ $wpProducts->links('pagination::bootstrap-4') }}
        </div>
    </div>
</div>
@endif

<!-- Preview Modal -->
<div class="modal-overlay" id="previewModal">
    <div class="modal-box">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
            <h3 style="margin: 0; font-size: 1.25rem;">Pratinjau Produk WP</h3>
            <button onclick="closePreview()" style="background: none; border: none; font-size: 1.25rem; cursor: pointer; color: #64748b;"><i class="ti ti-x"></i></button>
        </div>
        
        <div id="modalLoading" style="text-align: center; padding: 30px; color: #64748b;">
            <i class="ti ti-loader" style="animation: spin 1s linear infinite; font-size: 2rem; margin-bottom: 10px;"></i>
            <p>Mengambil data dari WordPress...</p>
        </div>
        
        <div id="modalContent" style="display: none;">
            <div class="preview-img" id="prevImage">
                <i class="ti ti-photo" style="font-size: 3rem;"></i>
            </div>
            
            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 15px;">
                <h4 id="prevTitle" style="margin: 0; font-size: 1.15rem; color: #0f172a;">Nama Produk</h4>
                <a id="prevLink" href="#" target="_blank" style="padding: 4px 10px; font-size: 0.75rem; border: 1px solid #cbd5e1; border-radius: 4px; color: #475569; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
                    <i class="ti ti-external-link"></i> Web Lama
                </a>
            </div>
            
            <div class="preview-meta">
                <div class="meta-box" style="grid-column: span 2;">
                    <div class="meta-label">Sistem Harga Sewa (RnB WP)</div>
                    <div class="meta-value" id="prevPriceType" style="color: var(--primary-color); "><i class="ti ti-tags"></i> -</div>
                </div>
                <div class="meta-box">
                    <div class="meta-label">Sewa Aktual</div>
                    <div class="meta-value">Rp <span id="prevPrice">0</span></div>
                </div>
                <div class="meta-box">
                    <div class="meta-label">Stok Tersedia</div>
                    <div class="meta-value" id="prevStock">0</div>
                </div>
                <div class="meta-box" style="grid-column: span 2;">
                    <div class="meta-label">Kategori (Otomatis Diimpor Bersama)</div>
                    <div class="meta-value" id="prevCategory">Kategori</div>
                </div>
            </div>
            
            <div style="background: #fffbeb; border: 1px solid #fde047; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 0.85rem; color: #854d0e;">
                <i class="ti ti-info-circle"></i> Info: Kategori beserta induknya akan diimpor (dibuat) agar hierarki pemetaan sejalan.
            </div>
            
            <form id="importForm" method="POST">
                @csrf
                <div style="display: flex; gap: 10px; justify-content: flex-end;">
                    <button type="button" onclick="closePreview()" style="padding: 10px 15px; border-radius: 6px; border: 1px solid #e2e8f0; background: white; cursor: pointer; font-weight: 500;">Batal</button>
                    <button type="submit" style="padding: 10px 20px; border-radius: 6px; background: var(--primary-color); color: white; border: none; cursor: pointer; font-weight: 600; display: inline-flex; align-items: center; gap: 5px;">
                        <i class="ti ti-check"></i> Konfirmasi Impor
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<style>
    @keyframes spin { 100% { transform: rotate(360deg); } }
</style>
<script>
    const modal = document.getElementById('previewModal');
    const modalLoading = document.getElementById('modalLoading');
    const modalContent = document.getElementById('modalContent');
    const importForm = document.getElementById('importForm');
    
    function openPreview(id) {
        modal.classList.add('active');
        modalLoading.style.display = 'block';
        modalContent.style.display = 'none';
        
        // Fetch Details
        fetch(`/wp-admin/migration/product/${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    alert('Gagal memuat produk: ' + data.error);
                    closePreview();
                    return;
                }
                
                document.getElementById('prevTitle').textContent = data.name;
                document.getElementById('prevPrice').textContent = data.price;
                document.getElementById('prevStock').textContent = data.stock;
                document.getElementById('prevCategory').textContent = data.category;
                document.getElementById('prevPriceType').innerHTML = '<i class="ti ti-tags"></i> ' + data.price_type_label;
                
                const prevLink = document.getElementById('prevLink');
                if (data.url && data.url !== '#') {
                    prevLink.href = data.url;
                    prevLink.style.display = 'inline-flex';
                } else {
                    prevLink.style.display = 'none';
                }
                
                const imgContainer = document.getElementById('prevImage');
                if (data.image) {
                    imgContainer.innerHTML = `<img src="${data.image}" style="width:100%; height:100%; object-fit:cover; border-radius:8px;" onerror="this.src='{{ asset('assets/images/mockup.png') }}'">`;
                } else {
                    imgContainer.innerHTML = `<div style="text-align:center;"><i class="ti ti-photo-off" style="font-size: 3rem; margin-bottom: 5px; display:block;"></i><span>Tanpa Gambar</span></div>`;
                }
                
                // Set Form Action Route
                importForm.action = `/wp-admin/migration/product/${id}/import`;
                
                modalLoading.style.display = 'none';
                modalContent.style.display = 'block';
            })
            .catch(error => {
                console.error(error);
                alert('Terdapat kesalahan jaringan saat memuat pratinjau.');
                closePreview();
            });
    }
    
    function closePreview() {
        modal.classList.remove('active');
    }
</script>
@endpush
