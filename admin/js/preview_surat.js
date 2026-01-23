document.addEventListener("DOMContentLoaded", () => {

    const radios = document.querySelectorAll('input[name="surat"]');
    const formIstirahat = document.getElementById("form-istirahat");
    const formPfLain = document.getElementById("form-pf-lain");

    const previewPanel = document.getElementById("previewPanel");
    const previewContent = document.getElementById("previewContent");
    const previewPlaceholder = document.getElementById("previewPlaceholder");

    // ketika radio dipilih
    radios.forEach(radio => {
        radio.addEventListener("change", function () {
            const jenis = this.value;

            // tampilkan form istirahat hanya jika surat sakit
            if (jenis === "sakit") {
                formIstirahat.style.display = "block";
            } else {
                formIstirahat.style.display = "none";
            }

            // tampilkan PF lain hanya jika surat sehat
            if (jenis === "sehat") {
                formPfLain.style.display = "block";
            } else {
                formPfLain.style.display = "none";
            }

            loadTemplateSurat(jenis);
        });
    });

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

        const namaDokter = dokterSelect 
            ? dokterSelect.options[dokterSelect.selectedIndex].text 
            : "";

        setText("pv_rm", PV_RM);
        setText("pv_nama", PV_NAMA);
        setText("pv_tgl_lahir", PV_TGL_LAHIR);
        setText("pv_usia", PV_USIA);
        setText("pv_jk", PV_JK === "L" ? "Laki-laki" : "Perempuan");
        setText("pv_identitas", PV_IDENTITAS);
        setText("pv_tgl_vaksin", formatTanggal(PV_TGL_VAKSIN));
        setText("pv_dokter", namaDokter);
        
        // ================= ISTIRAHAT (SURAT SAKIT) =================
        const lama = document.getElementById("input_lama")?.value;
        const tglAwal = document.getElementById("input_tgl_awal")?.value;
        const tglAkhir = document.getElementById("input_tgl_akhir")?.value;

        setText("pv_lama", lama);
        setText("pv_tgl_awal", formatTanggal(tglAwal));
        setText("pv_tgl_akhir", formatTanggal(tglAkhir));

        // ================= PEMERIKSAAN FISIK (SURAT SEHAT) =================
        const suhu = document.querySelector('input[name="suhu"]')?.value;
        const nadi = document.querySelector('input[name="nadi"]')?.value;
        const td = document.querySelector('input[name="tekanan_darah"]')?.value;
        const respirasi = document.querySelector('input[name="respirasi"]')?.value;

        const pfLainInput = document.getElementById("input_pf_lain")?.value;
        // kalau kosong → default "Dalam batas normal"
        const pfFinal = pfLainInput && pfLainInput.trim() !== ""
            ? pfLainInput
            : "Dalam batas normal";

        setText("pv_pf_lain", pfFinal);

        setText("pv_suhu", suhu);
        setText("pv_nadi", nadi);
        setText("pv_td", td);
        setText("pv_respirasi", respirasi);

        // dari input form vaksin
        const jenisVaksin = document.querySelector('input[name="jenis_vaksin"]')?.value;
        const batch = document.querySelector('input[name="batch_vaksin"]')?.value;
        const expired = document.querySelector('input[name="expired_vaksin"]')?.value;

        setText("pv_jenis_vaksin", jenisVaksin);
        setText("pv_batch", batch);
        setText("pv_expired", formatTanggal(expired));
        // fallback kalau dari PHP kosong
        const today = new Date().toLocaleDateString("id-ID", {
            day: "2-digit",
            month: "long",
            year: "numeric"
        });

        setText("pv_tanggal_surat", PV_TANGGAL_SURAT || today);

    }

    // ================= AUTO UPDATE ISTIRAHAT REALTIME =================
    ["input_lama", "input_tgl_awal", "input_tgl_akhir"].forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            el.addEventListener("input", () => {
                isiDataPreview();   // 🔥 preview update realtime
            });
        }
    });

    // ================= AUTO UPDATE PEMERIKSAAN SEHAT =================
    ["suhu", "nadi", "tekanan_darah", "respirasi"].forEach(name => {
        const el = document.querySelector(`input[name="${name}"]`);
        if (el) {
            el.addEventListener("input", () => {
                isiDataPreview();
            });
        }
    });

    // ================= AUTO UPDATE PF LAIN =================
    const pfInput = document.getElementById("input_pf_lain");
    if (pfInput) {
        pfInput.addEventListener("input", () => {
            isiDataPreview();
        });
    }

    const pfTextarea = document.querySelector('textarea[name="pemeriksaan_fisik"]');
    if (pfTextarea) {
        pfTextarea.addEventListener("input", () => {
            isiDataPreview();
        });
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

