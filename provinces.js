/* =========================
   DATA PROVINSI & KOTA
========================= */
const indonesiaData = {
    "Aceh": [
        "Banda Aceh", "Langsa", "Lhokseumawe", "Sabang", "Subulussalam",
        "Aceh Barat", "Aceh Barat Daya", "Aceh Besar", "Aceh Jaya",
        "Aceh Selatan", "Aceh Singkil", "Aceh Tamiang", "Aceh Tengah",
        "Aceh Tenggara", "Aceh Timur", "Aceh Utara", "Bener Meriah",
        "Bireuen", "Gayo Lues", "Nagan Raya", "Pidie", "Pidie Jaya",
        "Simeulue"
    ],

    "Sumatera Utara": [
        "Binjai", "Gunungsitoli", "Medan", "Padang Sidempuan",
        "Pematangsiantar", "Sibolga", "Tanjungbalai", "Tebing Tinggi",
        "Asahan", "Batubara", "Dairi", "Deli Serdang",
        "Humbang Hasundutan", "Karo", "Labuhanbatu",
        "Labuhanbatu Selatan", "Labuhanbatu Utara", "Langkat",
        "Mandailing Natal", "Nias", "Nias Barat", "Nias Selatan",
        "Nias Utara", "Padang Lawas", "Padang Lawas Utara",
        "Pakpak Bharat", "Samosir", "Serdang Bedagai", "Simalungun",
        "Tapanuli Selatan", "Tapanuli Tengah", "Tapanuli Utara", "Toba"
    ],

    "Sumatera Barat": [
        "Bukittinggi", "Padang", "Padang Panjang", "Pariaman",
        "Payakumbuh", "Sawahlunto", "Solok",
        "Agam", "Dharmasraya", "Kepulauan Mentawai",
        "Lima Puluh Kota", "Padang Pariaman", "Pasaman",
        "Pasaman Barat", "Pesisir Selatan", "Sijunjung",
        "Solok Selatan", "Tanah Datar"
    ],

    "Riau": [
        "Dumai", "Pekanbaru",
        "Bengkalis", "Indragiri Hilir", "Indragiri Hulu",
        "Kampar", "Kepulauan Meranti", "Kuantan Singingi",
        "Pelalawan", "Rokan Hilir", "Rokan Hulu", "Siak"
    ],

    "DKI Jakarta": [
        "Jakarta Pusat", "Jakarta Utara", "Jakarta Barat",
        "Jakarta Selatan", "Jakarta Timur", "Kepulauan Seribu"
    ],

    "Jawa Barat": [
        "Bandung", "Bekasi", "Bogor", "Cimahi", "Cirebon",
        "Depok", "Sukabumi", "Tasikmalaya", "Banjar",
        "Bandung Barat", "Ciamis", "Cianjur", "Garut",
        "Indramayu", "Karawang", "Kuningan", "Majalengka",
        "Pangandaran", "Purwakarta", "Subang", "Sumedang"
    ],

    "Jawa Tengah": [
        "Magelang", "Pekalongan", "Salatiga", "Semarang",
        "Surakarta", "Tegal", "Banjarnegara", "Banyumas",
        "Batang", "Blora", "Boyolali", "Brebes", "Cilacap",
        "Demak", "Grobogan", "Jepara", "Karanganyar",
        "Kebumen", "Kendal", "Klaten", "Kudus", "Pati",
        "Pemalang", "Purbalingga", "Purworejo", "Rembang",
        "Sragen", "Sukoharjo", "Temanggung", "Wonogiri",
        "Wonosobo"
    ],

    "DI Yogyakarta": [
        "Yogyakarta", "Bantul", "Gunungkidul", "Kulon Progo", "Sleman"
    ],

    "Jawa Timur": [
        "Batu", "Blitar", "Kediri", "Madiun", "Malang",
        "Mojokerto", "Pasuruan", "Probolinggo", "Surabaya",
        "Bangkalan", "Banyuwangi", "Bojonegoro", "Bondowoso",
        "Gresik", "Jember", "Jombang", "Lamongan", "Lumajang",
        "Magetan", "Nganjuk", "Ngawi", "Pacitan", "Pamekasan",
        "Ponorogo", "Sampang", "Sidoarjo", "Situbondo",
        "Sumenep", "Trenggalek", "Tuban", "Tulungagung"
    ],

    "Banten": [
        "Cilegon", "Serang", "Tangerang", "Tangerang Selatan",
        "Lebak", "Pandeglang"
    ],

    "Bali": [
        "Denpasar", "Badung", "Bangli", "Buleleng",
        "Gianyar", "Jembrana", "Karangasem", "Klungkung", "Tabanan"
    ]
};

/* =========================
   LOAD PROVINSI
========================= */
function loadProvinsi(selectedProvinsi = null) {
    const provSelect = document.getElementById('provinsiSelect');
    if (!provSelect) return;

    provSelect.innerHTML = '<option value="">-- Pilih Provinsi --</option>';

    Object.keys(indonesiaData)
        .sort()
        .forEach(prov => {
            const option = document.createElement('option');
            option.value = prov;
            option.textContent = prov;

            if (prov === selectedProvinsi) {
                option.selected = true;
            }

            provSelect.appendChild(option);
        });

    if (selectedProvinsi) {
        loadKota(selectedProvinsi);
    }
}

/* =========================
   LOAD KOTA
========================= */
function loadKota(provinsi, selectedKota = null) {
    const kotaSelect = document.getElementById('kotaSelect');
    if (!kotaSelect) return;

    kotaSelect.innerHTML = '<option value="">-- Pilih Kota --</option>';

    if (!provinsi || !indonesiaData[provinsi]) {
        kotaSelect.disabled = true;
        return;
    }

    kotaSelect.disabled = false;

    indonesiaData[provinsi]
        .sort()
        .forEach(kota => {
            const option = document.createElement('option');
            option.value = kota;
            option.textContent = kota;

            if (kota === selectedKota) {
                option.selected = true;
            }

            kotaSelect.appendChild(option);
        });
}

/* =========================
   EVENT
========================= */
document.getElementById('provinsiSelect')?.addEventListener('change', function () {
    loadKota(this.value);
});

/* =========================
   INIT (EDIT MODE)
========================= */
document.addEventListener('DOMContentLoaded', () => {
    const oldProv = document.getElementById('oldProvinsi')?.value;
    const oldKota = document.getElementById('oldKota')?.value;

    loadProvinsi(oldProv);
    if (oldProv) {
        loadKota(oldProv, oldKota);
    }
});
