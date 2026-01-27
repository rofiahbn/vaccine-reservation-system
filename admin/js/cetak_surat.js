document.getElementById("btnCetakSurat").addEventListener("click", function () {

    const previewContent = document.getElementById("previewContent");

    if (!previewContent || previewContent.innerHTML.trim() === "") {
        alert("⚠️ Preview surat masih kosong");
        return;
    }

    const jenisSurat = document.querySelector('input[name="surat"]:checked')?.value;
    if (!jenisSurat) {
        alert("⚠️ Pilih jenis surat dulu");
        return;
    }

    const formData = new FormData();

    // kirim data utama
    formData.append("booking_id", document.querySelector('input[name="booking_id"]').value);
    formData.append("patient_id", document.querySelector('input[name="patient_id"]').value);
    formData.append("jenis_surat", jenisSurat);
    formData.append("dokter_id", document.querySelector('select[name="dokter_id"]').value);
    formData.append("posisi", document.querySelector('input[name="posisi"]').value);

    // khusus surat sakit
    formData.append("lama_istirahat", document.getElementById("input_lama")?.value || "");
    formData.append("tgl_awal", document.getElementById("input_tgl_awal")?.value || "");
    formData.append("tgl_akhir", document.getElementById("input_tgl_akhir")?.value || "");

    // khusus surat sehat
    formData.append("pf_lain", document.getElementById("input_pf_lain")?.value || "");

    // data vaksin dari form tindakan
    formData.append("jenis_vaksin", document.querySelector('input[name="jenis_vaksin"]').value || "");
    formData.append("batch_vaksin", document.querySelector('input[name="batch_vaksin"]').value || "");
    formData.append("expired_vaksin", document.querySelector('input[name="expired_vaksin"]').value || "");

    // kirim isi surat (HTML preview)
    formData.append("html_surat", previewContent.innerHTML);

    fetch("cetak_surat.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.json())
    .then(res => {
        if (res.success) {
            // ✅ PERBAIKAN PATH
            window.open("../" + res.file, "_blank");
            // Dari: admin/proses_tindakan.php
            // Ke: uploads/surat/file.pdf
            // Path: ../uploads/surat/file.pdf
        } else {
            alert("❌ Gagal cetak surat: " + res.message);
        }
    })
    .catch(err => {
        console.error(err);
        alert("Terjadi kesalahan saat cetak surat");
    });

});