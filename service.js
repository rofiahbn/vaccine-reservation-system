function tampilkanPilihanPelayanan() {
    const pelayanan = document.getElementById('pelayananSelect').value;
    const container = document.getElementById('pilihanPelayananContainer');
    
    if (pelayanan === 'Vaksin') {
        container.style.display = 'block';
        container.innerHTML = `
            <div class="form-group">
                <label>Pilih Vaksin <span class="required">*</span></label>
                <p style="font-size:13px; color:#7f8c8d; margin-bottom:10px;">Anda dapat memilih lebih dari 1 Vaksin sesuai kebutuhan anda</p>
                
                <div class="vaksin-grid">
                    <label class="checkbox-item">
                        <input type="checkbox" name="vaksin[]" value="Adacel (Sanofi)"> Adacel (Sanofi)
                    </label>
                    <label class="checkbox-item">
                        <input type="checkbox" name="vaksin[]" value="Arexvy (GSK)"> Arexvy (GSK)
                    </label>
                    <label class="checkbox-item">
                        <input type="checkbox" name="vaksin[]" value="Avaxim 160 (Sanofi)"> Avaxim 160 (Sanofi)
                    </label>
                    <label class="checkbox-item">
                        <input type="checkbox" name="vaksin[]" value="Avaxim 80 (Sanofi)"> Avaxim 80 (Sanofi)
                    </label>
                    <label class="checkbox-item">
                        <input type="checkbox" name="vaksin[]" value="Bexsero (GSK)"> Bexsero (GSK)
                    </label>
                    <label class="checkbox-item">
                        <input type="checkbox" name="vaksin[]" value="bOPV Polio (Biofarma)"> bOPV Polio (Biofarma)
                    </label>
                    <label class="checkbox-item">
                        <input type="checkbox" name="vaksin[]" value="Campak (Biofarma)"> Campak (Biofarma)
                    </label>
                    <label class="checkbox-item">
                        <input type="checkbox" name="vaksin[]" value="Cervarix (GSK)"> Cervarix (GSK)
                    </label>
                    <label class="checkbox-item">
                        <input type="checkbox" name="vaksin[]" value="Engerix B 20mcg (GSK)"> Engerix B 20mcg (GSK)
                    </label>
                    <label class="checkbox-item">
                        <input type="checkbox" name="vaksin[]" value="Euvax B Adult (Sanofi)"> Euvax B Adult (Sanofi)
                    </label>
                    <label class="checkbox-item">
                        <input type="checkbox" name="vaksin[]" value="Euvax B Pediatric (Sanofi)"> Euvax B Pediatric (Sanofi)
                    </label>
                    <label class="checkbox-item">
                        <input type="checkbox" name="vaksin[]" value="Fluarix Tetra (GSK)"> Fluarix Tetra (GSK)
                    </label>
                    <label class="checkbox-item">
                        <input type="checkbox" name="vaksin[]" value="Gardasil (MSD)"> Gardasil (MSD)
                    </label>
                    <label class="checkbox-item">
                        <input type="checkbox" name="vaksin[]" value="Gardasil 9 (MSD)"> Gardasil 9 (MSD)
                    </label>
                    <label class="checkbox-item">
                        <input type="checkbox" name="vaksin[]" value="Havrix 1440 (GSK)"> Havrix 1440 (GSK)
                    </label>
                    <label class="checkbox-item">
                        <input type="checkbox" name="vaksin[]" value="Havrix 720 (GSK)"> Havrix 720 (GSK)
                    </label>
                    <label class="checkbox-item">
                        <input type="checkbox" name="vaksin[]" value="Hexaxim (Sanofi)"> Hexaxim (Sanofi)
                    </label>
                    <label class="checkbox-item">
                        <input type="checkbox" name="vaksin[]" value="Imovjev (Sanofi)"> Imovjev (Sanofi)
                    </label>
                    <label class="checkbox-item">
                        <input type="checkbox" name="vaksin[]" value="Infanrix Hexa (GSK)"> Infanrix Hexa (GSK)
                    </label>
                    <label class="checkbox-item">
                        <input type="checkbox" name="vaksin[]" value="Influvac Tetra (Abbott)"> Influvac Tetra (Abbott)
                    </label>
                    <label class="checkbox-item">
                        <input type="checkbox" name="vaksin[]" value="IPV (Biofarma)"> IPV (Biofarma)
                    </label>
                    <label class="checkbox-item">
                        <input type="checkbox" name="vaksin[]" value="MMR II (MSD)"> MMR II (MSD)
                    </label>
                    <label class="checkbox-item">
                        <input type="checkbox" name="vaksin[]" value="MR (Biofarma)"> MR (Biofarma)
                    </label>
                    <label class="checkbox-item">
                        <input type="checkbox" name="vaksin[]" value="Menactra (Sanofi)"> Menactra (Sanofi)
                    </label>
                    <label class="checkbox-item">
                        <input type="checkbox" name="vaksin[]" value="Menquadfi (Sanofi)"> Menquadfi (Sanofi)
                    </label>
                    <label class="checkbox-item">
                        <input type="checkbox" name="vaksin[]" value="Pneumovax 23 (MSD)"> Pneumovax 23 (MSD)
                    </label>
                    <label class="checkbox-item">
                        <input type="checkbox" name="vaksin[]" value="Prevenar 13 (Pfizer)"> Prevenar 13 (Pfizer)
                    </label>
                    <label class="checkbox-item">
                        <input type="checkbox" name="vaksin[]" value="Prevenar 20 (Pfizer)"> Prevenar 20 (Pfizer)
                    </label>
                    <label class="checkbox-item">
                        <input type="checkbox" name="vaksin[]" value="BCG (Biofarma)"> BCG (Biofarma)
                    </label>
                    <label class="checkbox-item">
                        <input type="checkbox" name="vaksin[]" value="Qdanga (Takeda)"> Qdanga (Takeda)
                    </label>
                    <label class="checkbox-item">
                        <input type="checkbox" name="vaksin[]" value="Twinrix (GSK)"> Twinrix (GSK)
                    </label>
                    <label class="checkbox-item">
                        <input type="checkbox" name="vaksin[]" value="Engerix B 10mcg (GSK)"> Engerix B 10mcg (GSK)
                    </label>
                    <label class="checkbox-item">
                        <input type="checkbox" name="vaksin[]" value="Rotateq (MSD)"> Rotateq (MSD)
                    </label>
                    <label class="checkbox-item">
                        <input type="checkbox" name="vaksin[]" value="Shingrix (GSK)"> Shingrix (GSK)
                    </label>
                    <label class="checkbox-item">
                        <input type="checkbox" name="vaksin[]" value="Typhim Vi (Sanofi)"> Typhim Vi (Sanofi)
                    </label>
                    <label class="checkbox-item">
                        <input type="checkbox" name="vaksin[]" value="Varilrix (MSD)"> Varilrix (MSD)
                    </label>
                    <label class="checkbox-item">
                        <input type="checkbox" name="vaksin[]" value="Vaxigrip Tetra (Sanofi)"> Vaxigrip Tetra (Sanofi)
                    </label>
                    <label class="checkbox-item">
                        <input type="checkbox" name="vaksin[]" value="Vaxneuvance (MSD)"> Vaxneuvance (MSD)
                    </label>
                    <label class="checkbox-item">
                        <input type="checkbox" name="vaksin[]" value="Vecon Adult (Biofarma)"> Vecon Adult (Biofarma)
                    </label>
                    <label class="checkbox-item">
                        <input type="checkbox" name="vaksin[]" value="Verorab (Sanofi)"> Verorab (Sanofi)
                    </label>
                    <label class="checkbox-item">
                        <input type="checkbox" name="vaksin[]" value="Vivaxim (Sanofi)"> Vivaxim (Sanofi)
                    </label>
                    <label class="checkbox-item">
                        <input type="checkbox" name="vaksin[]" value="Formening (Mersi)"> Formening (Mersi)
                    </label>
                    <label class="checkbox-item">
                        <input type="checkbox" name="vaksin[]" value="Hepatitis B Dewasa (Biofarma)"> Hepatitis B Dewasa (Biofarma)
                    </label>
                    <label class="checkbox-item">
                        <input type="checkbox" name="vaksin[]" value="Inlive (Sinovac)"> Inlive (Sinovac)
                    </label>
                    <label class="checkbox-item">
                        <input type="checkbox" name="vaksin[]" value="Menivax (Biofarma)"> Menivax (Biofarma)
                    </label>
                    <label class="checkbox-item">
                        <input type="checkbox" name="vaksin[]" value="Proquad (MSD)"> Proquad (MSD)
                    </label>
                    <label class="checkbox-item">
                        <input type="checkbox" name="vaksin[]" value="Synflorix (GSK)"> Synflorix (GSK)
                    </label>
                    <label class="checkbox-item">
                        <input type="checkbox" name="vaksin[]" value="Stamaril (Sanofi)"> Stamaril (Sanofi)
                    </label>
                    <label class="checkbox-item">
                        <input type="checkbox" name="vaksin[]" value="Tetraxim (Sanofi)"> Tetraxim (Sanofi)
                    </label>
                </div>
            </div>
        `;
        } else if (pelayanan === 'Vitamin') {
            container.style.display = 'block';
            container.innerHTML = `
                <div class="form-group">
                    <label>Pilih Vitamin <span class="required">*</span></label>
                    <p style="font-size:13px; color:#7f8c8d; margin-bottom:10px;">
                        Anda dapat memilih lebih dari 1 Vitamin sesuai kebutuhan anda
                    </p>

                    <div class="vaksin-grid">
                        <label class="checkbox-item">
                            <input type="checkbox" name="vitamin[]" value="Vitamin Badan Bugar">
                            Vitamin Badan Bugar
                        </label>

                        <label class="checkbox-item">
                            <input type="checkbox" name="vitamin[]" value="Vitamin Segar Bugar">
                            Vitamin Segar Bugar
                        </label>

                        <label class="checkbox-item">
                            <input type="checkbox" name="vitamin[]" value="Vitamin Sultan">
                            Vitamin Sultan
                        </label>

                        <label class="checkbox-item">
                            <input type="checkbox" name="vitamin[]" value="Vitamin Jeruk Segar">
                            Vitamin Jeruk Segar
                        </label>

                        <label class="checkbox-item">
                            <input type="checkbox" name="vitamin[]" value="Vitamin Remaja Abadi">
                            Vitamin Remaja Abadi
                        </label>

                        <label class="checkbox-item">
                            <input type="checkbox" name="vitamin[]" value="Vitamin Bugar Kinclong">
                            Vitamin Bugar Kinclong
                        </label>
                    </div>
                </div>
            `;

    } else {
        container.style.display = 'none';
        container.innerHTML = '';
    }
}