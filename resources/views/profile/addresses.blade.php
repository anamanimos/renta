@extends('layouts.app')

@section('title', 'Buku Alamat | Renta Enterprise')

@push('styles')
<style>
    .address-book-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: none;
        margin-bottom: 20px;
        padding-bottom: 0;
    }

    .address-card {
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 25px;
        margin-bottom: 20px;
        background: #fff;
        position: relative;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        transition: var(--transition);
    }

    .address-card:hover {
        box-shadow: 0 8px 25px rgba(0,0,0,0.06);
        border-color: #eaeaea;
    }

    .address-card.is-main {
        border-color: var(--primary-color);
        background: #fffafa;
        box-shadow: 0 4px 15px rgba(211, 47, 47, 0.08);
    }

    .address-card.is-main::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 100%;
        background: linear-gradient(to bottom, var(--primary-color), #f76b6b);
        border-radius: 12px 0 0 12px;
    }

    .address-label {
        display: inline-block;
        font-size: 11px;
        font-weight: 700;
        padding: 5px 12px;
        background: #f0f0f0;
        border-radius: 20px;
        margin-bottom: 12px;
        color: #555;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .address-label.main-label {
        background: var(--primary-color);
        color: #fff;
    }

    .address-name {
        font-size: 16px;
        font-weight: 700;
        margin-bottom: 5px;
        color: var(--text-dark);
    }

    .address-phone {
        font-size: 14px;
        color: var(--text-light);
        margin-bottom: 15px;
    }

    .address-detail {
        font-size: 14px;
        color: var(--text-dark);
        line-height: 1.6;
        margin-bottom: 20px;
    }

    .address-actions {
        display: flex;
        gap: 15px;
        align-items: center;
        margin-top: 15px;
        padding-top: 20px;
        border-top: 1px solid #f0f0f0;
    }

    .address-actions a, .address-actions button.btn-action-link {
        font-size: 13px;
        color: var(--text-light);
        font-weight: 600;
        transition: var(--transition);
        border: 1px solid transparent;
        padding: 8px 15px;
        border-radius: 6px;
        background: #f9f9f9;
        cursor: pointer;
        text-decoration: none;
    }

    .address-actions a:hover, .address-actions button.btn-action-link:hover {
        color: var(--primary-color);
        background: #fff;
        border-color: var(--border-color);
        box-shadow: 0 2px 5px rgba(0,0,0,0.03);
    }

    .btn-set-main {
        background: none;
        border: 1px solid var(--primary-color);
        color: var(--primary-color);
        padding: 8px 18px;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn-set-main:hover {
        background: var(--primary-color);
        color: #fff;
        box-shadow: 0 4px 10px rgba(211, 47, 47, 0.2);
        transform: translateY(-1px);
    }

    /* Modal Styles */
    .address-modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 1000;
        display: none; /* hidden by default */
        align-items: center;
        justify-content: center;
    }
    .address-modal-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(3px);
    }
    .address-modal-content {
        background: #fff;
        width: 90%;
        max-width: 600px;
        border-radius: 12px;
        z-index: 1001;
        position: relative;
        box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        max-height: 90vh;
        overflow-y: auto;
    }
    .address-modal-header {
        padding: 20px 25px;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .address-modal-header h3 {
        margin: 0;
        font-size: 18px;
    }
    .address-modal-close {
        background: none;
        border: none;
        font-size: 24px;
        cursor: pointer;
        color: var(--text-light);
    }
    .address-modal-body {
        padding: 25px;
    }

    @media (max-width: 991px) {
        .address-book-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 15px;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--border-color);
        }
        .address-card {
            padding: 15px;
        }
        .address-actions {
            flex-wrap: wrap;
        }
    }
</style>
@endpush

@section('content')
<div class="container" style="padding-top: 20px;">
    <!-- Breadcrumb -->
    <div class="shop-breadcrumb" style="margin-bottom: 30px;">
        <a href="{{ url('/') }}">Beranda</a> <span class="separator"><i class="fas fa-chevron-right"></i></span> 
        <a href="{{ url('/profile') }}">Akun Saya</a> <span class="separator"><i class="fas fa-chevron-right"></i></span> 
        <span>Buku Alamat</span>
    </div>

    <div class="account-container">
        <!-- Load Sidebar -->
        @include('profile.sidebar')

        <!-- Main Account Content -->
        <div class="account-content">
            <div class="address-book-header">
                <h1 class="shop-page-title" style="margin: 0; border: none; padding: 0;">Buku Alamat</h1>
                <button class="btn-primary" onclick="openAddModal()">+ Tambah Alamat Baru</button>
            </div>

            @if(session('success'))
            <div style="background: #e8f5e9; color: #2e7d32; padding: 15px; border-radius: 6px; margin-bottom: 20px; font-weight: 500;">
                {{ session('success') }}
            </div>
            @endif
            
            <div class="address-list">
                @forelse($addresses as $address)
                <div class="address-card {{ $address->is_main ? 'is-main' : '' }}">
                    <span class="address-label {{ $address->is_main ? 'main-label' : '' }}">
                        {{ $address->label }} {{ $address->is_main ? '(Utama)' : '' }}
                    </span>
                    <div class="address-name">{{ $address->recipient_name }}</div>
                    <div class="address-phone">{{ $address->phone_number }}</div>
                    <div class="address-detail">
                        {{ $address->full_address }}<br>
                        {{ $address->district_id }}, {{ $address->city_id }} {{ $address->postal_code }}
                    </div>
                    <div class="address-actions">
                        <button class="btn-action-link" onclick="openEditModal({{ $address->toJson() }})">Ubah Alamat</button>
                        
                        <form action="{{ url('/addresses/' . $address->id) }}" method="POST" onsubmit="return confirm('Hapus alamat ini?');" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-action-link" style="color: #d32f2f;">Hapus</button>
                        </form>

                        @if(!$address->is_main)
                        <form action="{{ url('/addresses/' . $address->id . '/set-main') }}" method="POST" style="margin-left: auto;">
                            @csrf
                            @method('PUT')
                            <button type="submit" class="btn-set-main">Jadikan Utama</button>
                        </form>
                        @endif
                    </div>
                </div>
                @empty
                <div style="text-align: center; padding: 40px; background: #fafafa; border-radius: 12px; border: 1px dashed #ddd;">
                    <i class="far fa-map" style="font-size: 40px; color: #ccc; margin-bottom: 15px;"></i>
                    <h3 style="margin-bottom: 10px; color: #555;">Belum ada alamat tersimpan</h3>
                    <p style="color: #777; margin-bottom: 20px;">Tambahkan alamat untuk mempermudah proses penyewaan.</p>
                    <button class="btn-primary" onclick="openAddModal()">+ Tambah Alamat Baru</button>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Address Form Modal -->
