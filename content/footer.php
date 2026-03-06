
<div id="footer">
    <div id="operational" >
        <h1>Jam Operasional</h1>
        <h2>Home Service</h2>
        <p>Dengan Perjanjian</p>
        <h2>Klinik</h2>
        <p>Senin - Sabu : 09.00 - 17.00</p>
        <p>Minggu       : 09.00 - 16.30 </p>
        <p> Hari libur nasional dan cuti bersama:<br/>tutup</p>
        
    </div>
    
    <div id="alamat">
        <h1>Hubungi Kami</h1>
        <h2>Klinik Vaksinin</h2>
        <p>Komplek Ruko Sentra Menteng Blok MN 88 I Jl. Moh. Husni Thamrin, Bintaro Sektor 7 Kel. Pondok Jaya, Kec. Pondok Aren, Kota Tangerang Selatan, Banten 15220</p>
        <br/>
        <p>https://maps.app.goo.gl/Hc3H8Xe7RzLvLMR6A</p>
    </div>
    
    <div id="maps" >
        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3965.9113192216073!2d106.71921788476418!3d-6.275389945203535!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e69f1ff936d9b19%3A0xd5c17ad1727bba4b!2sVaksinin%20Bintaro!5e0!3m2!1sid!2sid!4v1770553198277!5m2!1sid!2sid" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
    </div>
</div>

<script src="https://widget.tagembed.com/embed.min.js" type="text/javascript"></script>
<script>
  const hamburger = document.getElementById('hamburger');
  const nav = document.getElementById('nav');

  // Toggle Menu Utama di Mobile
  hamburger.addEventListener('click', () => {
    nav.classList.toggle('active');
  });

  // Toggle Dropdown di Mobile
  document.querySelectorAll('.dropdown-toggle').forEach(toggle => {
    toggle.addEventListener('click', function (e) {
      if (window.innerWidth <= 768) {
        e.preventDefault(); // Mencegah link pindah halaman
        const parent = this.parentElement;
        parent.classList.toggle('active');
      }
    });
  });
</script>