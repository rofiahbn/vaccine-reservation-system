document.getElementById("formTindakan").addEventListener("submit", function(e) {
    e.preventDefault();

    const form = this;
    const formData = new FormData(form);

    // 🔥 PAKSA KIRIM ULANG ID (ANTI HILANG)
    const bookingId = document.querySelector('input[name="booking_id"]').value;
    const patientId = document.querySelector('input[name="patient_id"]').value;

    formData.set("booking_id", bookingId);
    formData.set("patient_id", patientId);

    // 🔥 TIDAK KIRIM DATA SURAT SAMA SEKALI
    // surat hanya untuk preview & cetak nanti

    fetch("proses_simpan_tindakan.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.text())
    .then(text => {
        try {
            const data = JSON.parse(text);

            if (data.success) {
                const parentId = document.querySelector('input[name="parent_booking_id"]').value;
                const patientId = document.querySelector('input[name="patient_id"]').value;

                window.location.href =
                    "proses_tindakan.php?id=" + parentId +
                    "&participant_id=" + patientId;

            } else {
                alert("❌ Gagal simpan tindakan: " + data.message);
            }

        } catch (e) {
            console.error("Response bukan JSON:", text);
            alert("Terjadi kesalahan server");
        }
    });
});
