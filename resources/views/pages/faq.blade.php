@extends('layouts.app')

@section('title', 'Pusat Bantuan & FAQ | Renta Enterprise')

@push('styles')
<style>
    .faq-hero {
        background: linear-gradient(135deg, var(--primary-color), #f76b6b);
        padding: 80px 20px;
        text-align: center;
        color: #fff;
        margin-bottom: 50px;
    }

    .faq-hero h1 {
        color: #fff;
        font-size: 36px;
        margin-bottom: 15px;
    }

    .faq-hero p {
        font-size: 18px;
        opacity: 0.9;
        max-width: 600px;
        margin: 0 auto;
    }

    .faq-search {
        max-width: 600px;
        margin: 30px auto 0;
        position: relative;
    }

    .faq-search input {
        width: 100%;
        padding: 15px 25px;
        padding-right: 50px;
        border-radius: 30px;
        border: none;
        font-family: inherit;
        font-size: 16px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        outline: none;
    }

    .faq-search button {
        position: absolute;
        right: 5px;
        top: 5px;
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background: var(--primary-color);
        border: none;
        color: #fff;
        cursor: pointer;
        transition: var(--transition);
    }

    .faq-search button:hover {
        background: #b02323;
    }

    .faq-container {
        max-width: 800px;
        margin: 0 auto;
    }

    .faq-category-title {
        text-align: center;
        font-size: 24px;
        margin-bottom: 30px;
        color: var(--text-dark);
        position: relative;
        padding-bottom: 10px;
    }

    .faq-category-title::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 60px;
        height: 3px;
        background: var(--primary-color);
        border-radius: 2px;
    }

    .accordion-item {
        background: #fff;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        margin-bottom: 15px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0,0,0,0.02);
        transition: var(--transition);
    }

    .accordion-item:hover {
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        border-color: #eaeaea;
    }

    .accordion-header {
        padding: 20px 25px;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #fff;
        transition: var(--transition);
    }

    .accordion-header h4 {
        margin: 0;
        font-size: 16px;
        color: var(--text-dark);
        font-weight: 600;
    }

    .accordion-icon {
        color: var(--primary-color);
        transition: transform 0.3s ease;
    }

    .accordion-content {
        padding: 0 25px;
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.3s ease, padding 0.3s ease;
        background: #fafafa;
        border-top: 1px solid transparent;
    }

    .accordion-content p {
        margin: 0;
        padding: 20px 0;
        color: var(--text-light);
        line-height: 1.6;
        font-size: 15px;
    }

    /* Active State */
    .accordion-item.active .accordion-header {
        background: #fafafa;
    }

    .accordion-item.active .accordion-icon {
        transform: rotate(180deg);
    }

    .accordion-item.active .accordion-content {
        border-top-color: var(--border-color);
    }

    .contact-support {
        text-align: center;
        margin-top: 60px;
        padding: 40px 20px;
        background: #fdfdfd;
        border: 1px solid var(--border-color);
        border-radius: 12px;
    }

    .contact-support h3 {
        font-size: 20px;
        margin-bottom: 10px;
    }

    .contact-support p {
        color: var(--text-light);
        margin-bottom: 20px;
    }

    @media (max-width: 768px) {
        .faq-hero {
            padding: 50px 20px;
        }
        .faq-hero h1 {
            font-size: 28px;
        }
        .accordion-header {
            padding: 15px 20px;
        }
        .accordion-header h4 {
            font-size: 15px;
        }
    }
</style>
@endpush

@section('content')
<!-- Hero Section -->
<section class="faq-hero">
    <div class="container">
        <h1>Pusat Bantuan Pelanggan</h1>
        <p>Temukan jawaban untuk semua pertanyaan Anda terkait prosedur penyewaan, pembayaran, dan syarat ketentuan di Renta Enterprise.</p>
        
        <div class="faq-search">
            <input type="text" placeholder="Ketik kata kunci (contoh: deposit, telat, rusak)...">
            <button type="button"><i class="fas fa-search"></i></button>
        </div>
    </div>
</section>

