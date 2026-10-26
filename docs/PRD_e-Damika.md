# PRODUCT REQUIREMENTS DOCUMENT (PRD)

## e-Damika — Data Mining Kesehatan

**Versi 1.0 | 25 Oktober 2026**

---

> **Dokumen kebutuhan produk:** e-Damika adalah aplikasi analisis pola kunjungan pasien berbasis data historis rekam medis. Dokumen ini menjadi acuan kebutuhan produk, pengembangan, dan pengujian.

## 1. Informasi Dokumen

### Tujuan dokumen

Dokumen ini mendefinisikan kebutuhan produk, ruang lingkup, fungsi, alur pengguna, kebutuhan data, persyaratan algoritma, keamanan, dan kriteria penerimaan untuk pengembangan e-Damika menggunakan CodeIgniter 4 (CI4).

### Nama produk

e-Damika (Data Mining Kesehatan).

### Judul proyek

Pengembangan Aplikasi Analisis Pola Kunjungan Pasien Berdasarkan Data Rekam Medis Menggunakan Metode Data Mining.

### Jenis produk

Aplikasi web untuk import, pengelolaan, analisis, visualisasi, dan pelaporan pola kunjungan pasien berdasarkan data historis.

### Versi dokumen

1.0.

### Status

Dokumen kebutuhan awal yang menjadi dasar desain, implementasi, pengujian, dan evaluasi proyek.

## 2. Ringkasan Eksekutif

### Ringkasan

e-Damika adalah aplikasi web berbasis CodeIgniter 4 yang dikembangkan untuk mengidentifikasi pola kunjungan pasien dari data historis rekam medis. Aplikasi memproses atribut seperti usia, jenis kelamin, diagnosis, unit pelayanan, dan periode kunjungan, lalu menyajikan pola dalam bentuk grafik, ringkasan analitik, serta laporan.

### Tujuan penggunaan

Membantu pengguna memahami karakteristik dan pola kunjungan, melihat kelompok kunjungan yang terbentuk, mengevaluasi model klasifikasi apabila tersedia target kelas, serta mendukung perencanaan pelayanan dan sumber daya fasilitas kesehatan.

### Prinsip utama

Hasil analisis harus dihitung dari dataset yang benar-benar diimpor. Sistem tidak boleh menampilkan angka, grafik, atau hasil model statis seolah-olah berasal dari data pengguna.

### Fleksibilitas dataset

Aplikasi ditujukan untuk menerima berbagai file spreadsheet dan data tabular yang didukung. Nama kolom dan struktur file dapat berbeda. Sistem mendeteksi struktur, menampilkan pratinjau, menawarkan pemetaan kolom, dan meminta konfirmasi pengguna bila pemetaan tidak pasti.

## 3. Latar Belakang dan Permasalahan

### Latar belakang

Data kunjungan pasien memuat informasi yang dapat digunakan untuk memahami kebutuhan layanan kesehatan. Namun, data dalam file spreadsheet sering memiliki nama kolom, format tanggal, tipe nilai, dan susunan yang berbeda. Pengolahan manual menyulitkan analisis berulang, perbandingan hasil, dan penyusunan laporan.

### Permasalahan yang ingin diselesaikan

1) Data tersebar dalam file dengan format dan struktur yang bervariasi. 2) Pengguna perlu membersihkan dan menyiapkan data sebelum analisis. 3) Pola kunjungan sulit dilihat hanya dari tabel mentah. 4) Hasil analisis perlu dapat ditinjau ulang dan dilaporkan. 5) Tiga metode data mining perlu tersedia dengan tujuan penggunaan yang tepat.

### Pernyataan solusi

e-Damika menyediakan proses terpadu dari upload dan validasi data, pemetaan kolom, preprocessing, pemilihan metode, eksekusi analisis, visualisasi, evaluasi, hingga ekspor laporan.

## 4. Tujuan Produk dan Indikator Keberhasilan

### Tujuan produk

1) Mempermudah import data kunjungan pasien. 2) Mendukung variasi nama dan susunan kolom melalui pemetaan. 3) Menjalankan K-Means, C4.5, dan Naive Bayes sesuai persyaratan data tiap algoritma. 4) Menyajikan hasil yang mudah dipahami melalui grafik, tabel, aturan, dan laporan. 5) Menyimpan riwayat proses agar hasil dapat diaudit dan ditinjau ulang.

### Indikator keberhasilan

