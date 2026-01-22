document.addEventListener("DOMContentLoaded", () => {

    const radios = document.querySelectorAll('input[name="surat"]');

    const previewPanel = document.getElementById("previewPanel");
    const previewContent = document.getElementById("previewContent");
    const previewPlaceholder = document.getElementById("previewPlaceholder");

    // ketika radio dipilih
    radios.forEach(radio => {
        radio.addEventListener("change", function () {
            const jenis = this.value;
            loadTemplateSurat(jenis);
        });
    });

    function handlePreviewSurat() {

        // ambil semua yg dicentang
        const checked = Array.from(checkboxes)
            .filter(cb => cb.checked)
            .map(cb => cb.value);

        if (checked.length === 0) {
            // kosong → tampil placeholder
            previewContent.style.display = "none";
            previewPlaceholder.style.display = "block";
            previewContent.innerHTML = "";
            return;
        }

        // PRIORITAS:
        // sakit > sehat > vaksin
        let jenis = "";

        if (checked.includes("sakit")) jenis = "sakit";
        else if (checked.includes("sehat")) jenis = "sehat";
        else if (checked.includes("vaksin")) jenis = "vaksin";

        loadTemplateSurat(jenis);
    }

    async function loadTemplateSurat(jenis) {

        let file = "";

        if (jenis === "sakit") file = "templates/surat_sakit.php";
        else if (jenis === "sehat") file = "templates/surat_sehat.php";
        else if (jenis === "vaksin") file = "templates/sertifikat_vaksin.php";

        try {
            const res = await fetch(file);
            const html = await res.text();

            previewContent.innerHTML = html;
            previewContent.style.display = "block";
            previewPlaceholder.style.display = "none";

            isiDataPreview();

        } catch (err) {
            console.error("Gagal load template:", err);
        }
    }

    function isiDataPreview() {

        // ambil value dokter & tanggal dari form
        const dokterSelect = document.querySelector('select[name="dokter_id"]');
        const tanggalSuratInput = document.querySelector('input[name="tanggal_surat"]');

        const namaDokter = dokterSelect 
            ? dokterSelect.options[dokterSelect.selectedIndex].text 
            : "";

        const tanggalSurat = tanggalSuratInput && tanggalSuratInput.value
            ? formatTanggal(tanggalSuratInput.value)
            : "";

        setText("pv_rm", PV_RM);
        setText("pv_nama", PV_NAMA);
        setText("pv_tgl_lahir", formatTanggal(PV_TGL_LAHIR));
        setText("pv_jk", PV_JK === "L" ? "Laki-laki" : "Perempuan");
        setText("pv_identitas", PV_IDENTITAS);
        setText("pv_tgl_vaksin", formatTanggal(PV_TGL_VAKSIN));
        setText("pv_dokter", namaDokter);

        // dari input form vaksin
        const jenisVaksin = document.querySelector('input[name="jenis_vaksin"]')?.value;
        const batch = document.querySelector('input[name="batch_vaksin"]')?.value;
        const expired = document.querySelector('input[name="expired_vaksin"]')?.value;

        setText("pv_jenis_vaksin", jenisVaksin);
        setText("pv_batch", batch);
        setText("pv_expired", formatTanggal(expired));
        setText("pv_tanggal_surat", tanggalSurat);
    }

    function setText(id, value) {
        const el = document.getElementById(id);
        if (el) el.innerText = value || "-";
    }

    function formatTanggal(tgl) {
        if (!tgl) return "";

        const d = new Date(tgl);
        return d.toLocaleDateString("id-ID", {
            day: "2-digit",
            month: "long",
            year: "numeric"
        });
    }

});

/* =========================================
   FULL SCREEN PREVIEW (MAXIMIZE)
========================================= */

function openFullPreview() {

    const modal = document.getElementById("modalPreview");
    const modalContent = document.getElementById("modalPreviewContent");
    const previewContent = document.getElementById("previewContent");

    if (!previewContent || previewContent.innerHTML.trim() === "") {
        alert("Belum ada preview surat");
        return;
    }

    // copy isi preview ke modal
    modalContent.innerHTML = previewContent.innerHTML;

    // tampilkan modal
    modal.style.display = "flex";
}

function closePreview() {
    const modal = document.getElementById("modalPreview");
    modal.style.display = "none";
}

