@extends('layouts.admin')

@section('title', 'Kelola Pengguna')

@push('styles')
<style>
    .product-list-card { background-color:var(--white); border-radius:16px; box-shadow:0 4px 6px -1px rgba(0,0,0,0.05); padding:24px; }
    .btn-primary { background-color:var(--primary-color); color:var(--white); border:none; padding:10px 20px; border-radius:8px; font-weight:500; cursor:pointer; display:inline-flex; align-items:center; gap:8px; text-decoration:none; transition:background-color 0.2s; }
    .btn-primary:hover { background-color:#b71c1c; }
    .table-user { display:flex; align-items:center; gap:12px; }
    .table-user img { width:36px; height:36px; border-radius:50%; object-fit:cover; }
    .role-badge { padding:4px 10px; border-radius:12px; font-size:0.75rem; font-weight:600; }
    .role-badge.superadmin { background-color:#f3e8ff; color:#a855f7; }
    .role-badge.staff { background-color:#dbeafe; color:#2563eb; }
    .role-badge.customer { background-color:#d1fae5; color:#059669; }
</style>
@endpush

@section('content')
<div class="page-header">
    <h1>Kelola Pengguna</h1>
    <p>Daftar seluruh pengguna, staf, dan pelanggan Renta.</p>
</div>

<div class="product-list-card">
    <div class="table-header-actions" style="margin-bottom:20px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:15px;">
        <div class="filter-toolbar" style="display:flex; gap:10px;">
            <form method="GET" action="{{ route('admin.users.index') }}" style="display:flex; gap:10px;">
                <select name="role" onchange="this.form.submit()" style="padding:8px 16px; border:1px solid var(--border-color); border-radius:6px; font-family:inherit; font-size:0.9rem; background:var(--white);">
                    <option value="">Semua Peran</option>
                    <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Super Admin</option>
                    <option value="customer" {{ request('role') == 'customer' ? 'selected' : '' }}>Pelanggan</option>
                </select>
            </form>
            @if(request('role'))
            <a href="{{ route('admin.users.index') }}" style="background-color:#f8fafc; border:1px solid #e2e8f0; color:#475569; padding:8px 16px; border-radius:6px; font-weight:500; display:inline-flex; align-items:center; gap:8px; text-decoration:none; font-size:0.9rem;">
                <i class="ti ti-refresh"></i> Reset
            </a>
            @endif
        </div>
        <div class="search-control">
            <input type="text" id="customSearch" placeholder="Cari pengguna..." style="background:#f8fafc; border:1px solid #e2e8f0; width:250px; padding:8px 16px; border-radius:6px; outline:none; font-family:inherit;">
        </div>
    </div>
    <div class="table-responsive">
        <table class="admin-table" id="usersTable" style="width:100%">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Pengguna</th>
                    <th>Telepon / WA</th>
                    <th>Peran</th>
                    <th>Terdaftar</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $i => $user)
                <tr>
                    <td>{{ $users->firstItem() + $i }}</td>
                    <td>
                        <div class="table-user">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background={{ $user->role === 'admin' ? 'D32F2F&color=fff' : 'f0e68c' }}" alt="{{ $user->name }}">
                            <div>
                                <div style="font-weight:600;">{{ $user->name }}</div>
                                <div style="font-size:0.8rem; color:var(--text-light);">{{ $user->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td>{{ $user->phone_number ?? '-' }}</td>
                    <td>
                        @if($user->role === 'admin')
                        <span class="role-badge superadmin">Super Admin</span>
                        @else
                        <span class="role-badge customer">Pelanggan</span>
                        @endif
                    </td>
                    <td>{{ $user->created_at->format('d M Y') }}</td>
                    <td>
                        @if($user->role !== 'admin')
                        <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Pengguna ini akan dihapus permanen. Yakin?');">
                            @csrf @method('DELETE')
                            <button type="submit" class="action-btn delete" title="Hapus"><i class="ti ti-trash"></i></button>
                        </form>
                        @else
                        <span style="color:var(--text-light); font-size:0.8rem;">—</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align:center; padding:40px; color:var(--text-light);">
                        <i class="ti ti-users" style="font-size:2rem; display:block; margin-bottom:10px; opacity:0.3;"></i>
                        Belum ada pengguna terdaftar.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($users->hasPages())
    <div style="padding:15px 0; border-top:1px solid var(--border-color); margin-top:15px;">
        {{ $users->appends(request()->query())->links('pagination::bootstrap-4') }}
    </div>
    @endif
</div>
@endsection