• Pengguna dapat mengunggah format yang dinyatakan didukung dan menerima pesan yang jelas jika file tidak valid. • Sistem menampilkan pratinjau dan ringkasan validasi sebelum menyimpan dataset. • Pengguna dapat memetakan kolom yang berbeda ke atribut analitik yang relevan. • Hasil berubah sesuai dataset dan konfigurasi aktual. • K-Means menghasilkan label cluster dan ringkasan cluster. • C4.5 menghasilkan pohon/aturan klasifikasi jika target valid tersedia. • Naive Bayes menghasilkan prediksi kelas dan probabilitas jika target valid tersedia. • Laporan dapat diunduh dan merujuk pada proses analisis yang benar. • Akses ke data dibatasi sesuai peran.

### Catatan pengukuran

Target numerik seperti waktu proses maksimum, ukuran file maksimum, atau jumlah baris maksimum harus ditetapkan setelah kebutuhan infrastruktur dan contoh dataset diketahui; jangan mengklaim target performa yang belum diuji.

## 5. Ruang Lingkup Produk

### Termasuk dalam ruang lingkup

• Autentikasi dan pengelolaan pengguna sesuai peran. • Dashboard ringkasan. • Upload file data tabular yang didukung. • Pratinjau, validasi, pemetaan kolom, dan preprocessing. • Penyimpanan metadata dataset dan riwayat import. • K-Means clustering. • C4.5 classification/decision tree. • Naive Bayes classification. • Visualisasi dan evaluasi hasil. • Riwayat analisis. • Ekspor laporan. • Validasi keamanan dasar dan pencatatan aktivitas penting.

### Di luar ruang lingkup versi awal

• Integrasi langsung ke SIMRS/EMR rumah sakit tanpa spesifikasi dan persetujuan integrasi terpisah. • Diagnosis klinis atau rekomendasi terapi pasien. • Pengambilan keputusan klinis otomatis. • Jaminan kompatibilitas dengan setiap format file yang ada di dunia. • Prediksi yang dianggap valid tanpa data label, evaluasi, dan validasi yang memadai. • Pengiriman data pasien ke layanan eksternal tanpa dasar, persetujuan, dan pengamanan yang sesuai.

### Asumsi

• Pengguna memiliki hak yang sah untuk mengolah data yang diunggah. • File yang diunggah merupakan data tabular dengan header atau dapat dipilih header-nya. • Pengguna memahami makna kolom dan dapat mengonfirmasi pemetaan yang ambigu. • Kualitas dan kelengkapan hasil bergantung pada kualitas dataset.

## 6. Pengguna dan Hak Akses

### Admin

Mengelola akun pengguna, melihat dan mengelola dataset sesuai kebijakan, mengakses riwayat analisis, mengatur konfigurasi aplikasi, dan memantau aktivitas.

### Operator/analis

Mengunggah dataset, memeriksa dan memetakan kolom, menjalankan analisis, melihat hasil yang diizinkan, serta mengekspor laporan.

### Pembaca laporan (opsional)

Melihat dashboard dan laporan yang dibagikan atau diizinkan tanpa hak mengubah dataset maupun konfigurasi.

### Prinsip otorisasi

Hak akses harus diperiksa di sisi server untuk setiap aksi, bukan hanya menyembunyikan tombol pada antarmuka. Akses dataset dan hasil analisis mengikuti pemilik, organisasi, atau kebijakan peran yang ditetapkan.

## 7. Alur Pengguna Utama

### Alur import

1) Pengguna masuk. 2) Membuka menu Dataset. 3) Memilih file. 4) Sistem memeriksa ekstensi, ukuran, dan keamanan file. 5) Sistem membaca sheet/kolom yang didukung. 6) Pengguna memilih sheet dan header bila diperlukan. 7) Sistem menampilkan pratinjau dan ringkasan kualitas. 8) Sistem menyarankan pemetaan kolom. 9) Pengguna mengonfirmasi atau mengubah pemetaan. 10) Sistem menjalankan validasi dan preprocessing. 11) Pengguna menyimpan dataset yang siap dianalisis atau memperbaiki kesalahan.

### Alur analisis

1) Pengguna memilih dataset. 2) Memilih metode. 3) Sistem memeriksa apakah atribut wajib tersedia. 4) Pengguna mengatur parameter dan atribut. 5) Sistem menjalankan proses analisis. 6) Sistem menyimpan konfigurasi dan status. 7) Sistem menampilkan hasil, visualisasi, peringatan, dan metrik yang relevan. 8) Pengguna meninjau atau mengekspor laporan.

### Alur riwayat

Pengguna membuka riwayat, memilih suatu proses, melihat dataset dan konfigurasi yang digunakan, status, hasil, metrik, serta laporan yang dihasilkan sesuai hak akses.

