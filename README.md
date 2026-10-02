# Simulasi CBT TKA Pusmendik (Kemendikbud)

Website simulasi ujian Computer-Based Testing (CBT) Tes Kemampuan Akademik (TKA) berstandar resmi Pusmendik (Pusat Asesmen Pendidikan - Kemendikbudristek).

---

## 🚀 Fitur Unggulan Sistem

1. **Antarmuka Split-Screen Standar Pusmendik:**
   - Panel Kiri: Wacana / Stimulus (artikel, infografis data, tabel komparasi, rumus kimia & sains).
   - Panel Kanan: Lembar pertanyaan & pilihan jawaban responsif.
2. **5 Format Soal Interaktif Resmi Pusmendik:**
   - **Pilihan Ganda Tunggal:** 1 opsi jawaban bulat (*radio button*).
   - **Pilihan Ganda Kompleks:** Centang kotak lebih dari satu jawaban benar (*multi-checkbox*).
   - **Pernyataan Matriks Benar/Salah:** Tabel kesesuaian pernyataan dengan wacana.
   - **Menjodohkan:** Menghubungkan premis konsep dengan target definisi/dampak.
   - **Isian Singkat:** Input nilai numerik/istilah langsung dengan toleransi format desimal.
3. **Mekanisme Ujian Terlindungi:**
   - **Auto-Save Real-Time:** Jawaban langsung tersimpan secara *background AJAX* setiap kali opsi dipilih.
   - **Countdown Timer:** Sisa waktu pengerjaan otomatis mengunci ujian saat habis.
   - **Daftar Soal (Drawer Palette):** Grid nomor soal dengan kode warna (Abu-abu = belum dijawab, Biru = sudah dijawab, Kuning = ragu-ragu).
   - **Aksesibilitas Font:** Tombol `A-`, `A`, `A+` untuk menyesuaikan ukuran teks wacana.
4. **Halaman Hasil & Pembahasan Rinci:**
   - Perhitungan skor otomatis skala 0 - 100 dengan *partial scoring* untuk soal kompleks.
   - Review lengkap per butir soal: Jawaban Peserta vs Kunci Jawaban Resmi + Ulasan Pembahasan Mendalam.
   - Fitur cetak rapor evaluasi.

---

## 🛠️ Cara Menjalankan

### Cara 1: Menggunakan File Batch (1-Klik)
Cukup klik ganda file **`start-server.bat`** di folder ini. Browser akan otomatis terbuka ke `http://localhost:8000`.

### Cara 2: Lewat Command Prompt (CMD)
1. Buka CMD:
   ```cmd
   d:
   cd \xampp\htdocs\tester
   \xampp\php\php.exe -S localhost:8000
   ```
2. Buka browser dan kunjungi: **`http://localhost:8000`**

### Cara 3: Lewat Apache XAMPP
1. Buka XAMPP Control Panel dan klik **Start** pada Apache.
2. Buka browser dan kunjungi: **`http://localhost/tester`**
