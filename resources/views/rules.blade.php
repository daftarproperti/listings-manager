<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Newsreader:ital,opsz,wght@0,6..72,200..800;1,6..72,200..800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.css" rel="stylesheet" />

    <title>Daftar Properti - Peraturan</title>

    @vite('resources/css/app.css')
</head>

<body>
    <div class="min-h-screen mx-auto w-full font-inter">
        @include('partials.header')
        <main>
            <div class="max-w-6xl mx-auto px-4 py-12 bg-white min-h-96">
                <article class="prose">
<x-markdown>
# Peraturan Daftar Properti

## Pendaftaran Listing

1. Properti yang dijual/disewakan boleh didaftarkan oleh pemilik rumah maupun pihak lain (misal anggota keluarga atau
   agen/broker), asalkan pihak tersebut terhubung secara langsung dengan pemilik rumah dan mempunyai persetujuan yang
   jelas dengan pemilik rumah. Setiap pendaftaran untuk properti dijual/disewakan ini disebut dengan **Listing**.

2. Untuk setiap properti (rumah/tanah/gudang/unit apartement/dll), hanya ada 1 Listing yang bisa aktif dalam saat
   bersamaan. Ini berarti tidak akan ada 2 Listing yang duplikat (mengacu pada properti yang sama) di suatu saat.

3. Untuk setiap properti, pemilihan Listing aktif dilakukan secara *first-come first-served*. Artinya Listing yang lebih
   awal didaftarkan dan terverifikasi akan menjadi Listing yang aktif untuk properti tersebut. Listing-listing lain
   untuk properti yang sama boleh mengantri di *waitlist* dengan urutan *first-come first-served* juga.

4. Periode berlaku aktif suatu listing adalah 6 bulan. Setelah periode berlaku habis, pendaftar boleh mengajukan
   perpanjangan jika pemilik rumah menyetujui secara langsung, atau jika tidak ada Listing lain di *waitlist*. Jika
   tidak, Listing terdepan yang ada di *waitlist* akan menggantikan listing aktif tersebut. Aturan poin ini berlaku lagi
   ketika periode berikutnya berakhir.

5. Pemilik rumah dapat melapor jika ada Listing yang merugikan, misalnya Listing berkualitas rendah, tidak transparan,
   atau menjerumuskan. Kami akan tinjau laporan tersebut dan jika benar maka Listing tersebut akan diakhiri periode
   aktifnya.

## Verifikasi Listing

Sebuah Listing disebut terverifikasi jika memenuhi syarat-syarat di bawah ini:

1. Listing mencantumkan alamat lengkap yang dapat mengidentifikasi properti tersebut secara *unique*. Misalnya, untuk
   rumah alamat harus mencantumkan nama jalan, nomor rumah, dan kota/kabupaten. Jika alamatnya masih ambigu,
   kecamatan/kelurahan/RT/RW perlu disebutkan. Untuk unit apartemen, alamat lengkap apartemen dan nomor unit harus
   dicantumkan.

2. Listing mengandung foto-foto yang cukup untuk mengidentifikasi properti yang diacu. Biasanya ini adalah foto tampak
   depan dan foto dalam setiap ruangan. Foto-foto yang disediakan harus berkualitas (tidak buram atau terpotong).

3. Admin Daftar Properti akan mencari dan menentukan titik di peta (koordinat latitude dan longitude) untuk
   memverifikasi Listing. Oleh karena itu, pendaftar dianjurkan menyediakan titik di peta untuk membantu mempercepat
   verifikasi.

Syarat-syarat di atas diperlukan untuk memastikan kualitas Listing-listing di Daftar Properti sehingga pemasar dapat
memberikan informasi yang memuaskan untuk calon pembeli/penyewa.

Untuk lebih detil, silahkan kunjungi halaman [*checklist*](/checklist).

## Persetujuan Imbalan

Setiap Listing dianjurkan untuk menyetujui akan memberikan imbalan 0,5% (jual) atau 1% (sewa) untuk
Pemasar Daftar Properti yang berhasil merujuk pembeli/penyewa. Walaupun Listing boleh didaftarkan
tanpa persetujuan komisi, Pemasar akan lebih terpacu untuk merujuk pembeli untuk Listing-listing
yang menyetujui imbalan.

## Pemasar

Daftar Properti mengelola pendaftaran Listing, tetapi tidak melakukan pemasaran secara langsung. Akan tetapi,
Listing-listing di Daftar Properti bersifat terbuka, boleh menjadi sumber, ditampilkan, ataupun di-indeks oleh pihak
ketiga untuk tujuan pemasaran.

Siapapun, baik perorangan maupun bisnis, boleh mendaftar menjadi Pemasar di Daftar Properti. Pemasar berhak menyumber
data Listing dari Daftar Properti dan memasarkan Listing-listing tersebut dengan cara mereka sendiri.

Untuk mendapat keuntungan "*referral tracking*" Daftar Properti, pemasar wajib mengikuti protokol
"*verifiable referral logging*" sehingga di setiap closing/deal, Daftar Properti dapat dengan adil menentukan Pemasar
mana yang adalah perujuk dari sebuah closing. Pemasar yang telah ditentukan adalah perujuk dari pembeli/penyewa berhak
mendapatkan imbalan sesuai yang disetujui oleh Pendaftar Listing (0,5% transaksi untuk jual, 1% untuk transaksi sewa).

Jika ada lebih dari 1 Pemasar yang menjadi perujuk, maka imbalan akan dibagi rata untuk masing-masing Pemasar.

Daftar Properti menggunakan teknologi yang terbuka dan dapat diaudit untuk memastikan referral tracking dipastikan adil
dan tidak mungkin dimanipulasi.

Untuk penjelasan teknikal bagaimana teknologi terbuka kami dapat menjamin keadilan, transparansi, dan privasi, baca Daftar
Properti [Referral Tracking Whitepaper](/whitepaper).

Untuk Pemasar perorangan, gunakan jasa dari [Daftar Properti Pro](/daftar-properti-pro) atau jasa pihak ketiga lainnya.

Untuk Pemasar teknikal (*search engine*, situs internet), baca Daftar Properti [Referral Tracking Integration Guide](/referral-tracking-integration-guide).
</x-markdown>

                </article>
            </div>
        </main>
        @include('partials.footer')
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js"></script>
</body>

</html>