## 8. Persyaratan Fungsional

### FR-01 Autentikasi

Sistem harus menyediakan login dan logout serta melindungi halaman internal dari pengguna yang belum terautentikasi.

### FR-02 Pengelolaan peran

Admin dapat membuat, memperbarui, menonaktifkan, dan mengatur peran akun sesuai kebijakan aplikasi.

### FR-03 Upload dataset

Pengguna yang berwenang dapat mengunggah file dalam format yang didukung. Sistem memvalidasi ekstensi, MIME/isi file, ukuran, dan kegagalan pembacaan; nama file tidak boleh menjadi satu-satunya dasar validasi.

### FR-04 Pemilihan sheet/header

Untuk format spreadsheet yang memiliki sheet, sistem harus menyediakan pemilihan sheet. Sistem harus mendukung penentuan baris header atau menjelaskan format header yang diwajibkan.

### FR-05 Pratinjau

Sistem harus menampilkan sampel baris, nama kolom, jumlah baris/kolom yang terdeteksi bila tersedia, serta ringkasan nilai kosong dan tipe data.

### FR-06 Deteksi dan pemetaan kolom

Sistem harus mendeteksi tipe data dan menawarkan pemetaan berdasarkan nama serta isi kolom. Pengguna dapat mengubah pemetaan. Pemetaan yang ambigu tidak boleh diterapkan diam-diam.

### FR-07 Dataset dinamis

Sistem harus menyimpan metadata struktur tiap dataset agar dataset dengan nama kolom dan urutan berbeda dapat dikelola. Kolom tambahan yang tidak dipakai oleh suatu metode tetap dapat dipertahankan dalam data mentah atau metadata.

### FR-08 Validasi kualitas

Sistem harus memeriksa nilai kosong, format tanggal, tipe data yang tidak konsisten, baris duplikat sesuai definisi, nilai di luar rentang yang ditentukan, serta kolom yang tidak dapat diproses.

### FR-09 Preprocessing

Sistem harus menyediakan strategi preprocessing yang sesuai metode, termasuk encoding kategori, penanganan nilai hilang, normalisasi/skala fitur jika diperlukan, dan dokumentasi transformasi. Pengguna harus mengetahui transformasi yang diterapkan.

### FR-10 Pengelolaan dataset

Pengguna yang berwenang dapat melihat daftar dataset, detail, status validasi, waktu import, sumber file, serta menghapus atau mengarsipkan sesuai kebijakan retensi.

### FR-11 K-Means

Sistem harus menyediakan konfigurasi jumlah cluster K, pemilihan fitur, strategi scaling/encoding, dan pengaturan relevan lainnya. Hasil minimal memuat label cluster per baris, jumlah anggota, ringkasan karakteristik cluster, dan visualisasi yang sesuai.

### FR-12 C4.5

Sistem harus menyediakan pemilihan atribut input dan target kelas. Sistem harus memvalidasi target dan data pelatihan sebelum menjalankan klasifikasi. Hasil minimal memuat struktur pohon atau representasi yang dapat dibaca, aturan keputusan, dan metrik evaluasi bila pengujian yang sesuai tersedia.

### FR-13 Naive Bayes

Sistem harus menyediakan pemilihan atribut input dan target kelas, memilih varian/strategi likelihood yang sesuai dengan jenis fitur, serta menampilkan prediksi dan probabilitas kelas jika tersedia. Sistem harus menjelaskan batasan data atau kelas yang tidak memadai.

### FR-14 Validasi model

Untuk klasifikasi, sistem harus mendukung pemisahan data latih/uji atau validasi silang jika sesuai, menghindari kebocoran data, dan menampilkan metrik seperti accuracy, precision, recall, F1-score, serta confusion matrix jika dapat dihitung.

### FR-15 Hasil dinamis

Seluruh ringkasan, grafik, tabel, dan laporan harus dihitung dari dataset, parameter, dan versi proses yang sebenarnya. Tidak boleh ada hasil contoh statis yang ditampilkan sebagai hasil analisis nyata.

### FR-16 Dashboard

Dashboard menampilkan ringkasan sesuai data yang tersedia, misalnya jumlah baris kunjungan, rentang periode, distribusi atribut, jumlah dataset, dan ringkasan proses analisis. Sistem harus membedakan jumlah kunjungan dari jumlah pasien unik.

### FR-17 Visualisasi

Sistem menampilkan visualisasi yang cocok dengan hasil: grafik jumlah/proporsi, tren waktu jika tanggal valid, visualisasi cluster, pohon keputusan, dan matriks kebingungan. Grafik tidak boleh mengarang kategori atau nilai yang tidak terdapat dalam hasil.

