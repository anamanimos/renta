@extends('layouts.admin')

@section('title', 'Log Aktivitas | Admin Renta Enterprise')

@section('header', 'Log Aktivitas Sistem')
@section('description', 'Pantau semua perubahan dan percobaan login yang terjadi di dalam panel admin.')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css"/>
<style>
    .layout-grid {
        display: grid;
        grid-template-columns: 250px 1fr;
        gap: 24px;
        align-items: start;
    }
    @media (max-width: 992px) {
        .layout-grid {
            grid-template-columns: 1fr;
        }
    }

    .settings-nav {
        background: var(--white);
        border-radius: 12px;
        padding: 10px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    }
    .settings-nav a {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 16px;
        color: var(--text-color);
        text-decoration: none;
        border-radius: 8px;
        font-weight: 500;
        transition: 0.2s;
    }
    .settings-nav a:hover,
    .settings-nav a.active {
        background-color: var(--bg-color);
        color: var(--primary-color);
    }
    .settings-nav a i {
        font-size: 1.2rem;
    }

    .card {
        background: var(--white);
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        margin-bottom: 24px;
    }

    .log-level {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 4px;
        font-weight: 600;
        font-size: 0.75rem;
    }
    .log-info { background: #e0f2fe; color: #0284c7; }
    .log-warn { background: #fefce8; color: #ca8a04; }
    .log-danger { background: #fee2e2; color: #dc2626; }
</style>
@endpush

@section('content')
<div class="layout-grid">
    <!-- Settings Sidebar -->
    <div class="settings-nav">
        <a href="{{ route('admin.settings.general') }}">
            <i class="ti ti-adjustments"></i> Umum
        </a>
        <a href="{{ route('admin.settings.whatsapp') }}">
            <i class="ti ti-brand-whatsapp"></i> Integrasi WhatsApp
        </a>
        <a href="{{ route('admin.settings.payment') }}">
            <i class="ti ti-credit-card"></i> Pembayaran
        </a>
        <a href="{{ route('admin.settings.logs') }}" class="active">
            <i class="ti ti-history"></i> Log Aktivitas
        </a>
    </div>

    <!-- Settings Content -->
    <div class="settings-content">
        <div class="card" style="padding: 24px">
            <div class="table-header-actions" style="margin-bottom: 20px; display: flex; justify-content: flex-end;">
                <div class="search-control">
                    <input type="text" id="customSearch" class="form-control" placeholder="Cari log..." style="padding: 8px 15px; border-radius: 6px; border: 1px solid #ddd; width: 250px;">
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover" id="logsTable" style="width: 100%;">
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>Pengguna</th>
                            <th>Aksi</th>
                            <th>Modul</th>
                            <th>IP Address</th>
                            <th>Level</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Dummy Data for presentation purposes -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
    $(document).ready(function () {
        const logsData = [
            {
                "waktu": "{{ now()->format('d M Y H:i:s') }}",
                "user": "System Admin",
                "aksi": "Mengubah Konfigurasi WhatsApp API Gateway",
                "modul": "Pengaturan",
                "ip": "127.0.0.1",
                "level": "<span class='log-level log-info'>INFO</span>"
            },
            {
                "waktu": "{{ now()->subMinutes(15)->format('d M Y H:i:s') }}",
                "user": "System Admin",
                "aksi": "Menghapus Rekening Bank Mandiri",
                "modul": "Pembayaran",
                "ip": "127.0.0.1",
                "level": "<span class='log-level log-warn'>WARNING</span>"
            },
            {
                "waktu": "{{ now()->subDays(1)->format('d M Y H:i:s') }}",
                "user": "System",
                "aksi": "Gagal Verifikasi Midtrans Webhook Notification",
                "modul": "Integrasi",
                "ip": "localhost",
                "level": "<span class='log-level log-danger'>CRITICAL</span>"
            },
            {
                "waktu": "{{ now()->subDays(2)->format('d M Y H:i:s') }}",
                "user": "Siti (Staf)",
                "aksi": "Menambahkan Kupon 'RAMADHAN25'",
                "modul": "Kupon",
                "ip": "192.168.1.5",
                "level": "<span class='log-level log-info'>INFO</span>"
            }
        ];

        const table = $("#logsTable").DataTable({
            dom: 'rt<"pagination-container"p>',
            data: logsData,
            lengthChange: false,
            pageLength: 10,
            order: [[0, "desc"]],
            columns: [
                { data: "waktu" },
                { data: "user" },
                { data: "aksi" },
                { data: "modul" },
                { data: "ip" },
                { data: "level" }
            ],
            language: {
                search: "Cari Log:",
                lengthMenu: "Tampil _MENU_ baris",
                info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ logs",
                paginate: {
                    previous: '<i class="fas fa-chevron-left"></i>',
                    next: '<i class="fas fa-chevron-right"></i>'
                }
            }
        });

        $("#customSearch").on("keyup", function () {
            table.search(this.value).draw();
        });
    });
</script>
@endpush
