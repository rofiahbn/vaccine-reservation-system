let searchTimeout;

function handleSearch() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        const search = document.getElementById('searchInput').value;
        const kategori = document.getElementById('kategoriFilter').value;

        window.location.href =
            `products_pelayanan.php?search=${encodeURIComponent(search)}&kategori=${encodeURIComponent(kategori)}`;
    }, 400);
}

function handleFilter() {
    handleSearch();
}

function deleteProduct(id) {
    if (!confirm("Yakin ingin menghapus data ini?")) return;
    window.location.href = `delete_product.php?id=${id}`;
}