### FR-18 Laporan

Pengguna dapat mengekspor ringkasan dataset, langkah preprocessing, metode, parameter, hasil, metrik, waktu proses, serta peringatan kualitas ke format yang disediakan, misalnya XLSX/CSV/PDF.

### FR-19 Riwayat analisis

Sistem menyimpan dataset referensi, metode, parameter, versi/preprocessing, status, waktu, metrik, dan ringkasan hasil agar proses dapat ditinjau ulang.

### FR-20 Penanganan kegagalan

Jika file atau analisis gagal, sistem menampilkan pesan yang membantu, tidak membocorkan stack trace kepada pengguna, dan mencatat detail teknis secara aman untuk administrator.

### FR-21 Audit

Sistem mencatat aktivitas penting seperti login, upload, penghapusan dataset, perubahan konfigurasi, dan eksekusi analisis sesuai kebijakan audit.

### FR-22 Penghapusan/retensi

Sistem harus menerapkan kebijakan retensi dan penghapusan file, data hasil, serta salinan sementara; penghapusan harus konsisten dan dibatasi sesuai hak akses.

## 9. Persyaratan Metode Data Mining

### 9.1 K-Means

Tujuan: mengelompokkan record kunjungan berdasarkan kemiripan fitur. Masukan: fitur yang dipilih pengguna dan dapat direpresentasikan secara numerik setelah preprocessing. Keluaran: label cluster, centroid atau representasi pusat, jumlah anggota, ringkasan tiap cluster, dan visualisasi. Parameter penting: K, fitur, scaling, encoding, dan random seed bila relevan. Validasi: jelaskan bahwa nomor cluster tidak memiliki urutan makna intrinsik; bila tersedia, tampilkan metrik internal seperti inertia atau silhouette dengan penjelasan batasannya. Fitur kategorikal seperti diagnosis dan unit pelayanan tidak boleh dikonversi secara sembarang; pilih encoding/distance yang sesuai atau batasi fitur dengan peringatan.

### 9.2 C4.5

Tujuan: membangun pohon keputusan untuk memprediksi target kategorikal. Masukan: atribut prediktor dan label target yang valid. Keluaran: pohon keputusan, aturan IF–THEN, kelas prediksi, dan metrik evaluasi bila tersedia. Implementasi harus menjelaskan pemilihan split berdasarkan gain ratio atau pendekatan C4.5 yang setara, penanganan atribut numerik/kategorikal, pruning, dan missing values sesuai implementasi yang dipilih. Jika target tidak ada atau hanya satu kelas, sistem harus menolak atau menjelaskan bahwa klasifikasi tidak dapat dilakukan.

### 9.3 Naive Bayes

Tujuan: mengklasifikasikan record berdasarkan probabilitas kelas dengan asumsi independensi kondisional fitur. Masukan: atribut prediktor dan label target. Keluaran: kelas prediksi, probabilitas kelas jika implementasi mendukung, dan metrik evaluasi. Sistem harus memilih model likelihood sesuai tipe fitur, misalnya Gaussian untuk fitur numerik kontinu dan model kategorikal untuk fitur kategorikal, atau strategi campuran yang didokumentasikan. Gunakan smoothing bila relevan dan tangani fitur/kelas yang belum terlihat pada data latih.

### Pemilihan metode

K-Means tidak membutuhkan label target, sedangkan C4.5 dan Naive Bayes membutuhkan target kelas untuk pelatihan terawasi. Ketiga metode tidak wajib dijalankan pada konfigurasi fitur yang identik jika persyaratan tipe data berbeda. Antarmuka harus menjelaskan atribut yang digunakan dan alasan metode tidak tersedia pada dataset tertentu.

### Kehati-hatian interpretasi

Hasil clustering bukan diagnosis atau kategori klinis yang telah tervalidasi. Hasil klasifikasi adalah prediksi statistik yang kualitasnya bergantung pada label, sampel, distribusi data, dan validasi. Hasil tidak boleh dipresentasikan sebagai keputusan medis otomatis.

## 10. Import File dan Kompatibilitas Format

### Format awal yang ditargetkan

XLSX, XLS, dan CSV. Format ODS dapat ditambahkan jika reader yang dipakai mendukungnya. Daftar format harus dinyatakan secara eksplisit pada UI dan dokumentasi.

### Teknologi pembaca

PhpSpreadsheet dapat dipertimbangkan untuk file spreadsheet dalam ekosistem PHP/CI4. Kompatibilitas aktual harus diuji pada versi library yang dipilih. File rusak, terenkripsi, terlalu besar, atau menggunakan fitur yang tidak didukung harus ditolak dengan pesan yang jelas.

