document.getElementById("btnCetakSurat").addEventListener("click", function () {

    const previewContent = document.getElementById("previewContent");

    if (!previewContent || previewContent.innerHTML.trim() === "") {
        alert("⚠️ Preview surat masih kosong");
        return;
    }

    // buka window baru khusus cetak
    const printWindow = window.open("", "_blank", "width=900,height=650");

    printWindow.document.write(`
        <html>
        <head>
            <title>Cetak Surat</title>

            <!-- load css surat -->
            <link rel="stylesheet" href="css/surat.css">

            <style>
                body {
                    margin: 0;
                    padding: 0;
                }

                /* paksa ukuran A4 */
                @page {
                    size: A4;
                    margin: 0;
                }

                /* hilangkan scale preview */
                .surat-template {
                    transform: scale(1) !important;
                    margin: 0 auto;
                }
            </style>
        </head>
        <body>
            ${previewContent.innerHTML}
        </body>
        </html>
    `);

    printWindow.document.close();

    // tunggu render dulu baru print
    setTimeout(() => {
        printWindow.focus();
        printWindow.print();
        // printWindow.close();  // aktifkan kalau mau auto close
    }, 500);
});