<!-- Main Content -->
<div style="padding-bottom: 70px;">
    <div class="container faq-container">
        
        <h2 class="faq-category-title">Prosedur & Aturan Sewa</h2>

        <div class="accordion-item">
            <div class="accordion-header">
                <h4>Bagaimana cara menyewa alat di Renta Enterprise?</h4>
                <i class="fas fa-chevron-down accordion-icon"></i>
            </div>
            <div class="accordion-content">
                <p>Cukup pilih produk yang Anda butuhkan melalui website, tambahkan ke keranjang, dan lakukan proses checkout. Tim kami akan menghubungi Anda via WhatsApp untuk memverifikasi ketersediaan jadwal serta memberikan instruksi penyerahan jaminan identitas sebelum pesanan Anda kami proses untuk disiapkan.</p>
            </div>
        </div>

        <div class="accordion-item">
            <div class="accordion-header">
                <h4>Apa syarat yang harus saya serahkan sebagai jaminan?</h4>
                <i class="fas fa-chevron-down accordion-icon"></i>
            </div>
            <div class="accordion-content">
                <p>Sebagai syarat keamanan, Anda wajib menitipkan <strong>1 Kartu Identitas Asli yang masih berlaku</strong> (E-KTP / SIM / Paspor) yang akan kami simpan dengan aman selama masa sewa berlangsung. Identitas yang ditinggalkan harus atas nama orang yang sama dengan akun pemesan / yang mengambil alat.</p>
            </div>
        </div>

        <div class="accordion-item">
            <div class="accordion-header">
                <h4>Bagaimana jika saya mengembalikan alat terlambat dari waktu yang disepakati?</h4>
                <i class="fas fa-chevron-down accordion-icon"></i>
            </div>
            <div class="accordion-content">
                <p>Keterlambatan pengembalian unit alat akan dikenakan denda keterlambatan (overcharge) yang perhitungannya adalah: keterlambatan lebih dari 2 jam hingga 12 jam dikenakan denda 50% dari harga sewa harian. Keterlambatan lebih dari 12 jam dihitung perpanjangan masa sewa 1 hari penuh.</p>
            </div>
        </div>

        <div class="accordion-item">
            <div class="accordion-header">
                <h4>Apa yang terjadi jika barang yang saya sewa cacat atau rusak?</h4>
                <i class="fas fa-chevron-down accordion-icon"></i>
            </div>
            <div class="accordion-content">
                <p>Segala bentuk kehilangan, kerusakan fisik, atau cacat fungsional yang terjadi selama alat berada di tangan penyewa sepenuhnya menjadi tanggung jawab penyewa. Anda akan dikenakan biaya perbaikan servis resmi atau penggantian unit baru apabila alat hilang sesuai nominal harga barang terkini di pasaran.</p>
            </div>
        </div>

        <br><br>
        <h2 class="faq-category-title">Pembayaran & Pengiriman</h2>

        <div class="accordion-item">
            <div class="accordion-header">
                <h4>Apakah sewa alat berat seperti LED / Sound sudah termasuk operasional teknisi?</h4>
                <i class="fas fa-chevron-down accordion-icon"></i>
            </div>
            <div class="accordion-content">
                <p>Ya, untuk unit skala menengah-besar tertentu seperti paket <em>Sound System / Rigging Stage / Videotron (LED Wall)</em>, harga sewa yang tertera sudah termasuk jasa 1-2 orang operator teknisi. Namun, hal ini belum termasuk biaya tambahan kuli angkut (porter) jika lokasi bongkar muat sulit dijangkau.</p>
            </div>
        </div>

        <div class="accordion-item">
            <div class="accordion-header">
                <h4>Bisakah sistem pembayaran diangsur atau dibayar sebagian (DP)?</h4>
                <i class="fas fa-chevron-down accordion-icon"></i>
            </div>
            <div class="accordion-content">
                <p>Untuk total transaksi di atas Rp 2.000.000, Anda berhak membayarkan <em>Down Payment</em> (DP) minimal 50% terlebih dahulu untuk mem-booking alat. Sisa pembayaran wajib diselesaikan selambat-lambatnya 1 hari (H-1) sebelum alat digunakan atau diantarkan ke tempat Anda.</p>
            </div>
        </div>

        <div class="accordion-item">
            <div class="accordion-header">
                <h4>Apakah melayani antar-jemput barang sewaan?</h4>
                <i class="fas fa-chevron-down accordion-icon"></i>
            </div>
            <div class="accordion-content">
                <p>Tentu, kami menyediakan armada mobil logistik. Untuk penyewaan di wilayah Kota Malang khusus pembelian di atas Rp 500.000, biaya ongkir kami yang tanggung (Gratis). Selebihnya atau untuk pengiriman luar kota/kabupaten, akan dikenakan biaya ongkos transportasi sesuai jarak kilometer (mulai dari Rp 50.000 - 300.000).</p>
            </div>
        </div>

        <!-- Block Hubungi Support -->
        <div class="contact-support">
            <h3>Pertanyaan Anda Belum Terjawab?</h3>
            <p>Tim dukungan kami siap mendengarkan kebutuhan dan keluhan Anda 24/7. <br>Silakan layangkan pesan atau telepon melalui lini CS kami.</p>
            <a href="https://wa.me/6281234567890" target="_blank" class="btn-primary" style="padding: 12px 30px; font-size: 15px; text-decoration:none;"><i class="fab fa-whatsapp"></i> Hubungi WhatsApp (0812-3456-7890)</a>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const accordionHeaders = document.querySelectorAll('.accordion-header');

        accordionHeaders.forEach(header => {
            header.addEventListener('click', function() {
                const item = this.parentElement;
                const content = item.querySelector('.accordion-content');
                
                // Toggle current item
                if (item.classList.contains('active')) {
                    item.classList.remove('active');
                    content.style.maxHeight = null;
                    content.style.paddingTop = '0';
                    content.style.paddingBottom = '0';
                } else {
                    document.querySelectorAll('.accordion-item').forEach(otherItem => {
                        if (otherItem !== item) {
                            otherItem.classList.remove('active');
                            const otherContent = otherItem.querySelector('.accordion-content');
                            otherContent.style.maxHeight = null;
                            otherContent.style.paddingTop = '0';
                            otherContent.style.paddingBottom = '0';
                        }
                    });

                    item.classList.add('active');
                    content.style.maxHeight = content.scrollHeight + 40 + "px"; // 40px for padding
                    content.style.paddingTop = '0';
                    content.style.paddingBottom = '0';
                }
            });
        });
    });
</script>
@endpush