### Batasan kata 'semua format'

Tidak ada jaminan realistis bahwa satu aplikasi dapat membaca semua format spreadsheet atau semua struktur tanpa aturan. Sistem harus memiliki daftar format yang didukung, validasi isi, batas ukuran, dan mekanisme perluasan reader.

### Format data

Sistem perlu menangani perbedaan pemisah CSV, encoding karakter, format tanggal, pemisah desimal, header ganda, nama kolom kosong, nama kolom berulang, sel gabungan, formula, sheet kosong, dan baris kosong sesuai kemampuan library.

### Pemetaan semantik

Pemetaan otomatis merupakan rekomendasi, bukan kebenaran yang dijamin. Pengguna dapat mengonfirmasi atribut seperti usia, jenis kelamin, diagnosis, unit pelayanan, tanggal kunjungan, ID kunjungan, dan target klasifikasi. Atribut yang tidak cocok harus tetap tersedia sebagai kolom sumber atau ditandai tidak dipakai.

## 11. Model Data dan Entitas yang Disarankan

### users

Menyimpan akun, kredensial dalam bentuk hash, peran, status, dan metadata waktu.

### datasets

Menyimpan ID dataset, pemilik, nama file asli yang telah disanitasi, lokasi penyimpanan internal, format, ukuran, jumlah baris/kolom, status, waktu import, dan status retensi.

### dataset_columns

Menyimpan kolom sumber, posisi, tipe terdeteksi, pemetaan atribut standar, aturan transformasi, dan konfirmasi pengguna.

### dataset_rows / staging

Menyimpan data mentah atau data staging sesuai desain. Untuk struktur dinamis, pertimbangkan staging terkontrol atau penyimpanan JSON; data yang sering ditanya dapat dinormalisasi ke tabel terstruktur.

### patient_visits

Opsional untuk data yang sudah dipetakan ke struktur standar; jangan memaksakan semua dataset ke tabel tetap jika variasinya tidak dapat diwakili dengan aman.

### analysis_runs

Menyimpan dataset, metode, konfigurasi, status, waktu mulai/selesai, versi algoritma, ringkasan, dan pengguna pelaksana.

### analysis_results

Menyimpan ringkasan hasil, referensi artefak, dan metadata output.

### analysis_details

Menyimpan hasil per record seperti cluster/prediksi hanya jika diperlukan dan sesuai kebijakan privasi serta ukuran data.

### model_metrics

Menyimpan metrik evaluasi, metode validasi, ukuran data latih/uji, dan kelas yang dievaluasi.

### audit_logs

Menyimpan aktivitas penting dengan akses terbatas dan tanpa mencatat data pasien sensitif secara berlebihan.

### Catatan desain

Skema akhir harus ditentukan setelah contoh dataset, kebutuhan query, ukuran data, retensi, dan kebijakan privasi dikonfirmasi. Hindari menyimpan data sensitif berulang kali tanpa kebutuhan.

## 12. Persyaratan Antarmuka dan Navigasi

### Menu utama yang disarankan

Dashboard; Dataset/Import Data; Data Preview dan Pemetaan; Preprocessing; Analisis Data Mining; Hasil Analisis; Riwayat Analisis; Laporan; Pengguna dan Pengaturan (sesuai peran).

### Prinsip UX

Antarmuka berbahasa Indonesia, konsisten, responsif, mudah dibaca, dan menampilkan status proses. Gunakan tabel untuk data tabular, grafik untuk pola, serta pesan validasi yang menyebutkan kolom/baris terkait jika memungkinkan.

### Status proses

Tampilkan status seperti Menunggu, Memvalidasi, Perlu Pemetaan, Siap Dianalisis, Sedang Diproses, Berhasil, Gagal, dan Dibatalkan jika mekanisme pembatalan tersedia.

### Keterlacakan

Setiap hasil harus menunjukkan dataset sumber, waktu analisis, metode, fitur yang digunakan, target (untuk klasifikasi), preprocessing, dan parameter utama.

## 13. Persyaratan Nonfungsional

### NFR-01 Keamanan

Gunakan proteksi CSRF, validasi server-side, prepared statements/Query Builder, output escaping, autentikasi yang aman, pembatasan percobaan login sesuai kebutuhan, dan pemeriksaan otorisasi pada setiap endpoint.

### NFR-02 Keamanan file

Simpan file upload di lokasi non-public atau di luar web root bila memungkinkan; buat nama file internal yang acak; batasi ukuran dan ekstensi; validasi konten; hindari eksekusi file upload; bersihkan file sementara.

