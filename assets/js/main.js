// assets/js/main.js
document.addEventListener("DOMContentLoaded", function() {
    const formBooking = document.getElementById("form-booking-client");
    
    if(formBooking) {
        formBooking.addEventListener("submit", function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            // Pengiriman via Fetch API AJAX murni
            fetch("transaksi/booking/proses_tambah.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if(data.status === "success") {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: data.message,
                        showConfirmButton: false,
                        timer: 2000
                    }).then(() => {
                        window.location.href = "index.php";
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: data.message
                    });
                }
            })
            .catch(error => {
                console.error("Error:", error);
                Swal.fire({
                    icon: 'error',
                    title: 'System Error',
                    text: 'Terjadi kegagalan koneksi ke server.'
                });
            });
        });
    }
});