<div id="addressModal" class="address-modal" style="display: none;">
    <div class="address-modal-overlay" onclick="closeModal()"></div>
    <div class="address-modal-content">
        <div class="address-modal-header">
            <h3 id="modalTitle">Tambah Alamat Baru</h3>
            <button class="address-modal-close" onclick="closeModal()">&times;</button>
        </div>
        <div class="address-modal-body">
            <form id="addressForm" action="{{ url('/addresses') }}" method="POST">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label style="display: block; margin-bottom: 5px; font-size: 13px;">Label Alamat</label>
                        <input type="text" name="label" id="fieldLabel" class="form-control" placeholder="Contoh: Rumah/Kantor" required>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label style="display: block; margin-bottom: 5px; font-size: 13px;">Nama Penerima</label>
                        <input type="text" name="recipient_name" id="fieldName" class="form-control" placeholder="Nama Lengkap" required>
                    </div>
                </div>
                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-size: 13px;">Nomor Telepon</label>
                    <input type="text" name="phone_number" id="fieldPhone" class="form-control" placeholder="Mulai dengan 08..." required>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label style="display: block; margin-bottom: 5px; font-size: 13px;">Kota/Kab.</label>
                        <input type="text" name="city_id" id="fieldCity" class="form-control" placeholder="Kota" required>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label style="display: block; margin-bottom: 5px; font-size: 13px;">Kecamatan</label>
                        <input type="text" name="district_id" id="fieldDistrict" class="form-control" placeholder="Kecamatan" required>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label style="display: block; margin-bottom: 5px; font-size: 13px;">Kode Pos</label>
                        <input type="text" name="postal_code" id="fieldPostal" class="form-control" placeholder="Kode Pos" required>
                    </div>
                </div>
                <div class="form-group" style="margin-bottom: 25px;">
                    <label style="display: block; margin-bottom: 5px; font-size: 13px;">Detail Alamat Lengkap (Jalan, No. Rumah, RT/RW)</label>
                    <textarea name="full_address" id="fieldAddress" class="form-control" rows="3" required></textarea>
                </div>
                
                <div style="display: flex; gap: 10px; margin-bottom: 15px; align-items: center;">
                    <input type="checkbox" name="is_main" id="fieldMain" value="1">
                    <label for="fieldMain" style="cursor: pointer; font-size: 14px; user-select: none;">Jadikan sebagai alamat utama</label>
                </div>

                <button type="submit" class="btn-primary" style="width: 100%;">Simpan Alamat</button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openAddModal() {
        document.getElementById('modalTitle').innerText = 'Tambah Alamat Baru';
        document.getElementById('addressForm').action = "{{ url('/addresses') }}";
        document.getElementById('formMethod').value = 'POST';
        
        // Reset fields
        document.getElementById('fieldLabel').value = '';
        document.getElementById('fieldName').value = '';
        document.getElementById('fieldPhone').value = '';
        document.getElementById('fieldCity').value = '';
        document.getElementById('fieldDistrict').value = '';
        document.getElementById('fieldPostal').value = '';
        document.getElementById('fieldAddress').value = '';
        document.getElementById('fieldMain').checked = false;

        document.getElementById('addressModal').style.display = 'flex';
    }

    function openEditModal(address) {
        document.getElementById('modalTitle').innerText = 'Ubah Alamat';
        document.getElementById('addressForm').action = "{{ url('/addresses') }}/" + address.id;
        document.getElementById('formMethod').value = 'PUT';
        
        // Fill fields
        document.getElementById('fieldLabel').value = address.label;
        document.getElementById('fieldName').value = address.recipient_name;
        document.getElementById('fieldPhone').value = address.phone_number;
        document.getElementById('fieldCity').value = address.city_id;
        document.getElementById('fieldDistrict').value = address.district_id;
        document.getElementById('fieldPostal').value = address.postal_code;
        document.getElementById('fieldAddress').value = address.full_address;
        document.getElementById('fieldMain').checked = address.is_main == 1;

        document.getElementById('addressModal').style.display = 'flex';
    }

    function closeModal() {
        document.getElementById('addressModal').style.display = 'none';
    }
</script>
@endpush
