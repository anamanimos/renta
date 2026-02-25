/* admin.js - Admin Dashboard Script */

document.addEventListener("DOMContentLoaded", () => {
  // Sidebar Toggle Logic
  const menuToggle = document.getElementById("menuToggle");
  const closeSidebar = document.getElementById("closeSidebar");
  const sidebar = document.getElementById("sidebar");
  const overlay = document.getElementById("sidebarOverlay");

  if (menuToggle) {
    menuToggle.addEventListener("click", () => {
      sidebar.classList.add("show");
      overlay.classList.add("show");
    });
  }

  if (closeSidebar) {
    closeSidebar.addEventListener("click", () => {
      sidebar.classList.remove("show");
      overlay.classList.remove("show");
    });
  }

  if (overlay) {
    overlay.addEventListener("click", () => {
      sidebar.classList.remove("show");
      overlay.classList.remove("show");
    });
  }

  // Logout Confirmation
  const logoutBtn = document.getElementById("logoutBtn");
  if (logoutBtn) {
    logoutBtn.addEventListener("click", (e) => {
      e.preventDefault();
      Swal.fire({
        title: "Konfirmasi Logout",
        text: "Apakah Anda yakin ingin keluar?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#ef4444",
        cancelButtonColor: "#64748b",
        confirmButtonText: "Ya, Keluar!",
        cancelButtonText: "Batal",
      }).then((result) => {
        if (result.isConfirmed) {
          Swal.fire({
            title: "Berhasil",
            text: "Anda telah berhasil logout.",
            icon: "success",
            timer: 1500,
            showConfirmButton: false,
          }).then(() => {
            window.location.href = "index.html"; // Redirect to home/login
          });
        }
      });
    });
  }

  // Scan Button (Mobile Bottom Nav)
  const scanBtn = document.querySelector(".scan-btn");
  if (scanBtn) {
    scanBtn.addEventListener("click", (e) => {
      e.preventDefault();
      Swal.fire({
        title: "Scan QR Code Pesanan",
        text: "Fitur kamera akan segera hadir di versi ini.",
        icon: "info",
        confirmButtonColor: "#D32F2F",
        confirmButtonText: "Mengerti",
      });
    });
  }

  // Store Mode Toggle Logic (Div-based)
  const modeToggles = document.querySelectorAll(".mode-toggle-btn");
  modeToggles.forEach((toggle) => {
    toggle.addEventListener("click", function () {
      const isKatalog = this.classList.contains("katalog-mode");
      const container = this.closest(".mode-switcher-container");
      const rentalLabel = container.querySelector(".mode-rental");
      const katalogLabel = container.querySelector(".mode-katalog");

      if (!isKatalog) {
        // Switch to Katalog Mode
        this.classList.add("katalog-mode");
        rentalLabel.classList.remove("active");
        rentalLabel.style.color = "var(--text-light)";
        katalogLabel.classList.add("active");
        katalogLabel.style.color = "var(--success)";

        // Sync other toggles if any
        modeToggles.forEach((t) => {
          if (t !== this) t.classList.add("katalog-mode");
        });

        // Show toast notification
        Swal.fire({
          toast: true,
          position: "top-end",
          icon: "success",
          title: "Beralih ke Mode Katalog",
          showConfirmButton: false,
          timer: 1500,
        });
      } else {
        // Switch to Rental Mode
        this.classList.remove("katalog-mode");
        katalogLabel.classList.remove("active");
        katalogLabel.style.color = "var(--text-light)";
        rentalLabel.classList.add("active");
        rentalLabel.style.color = "var(--primary-color)";

        // Sync other toggles if any
        modeToggles.forEach((t) => {
          if (t !== this) t.classList.remove("katalog-mode");
        });

        Swal.fire({
          toast: true,
          position: "top-end",
          icon: "info",
          title: "Beralih ke Mode Rental",
          showConfirmButton: false,
          timer: 1500,
        });
      }
    });
  });
});

// Order Action Functions using SweetAlert2
function approveOrder(orderId) {
  Swal.fire({
    title: `Terima Pesanan ${orderId}?`,
    text: "Pesanan akan masuk ke daftar aktif.",
    icon: "question",
    showCancelButton: true,
    confirmButtonColor: "#10b981",
    cancelButtonColor: "#64748b",
    confirmButtonText: "Ya, Terima!",
    cancelButtonText: "Batal",
  }).then((result) => {
    if (result.isConfirmed) {
      Swal.fire("Diterima!", "Pesanan berhasil dikonfirmasi.", "success");
      // Here you would add logic to update DOM or make API call
    }
  });
}

function rejectOrder(orderId) {
  Swal.fire({
    title: `Tolak Pesanan ${orderId}?`,
    html: `<p>Berikan alasan penolakan:</p><textarea id="rejectReason" class="swal2-textarea" placeholder="Alasan..."></textarea>`,
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#ef4444",
    cancelButtonColor: "#64748b",
    confirmButtonText: "Ya, Tolak",
    cancelButtonText: "Batal",
    preConfirm: () => {
      const reason = Swal.getPopup().querySelector("#rejectReason").value;
      if (!reason) {
        Swal.showValidationMessage("Alasan penolakan harus diisi");
      }
      return { reason: reason };
    },
  }).then((result) => {
    if (result.isConfirmed) {
      Swal.fire("Ditolak!", "Pesanan telah ditolak.", "error");
    }
  });
}

function completeOrder(orderId) {
  Swal.fire({
    title: `Selesaikan Pesanan ${orderId}?`,
    text: "Pastikan barang sewaan sudah kembali dan dicek.",
    icon: "info",
    showCancelButton: true,
    confirmButtonColor: "#8b5cf6",
    cancelButtonColor: "#64748b",
    confirmButtonText: "Selesai",
    cancelButtonText: "Batal",
  }).then((result) => {
    if (result.isConfirmed) {
      Swal.fire(
        "Selesai!",
        "Pesanan telah diselesaikan. Invoice telah dikirim.",
        "success",
      );
    }
  });
}

function viewOrder(orderId) {
  Swal.fire({
    title: `Detail Pesanan ${orderId}`,
    html: `
            <div style="text-align: left; font-size: 0.9rem;">
                <p><strong>Pelanggan:</strong> PT Maju Jaya (Budi)</p>
                <p><strong>Tanggal Sewa:</strong> 22-24 Okt 2026</p>
                <p><strong>Barang:</strong></p>
                <ul>
                    <li>1x Stage (t: 40-60cm) + Karpet</li>
                    <li>2x AC 5 PK</li>
                </ul>
                <p><strong>Total:</strong> Rp 12.000.000</p>
                 <p><strong>Catatan:</strong> Tolong dikirim jam 9 pagi.</p>
            </div>
        `,
    icon: "info",
    confirmButtonColor: "#D32F2F",
    confirmButtonText: "Tutup",
  });
}
