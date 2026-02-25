@extends('layouts.admin')

@section('title', 'Kelola Kategori')

@section('header-left-extra')
<a href="{{ route('admin.products.index') }}" class="icon-btn" style="margin-right:15px; color:var(--text-color); font-size:1rem; gap:5px;">
    <i class="ti ti-arrow-left"></i> Kembali ke Produk
</a>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    .category-container { margin-top:20px; background:var(--white); border-radius:16px; padding:24px; box-shadow:0 4px 6px -1px rgba(0,0,0,0.05); }
    .cat-item { background:var(--white); border:1px solid var(--border-color); border-radius:8px; margin-bottom:10px; padding:12px 16px; display:flex; align-items:center; justify-content:space-between; transition:box-shadow 0.2s; }
    .cat-item:hover { box-shadow:0 2px 8px rgba(0,0,0,0.05); }
    .cat-item.child { margin-left:40px; border-left:4px solid var(--primary-color); }
    .cat-content-left { display:flex; align-items:center; gap:15px; }
    .cat-icon { width:40px; height:40px; background-color:#eff6ff; color:#3b82f6; border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:1.2rem; }
    .cat-info { display:flex; flex-direction:column; }
    .cat-title { font-weight:600; font-size:1rem; color:var(--text-color); }
    .cat-meta { font-size:0.8rem; color:var(--text-light); }
    .cat-actions { display:flex; gap:8px; }
    .btn-icon { background:none; border:1px solid var(--border-color); width:32px; height:32px; border-radius:6px; display:flex; align-items:center; justify-content:center; color:var(--text-light); cursor:pointer; transition:0.2s; }
    .btn-icon:hover { background-color:var(--bg-color); color:var(--primary-color); }
    .btn-icon.delete:hover { border-color:#fca5a5; background-color:#fef2f2; color:var(--danger); }
    .categories-layout { display:grid; grid-template-columns:5fr 7fr; gap:24px; align-items:start; }
    @media (max-width:992px) { .categories-layout { grid-template-columns:1fr; } }
    .form-group { margin-bottom:15px; }
    .form-label { display:block; margin-bottom:8px; font-weight:500; }
    .form-control { width:100%; padding:10px; border-radius:8px; font-family:inherit; border:1px solid var(--border-color); }
    .form-control:focus { outline:none; border-color:var(--primary-color); }

    /* Edit Modal */
    .modal-overlay { display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); z-index:200; align-items:center; justify-content:center; }
    .modal-overlay.show { display:flex; }
    .modal-box { background:var(--white); border-radius:12px; padding:24px; width:100%; max-width:400px; box-shadow:0 20px 60px rgba(0,0,0,0.15); }

    /* Icon Picker Styles */
    .icon-picker-wrapper { position: relative; }
    .icon-picker-btn { display: flex; align-items: center; gap: 10px; padding: 10px 15px; border: 1px solid var(--border-color); border-radius: 8px; background: var(--white); cursor: pointer; font-family: inherit; font-size: 0.95rem; color: var(--text-color); width: 100%; text-align: left; transition: 0.2s; }
    .icon-picker-btn:hover { border-color: var(--primary-color); }
    .icon-picker-btn i.selected-icon { font-size: 1.4rem; color: var(--primary-color); width: 25px; text-align:center; }
    .icon-dropdown { position: absolute; top: 100%; left: 0; width: 100%; background: var(--white); border: 1px solid var(--border-color); border-radius: 8px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); margin-top: 5px; z-index: 10; display: none; padding: 15px; }
    .icon-dropdown.show { display: block; }
    .icon-search { width: 100%; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 6px; margin-bottom: 10px; outline: none; font-family: inherit; }
    .icon-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(40px, 1fr)); gap: 8px; max-height: 200px; overflow-y: auto; }
    .icon-option { width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; border: 1px solid transparent; border-radius: 6px; cursor: pointer; font-size: 1.4rem; color: var(--text-light); }
    .icon-option:hover, .icon-option.active { background: #eff6ff; color: #3b82f6; border-color: #bfdbfe; }
</style>
@endpush

@section('content')
<div class="page-header">
    <h1>Kelola Kategori</h1>
    <p>Susun hirarki kategori produk sewa, beserta visual Ikonnya.</p>
</div>

<div class="categories-layout">
    <!-- Left side: Form -->
    <div class="category-container" style="margin-top:0">
        <h3 style="margin-bottom:20px; font-size:1.2rem;">Buat Kategori Baru</h3>
        <form action="{{ route('admin.categories.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label class="form-label">Nama Kategori</label>
                <input type="text" name="name" class="form-control" placeholder="Contoh: Sound System" required>
                @error('name') <small style="color:var(--danger);">{{ $message }}</small> @enderror
            </div>

            <div class="form-group" style="margin-top:15px">
                <label class="form-label">Induk Kategori (Opsional)</label>
                <select name="parent_id" class="form-control">
                    <option value="">-- Jadikan Kategori Utama --</option>
                    @foreach($allCategories as $catOption)
                        <option value="{{ $catOption->id }}">{{ $catOption->name }}</option>
                    @endforeach
                </select>
                @error('parent_id') <small style="color:var(--danger);">{{ $message }}</small> @enderror
            </div>

            <div class="form-group" style="margin-top:15px; margin-bottom:25px">
                <label class="form-label">Pilih Ikon</label>
                <div class="icon-picker-wrapper">
                    <button type="button" class="icon-picker-btn" id="iconPickerBtn">
                        <i class="fas fa-box selected-icon" id="currentIcon"></i>
                        <span id="currentIconName">fas fa-box</span>
                    </button>
                    <input type="hidden" name="icon" id="selectedIconInput" value="fas fa-box">

                    <div class="icon-dropdown" id="iconDropdown">
                        <input type="text" class="icon-search" id="iconSearch" placeholder="Cari ikon...">
                        <div class="icon-grid" id="iconGrid">
                            <!-- Icons injected dynamically -->
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center; background-color:var(--primary-color); color:var(--white); border:none; padding:10px 20px; border-radius:8px; font-weight:500; cursor:pointer; display:inline-flex; align-items:center; gap:8px;">
                <i class="ti ti-device-floppy"></i> Simpan Kategori
            </button>
        </form>
    </div>

    <!-- Right side: List -->
    <div class="category-container" style="margin-top:0">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
            <h3 style="font-size:1.2rem; margin:0;">Susunan Kategori</h3>
            <span style="font-size:0.85rem; color:var(--text-light);">{{ $categories->count() }} kategori Induk</span>
        </div>

        @forelse($categories as $category)
        <div class="cat-item">
            <div class="cat-content-left">
                <div class="cat-icon">
                    <i class="{{ $category->icon ?? 'fas fa-box' }}"></i>
                </div>
                <div class="cat-info">
                    <span class="cat-title">{{ $category->name }}</span>
                    <span class="cat-meta">Produk: {{ $category->products_count ?? 0 }} | Sub-Kategori: {{ $category->children->count() }}</span>
                </div>
            </div>
            <div class="cat-actions">
                <button class="btn-icon" onclick="openEditModal({{ $category->id }}, '{{ addslashes($category->name) }}', '{{ $category->parent_id }}', '{{ $category->icon }}')" title="Edit">
                    <i class="ti ti-edit"></i>
                </button>
                @if(($category->products_count ?? 0) == 0 && $category->children->count() == 0)
                <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Kategori akan dihapus. Yakin?');">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn-icon delete" title="Hapus"><i class="ti ti-trash"></i></button>
                </form>
                @endif
            </div>
        </div>
            
            @foreach($category->children as $child)
            <div class="cat-item child">
                <div class="cat-content-left">
                    <div class="cat-icon" style="width:30px; height:30px; font-size:0.9rem;">
                        <i class="{{ $child->icon ?? 'fas fa-arrow-right' }}"></i>
                    </div>
                    <div class="cat-info">
                        <span class="cat-title" style="font-size:0.95rem;">{{ $child->name }}</span>
                        <span class="cat-meta">Produk: {{ $child->products_count ?? 0 }}</span>
                    </div>
                </div>
                <div class="cat-actions">
                    <button class="btn-icon" onclick="openEditModal({{ $child->id }}, '{{ addslashes($child->name) }}', '{{ $child->parent_id }}', '{{ $child->icon }}')" title="Edit">
                        <i class="ti ti-edit"></i>
                    </button>
                    @if(($child->products_count ?? 0) == 0)
                    <form action="{{ route('admin.categories.destroy', $child->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Kategori akan dihapus. Yakin?');">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn-icon delete" title="Hapus"><i class="ti ti-trash"></i></button>
                    </form>
                    @endif
                </div>
            </div>
            @endforeach

        @empty
        <div style="text-align:center; padding:40px; color:var(--text-light);">
            <i class="ti ti-category" style="font-size:2rem; display:block; margin-bottom:10px; opacity:0.3;"></i>
            Belum ada kategori. Buat kategori pertama di form sebelah kiri.
        </div>
        @endforelse
    </div>
</div>

<!-- Edit Modal -->
<div class="modal-overlay" id="editModal">
    <div class="modal-box">
        <h3 style="margin-bottom:20px;">Edit Kategori</h3>
        <form id="editForm" method="POST">
            @csrf @method('PUT')
            <div class="form-group">
                <label class="form-label">Nama Kategori</label>
                <input type="text" name="name" id="editCategoryName" class="form-control" required>
            </div>
            
            <div class="form-group" style="margin-top:15px">
                <label class="form-label">Induk Kategori</label>
                <select name="parent_id" id="editCategoryParent" class="form-control">
                    <option value="">-- Kategori Utama --</option>
                    @foreach($allCategories as $catOption)
                        <option value="{{ $catOption->id }}">{{ $catOption->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group" style="margin-top:15px;">
                <label class="form-label">Referensi Ikon</label>
                <input type="text" name="icon" id="editCategoryIcon" class="form-control" placeholder="fas fa-box">
            </div>

            <div style="display:flex; gap:10px; justify-content:flex-end; margin-top:20px;">
                <button type="button" onclick="closeEditModal()" style="padding:10px 20px; border-radius:8px; border:1px solid var(--border-color); background:var(--white); cursor:pointer; font-family:inherit;">Batal</button>
                <button type="submit" style="padding:10px 20px; border-radius:8px; border:none; background:var(--primary-color); color:var(--white); cursor:pointer; font-weight:500; font-family:inherit;">
                    <i class="ti ti-check"></i> Simpan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Icon Picker Logic
const faIcons = [
    'fas fa-volume-up', 'fas fa-lightbulb', 'fas fa-layer-group', 'fas fa-desktop',
    'fas fa-video', 'fas fa-wifi', 'fas fa-camera', 'fas fa-campground',
    'fas fa-chair', 'fas fa-table', 'fas fa-couch', 'fas fa-store',
    'fas fa-charging-station', 'fas fa-snowflake', 'fas fa-cloud', 'fas fa-sign',
    'fas fa-ellipsis-h', 'fas fa-cubes', 'fas fa-box-open', 'fas fa-box',
    'fas fa-microphone', 'fas fa-headphones', 'fas fa-tv', 'fas fa-broadcast-tower',
    'fas fa-film', 'fas fa-plug', 'fas fa-battery-full', 'fas fa-bolt',
    'fas fa-asterisk', 'fas fa-compact-disc', 'fas fa-gamepad'
];

const iconGrid = document.getElementById('iconGrid');
const iconInput = document.getElementById('selectedIconInput');
const currentIconDisp = document.getElementById('currentIcon');
const currentIconName = document.getElementById('currentIconName');
const iconSearch = document.getElementById('iconSearch');
const iconDropdown = document.getElementById('iconDropdown');
const iconPickerBtn = document.getElementById('iconPickerBtn');

function renderIcons(search = '') {
    iconGrid.innerHTML = '';
    const filteredIcons = faIcons.filter(icon => icon.includes(search.toLowerCase()));
    
    filteredIcons.forEach(iconClass => {
        const div = document.createElement('div');
        div.className = 'icon-option' + (iconInput.value === iconClass ? ' active' : '');
        div.innerHTML = `<i class="${iconClass}"></i>`;
        div.onclick = () => selectIcon(iconClass);
        iconGrid.appendChild(div);
    });
}

function selectIcon(iconClass) {
    iconInput.value = iconClass;
    currentIconDisp.className = iconClass + ' selected-icon';
    currentIconName.textContent = iconClass;
    iconDropdown.classList.remove('show');
    renderIcons(); // Update active state
}

iconPickerBtn.addEventListener('click', () => {
    iconDropdown.classList.toggle('show');
    if(iconDropdown.classList.contains('show')) {
        iconSearch.focus();
    }
});

document.addEventListener('click', (e) => {
    if(!e.target.closest('.icon-picker-wrapper')) {
        iconDropdown.classList.remove('show');
    }
});

iconSearch.addEventListener('input', (e) => {
    renderIcons(e.target.value);
});

// Initialize icons
renderIcons();

// Modal Logic
function openEditModal(id, name, parent_id, icon) {
    document.getElementById('editModal').classList.add('show');
    document.getElementById('editCategoryName').value = name;
    document.getElementById('editCategoryParent').value = parent_id || '';
    document.getElementById('editCategoryIcon').value = icon || '';
    document.getElementById('editForm').action = '{{ url("wp-admin/categories") }}/' + id;
}
function closeEditModal() {
    document.getElementById('editModal').classList.remove('show');
}
document.getElementById('editModal').addEventListener('click', function(e) {
    if (e.target === this) closeEditModal();
});
</script>
@endpush