### NFR-03 Privasi

Minimalkan penggunaan identitas pasien. Terapkan pseudonimisasi atau penghapusan identifier untuk analisis bila tidak diperlukan. Batasi akses, logging, ekspor, dan masa simpan sesuai kebijakan serta ketentuan yang berlaku.

### NFR-04 Integritas

Setiap hasil harus dapat ditelusuri ke dataset, konfigurasi, dan versi proses yang menghasilkan output tersebut.

### NFR-05 Kinerja

Upload dan analisis dataset besar tidak boleh membuat request web macet tanpa batas. Gunakan pagination, proses background/queue jika dibutuhkan, batas sumber daya, dan indikator progres. Nilai target performa ditetapkan setelah pengujian.

### NFR-06 Keandalan

Kegagalan satu proses tidak boleh merusak dataset lain. Status proses harus konsisten dan kegagalan dapat dilacak.

### NFR-07 Skalabilitas

Pisahkan parsing file, preprocessing, eksekusi algoritma, dan penyajian hasil agar dapat ditingkatkan secara bertahap.

### NFR-08 Usability

Pengguna harus memahami format yang diterima, masalah validasi, atribut yang dipilih, parameter, dan makna keluaran tanpa harus melihat log teknis.

### NFR-09 Maintainability

Gunakan struktur MVC CI4, validasi terpusat, service untuk logika bisnis/algoritma, migration dan seeder, pengujian otomatis, serta dokumentasi konfigurasi.

### NFR-10 Kompatibilitas

Diuji pada versi PHP, CI4, database, browser, dan library spreadsheet yang ditetapkan dalam dokumentasi instalasi.

## 14. Teknologi dan Arsitektur yang Disarankan

### Backend

CodeIgniter 4 untuk routing, controller, model, validasi, autentikasi, akses database, dan layanan aplikasi.

### Database

MySQL atau MariaDB. Gunakan migration untuk skema dan indeks sesuai kebutuhan.

### Spreadsheet

PhpSpreadsheet untuk membaca dan menulis format yang didukung; verifikasi dukungan berdasarkan versi library.

### Frontend

Bootstrap 5 untuk layout responsif; Chart.js untuk grafik; DataTables atau tabel sejenis untuk pencarian dan pagination bila diperlukan.

### Algoritma

Dapat diimplementasikan di PHP melalui service yang terpisah, atau menggunakan layanan Python yang dipanggil CI4. Pilihan arsitektur perlu diputuskan berdasarkan kebutuhan akademik, keahlian tim, pustaka, deployment, dan transparansi implementasi.

### Pemisahan komponen

Pisahkan Upload/Import Service, Schema Detection & Mapping Service, Preprocessing Service, KMeans Service, C45 Service, NaiveBayes Service, Reporting Service, dan Audit Service.

### Konfigurasi

Simpan rahasia di environment/config yang tidak masuk version control. Jangan menaruh kredensial database atau secret di kode publik.

## 15. Pelaporan dan Interpretasi

### Isi laporan

Identitas proses non-sensitif, nama dataset, periode data jika valid, jumlah baris yang diproses/dikecualikan, ringkasan kualitas, transformasi, metode, parameter, fitur, target klasifikasi, hasil, metrik, peringatan, waktu proses, dan versi implementasi.

### K-Means

Sajikan ukuran cluster, profil fitur tiap cluster, grafik distribusi, dan metode evaluasi internal jika dihitung. Jelaskan bahwa label cluster bersifat arbitrer.

### C4.5

Sajikan pohon keputusan atau aturan IF–THEN, target kelas, fitur yang digunakan, dan metrik pada data uji/validasi.

### Naive Bayes

Sajikan distribusi prediksi, probabilitas jika tersedia, target kelas, fitur, dan metrik evaluasi.

### Keterbatasan

Laporan harus menyebutkan keterbatasan data, missing values, ketidakseimbangan kelas, ukuran sampel, atau evaluasi yang tidak tersedia bila relevan.

## 16. Privasi, Etika, dan Tata Kelola Data

### Perlindungan data pasien

Data rekam medis dapat mengandung informasi kesehatan pribadi. Gunakan data hanya untuk tujuan yang sah, batasi akses sesuai kebutuhan, dan ikuti peraturan serta kebijakan institusi yang berlaku.

### Minimisasi

Jangan gunakan nama lengkap, alamat, nomor identitas, atau nomor rekam medis sebagai fitur analitik kecuali ada kebutuhan yang jelas dan dasar yang sah. Untuk analisis pola, identifier sebaiknya dipisahkan atau dipseudonimkan.

