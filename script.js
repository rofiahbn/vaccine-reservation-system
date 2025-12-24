function hitungUsia() {
            const tglLahir = document.getElementById('tanggalLahir').value;
            if (!tglLahir) return;
            
            const lahir = new Date(tglLahir);
            const today = new Date();
            let usia = today.getFullYear() - lahir.getFullYear();
            const bulan = today.getMonth() - lahir.getMonth();
            
            if (bulan < 0 || (bulan === 0 && today.getDate() < lahir.getDate())) {
                usia--;
            }
            
            const kategori = usia < 18 ? 'Anak' : 'Dewasa';
            
            document.getElementById('usiaText').textContent = usia;
            document.getElementById('kategoriText').textContent = kategori;
            document.getElementById('usiaInfo').style.display = 'block';
        }
        
        function addField(type) {
            let container, inputHTML;
            
            if (type === 'email') {
                container = document.getElementById('emailContainer');
                inputHTML = '<input type="email" name="emails[]" placeholder="contoh@email.com">';
            } else if (type === 'phone') {
                container = document.getElementById('phoneContainer');
                inputHTML = '<input type="tel" name="phones[]" placeholder="08123456789">';
            } else if (type === 'address') {
                container = document.getElementById('addressContainer');
                inputHTML = '<textarea name="addresses[]" placeholder="Jalan, RT/RW, Kelurahan, Kecamatan, Kota, Provinsi, Kode Pos"></textarea>';
            }
            
            const div = document.createElement('div');
            div.className = 'dynamic-field';
            div.innerHTML = inputHTML + '<button type="button" class="btn btn-remove" onclick="removeField(this)">×</button>';
            container.appendChild(div);
        }
        
        function removeField(btn) {
            btn.parentElement.remove();
        }
        
        // Validasi form sebelum submit
        document.getElementById('registrationForm').addEventListener('submit', function(e) {
            const tglLahir = document.getElementById('tanggalLahir').value;
            if (!tglLahir) {
                e.preventDefault();
                alert('Tanggal lahir harus diisi!');
                return;
            }
            
            // Validasi minimal 1 kontak
            const emails = document.querySelectorAll('input[name="emails[]"]');
            const phones = document.querySelectorAll('input[name="phones[]"]');
            const addresses = document.querySelectorAll('textarea[name="addresses[]"]');
            
            let emailValid = false;
            let phoneValid = false;
            let addressValid = false;
            
            emails.forEach(email => {
                if (email.value.trim() !== '') emailValid = true;
            });
            
            phones.forEach(phone => {
                if (phone.value.trim() !== '') phoneValid = true;
            });
            
            addresses.forEach(address => {
                if (address.value.trim() !== '') addressValid = true;
            });
            
            if (!emailValid || !phoneValid || !addressValid) {
                e.preventDefault();
                alert('Minimal harus ada 1 email, 1 nomor HP, dan 1 alamat yang diisi!');
            }
        });