### Bukan perangkat diagnosis

e-Damika merupakan alat analitik dan pendukung perencanaan, bukan sistem untuk menetapkan diagnosis, memilih terapi, atau menggantikan tenaga kesehatan.

### Penggunaan hasil

Hasil harus ditinjau dengan pemahaman domain. Korelasi atau cluster tidak otomatis membuktikan sebab-akibat. Model klasifikasi harus diuji dan tidak boleh dianggap akurat hanya karena berhasil dilatih.

### Retensi dan berbagi

Tetapkan siapa yang boleh mengunduh, membagikan, menghapus, dan mempertahankan dataset serta laporan. Terapkan kebijakan penghapusan untuk file sementara dan hasil turunan.

## 17. Pengujian dan Kriteria Penerimaan

### AT-01 Import berhasil

Diberikan file XLSX/XLS/CSV yang valid dan didukung, sistem membaca struktur yang didukung serta menampilkan pratinjau.

### AT-02 Format tidak didukung

Diberikan file yang tidak didukung atau rusak, sistem menolak dengan pesan yang jelas dan tidak membuat dataset setengah jadi.

### AT-03 Kolom berbeda

Diberikan dua file dengan nama/urutan kolom berbeda tetapi semantik yang bisa dipetakan, pengguna dapat memetakan atribut dan menyimpan konfigurasi masing-masing.

### AT-04 Pemetaan ambigu

Jika beberapa kolom sama-sama mungkin menjadi atribut tertentu, sistem meminta konfirmasi dan tidak memilih diam-diam.

### AT-05 Nilai kosong/tipe salah

Sistem melaporkan masalah kualitas dan menerapkan hanya strategi preprocessing yang dipilih/dikonfigurasi.

### AT-06 K-Means

Pada dataset yang memenuhi persyaratan fitur, sistem menghasilkan label cluster, jumlah anggota, dan ringkasan yang berasal dari hasil aktual.

### AT-07 C4.5 tanpa target

Jika target kelas tidak tersedia atau tidak valid, sistem mencegah eksekusi dan menjelaskan persyaratannya.

### AT-08 Naive Bayes

Jika target dan fitur valid tersedia, sistem menghasilkan prediksi/probabilitas sesuai implementasi dan menampilkan evaluasi bila tersedia.

### AT-09 Hasil dinamis

Dataset berbeda atau parameter berbeda menghasilkan output yang dihitung ulang; hasil lama tidak ditampilkan seolah-olah merupakan hasil baru.

### AT-10 Laporan

File laporan berisi metode, dataset, parameter, hasil, dan peringatan yang konsisten dengan proses terpilih.

### AT-11 Hak akses

Pengguna tanpa izin tidak dapat mengakses dataset, hasil, endpoint, atau file melalui URL langsung.

### AT-12 Keterlacakan

Riwayat menyimpan informasi yang cukup untuk mengidentifikasi dataset, konfigurasi, waktu, status, dan metrik proses.

### AT-13 Privasi

Identifier pasien yang tidak dibutuhkan tidak ditampilkan pada grafik atau laporan analitik standar.

### AT-14 Ketahanan

Kesalahan import atau analisis tidak menyebabkan kebocoran stack trace, korupsi data, atau gangguan pada dataset lain.

## 18. Risiko dan Mitigasi

### Variasi data terlalu luas

Mitigasi: daftar format yang didukung, deteksi schema, mapping yang dapat diedit, preview, dan validasi eksplisit.

### Kualitas data rendah

Mitigasi: laporan kualitas sebelum analisis, aturan validasi, strategi missing values yang terdokumentasi, dan peringatan.

### Pemilihan metode yang tidak tepat

Mitigasi: jelaskan perbedaan clustering dan klasifikasi serta validasi prasyarat metode.

### Kebocoran data saat evaluasi

Mitigasi: pisahkan data latih/uji sebelum transformasi yang belajar dari data; lakukan fitting preprocessing hanya pada data latih dalam evaluasi.

### Privasi rekam medis

Mitigasi: kontrol akses, minimisasi/pseudonimisasi, penyimpanan aman, audit, retensi, dan pembatasan ekspor.

### Performa pada dataset besar

Mitigasi: batas upload, pagination, job background, indeks, monitoring, dan uji beban.

### Ekspektasi 'semua format'

Mitigasi: komunikasikan daftar format yang diuji; dukungan tambahan dibuat melalui reader atau proses konversi yang aman.

## 19. Prioritas Pengembangan

### P0 — Fondasi

Struktur CI4, autentikasi, database/migration, layout utama, upload file aman, pratinjau, validasi, dan pemetaan kolom.

### P1 — Analisis inti

Preprocessing yang terdokumentasi, K-Means, pemilihan target dan klasifikasi C4.5 serta Naive Bayes, serta penyimpanan riwayat.

### P2 — Hasil dan laporan

Dashboard dinamis, grafik, metrik evaluasi, ekspor laporan, dan audit.

### P3 — Penyempurnaan

Queue/background job, dukungan format tambahan, pemetaan yang lebih cerdas, role tambahan, optimasi performa, dan pengujian beban.

### Catatan prioritas

Urutan akhir dapat disesuaikan setelah kebutuhan kampus/pembimbing, contoh dataset, dan keterbatasan deployment dipastikan.

## 20. Pertanyaan yang Perlu Diputuskan Sebelum Implementasi

### Arsitektur algoritma

Apakah ketiga algoritma harus diimplementasikan langsung dalam PHP/CI4, atau layanan Python diperbolehkan?

### Dataset contoh

Apa contoh file nyata yang akan menjadi dataset uji, dan apakah data tersebut telah dianonimkan?

### Atribut standar

Kolom mana yang harus dikenali sebagai atribut standar, dan apakah ada atribut tambahan yang wajib didukung?

### Target klasifikasi

Target apa yang ingin diprediksi dengan C4.5 dan Naive Bayes? Apakah label tersebut tersedia dalam data historis?

### Ukuran data

Berapa ukuran file dan jumlah baris tipikal/maksimum yang perlu ditangani?

### Format laporan

Apakah keluaran wajib XLSX, PDF, CSV, atau semuanya?

### Pengguna

Apakah aplikasi hanya dipakai admin atau juga operator/analis dan pembaca laporan?

### Kebijakan data

Di mana aplikasi akan di-host, siapa pemilik data, dan berapa lama dataset/hasil harus disimpan?

## 21. Definisi Selesai (Definition of Done)

### Kriteria akhir

Proyek dianggap siap untuk penerimaan awal apabila: seluruh alur inti dari upload sampai laporan dapat digunakan; format yang dinyatakan didukung telah diuji; pemetaan kolom dan validasi berfungsi; ketiga metode berjalan sesuai prasyarat; hasil dan grafik bersumber dari proses aktual; klasifikasi memiliki evaluasi yang sesuai; hak akses dan penanganan file diuji; dokumentasi instalasi dan penggunaan tersedia; serta tidak ada masalah kritis yang diketahui.

## 22. Glosarium

### Dataset

Kumpulan baris dan kolom yang diimpor untuk dianalisis.

### Preprocessing

Langkah menyiapkan data, termasuk validasi, pembersihan, encoding, dan scaling.

### Clustering

Pengelompokan data tanpa label target yang telah ditentukan.

### Klasifikasi

Prediksi label kelas berdasarkan contoh data berlabel.

### Target kelas

Kolom label yang hendak diprediksi oleh model klasifikasi.

### Fitur/atribut

Kolom yang dipakai sebagai masukan analisis.

### Confusion matrix

Tabel perbandingan kelas aktual dan prediksi.

### Pseudonimisasi

Penggantian identifier langsung dengan identifier pengganti sehingga identitas tidak langsung terlihat, dengan tetap memperhatikan risiko pengaitan kembali.

## Lampiran A — Contoh atribut standar

| Atribut standar | Contoh nama kolom sumber | Tipe/pertimbangan |
|---|---|---|
| Usia | `usia`, `umur_pasien` | Numerik; validasi rentang dan missing values |
| Jenis kelamin | `jenis_kelamin`, `gender` | Kategorikal; normalisasi label |
| Diagnosis | `diagnosis`, `nama_penyakit` | Kategorikal/teks; pertimbangkan kategori dan privasi |
| Unit pelayanan | `unit_pelayanan`, `poli` | Kategorikal |
| Tanggal kunjungan | `tanggal_kunjungan`, `tgl_kunjungan` | Tanggal; format perlu dideteksi/diuji |
| ID kunjungan | `id_kunjungan`, `visit_id` | Identifier; bukan fitur secara otomatis |
| Target klasifikasi | Bergantung tujuan analisis | Kategorikal dan wajib tersedia untuk C4.5/Naive Bayes |

## Lampiran B — Contoh status dataset

1. Diunggah
2. Sedang divalidasi
3. Perlu pemetaan
4. Memiliki peringatan kualitas
5. Siap dianalisis
6. Diarsipkan/dihapus sesuai kebijakan

---

**Akhir dokumen PRD e-Damika.**
