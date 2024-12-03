<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Newsreader:ital,opsz,wght@0,6..72,200..800;1,6..72,200..800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.css" rel="stylesheet" />

    <title>Daftar Properti - Checklist Verifikasi Listing</title>

    @vite('resources/css/app.css')
</head>

<body>
    <div class="min-h-screen mx-auto w-full font-inter">
        @include('partials.header')
        <main>
            <div class="max-w-6xl mx-auto px-4 pb-12 pt-4 bg-white min-h-96">
                <article class="prose">
<x-markdown>
# *Checklist* Verifikasi Listing

Semua listing untuk masuk ke Daftar Properti harus lolos *review* dengan tujuan:

1. Memuaskan calon pembeli/penyewa dan agen yang mencari properti.
2. Memproteksi pendaftar (agen/pemilik) dari listing lain yang duplikat.
3. Memastikan pemasar dapat menjadi perujuk secara *fair*.

Oleh karena itu, setiap listing harus lolos *checklist* berikut:

1. Harus menyertakan alamat lengkap. Aturan ini perlu supaya kami dapat memproteksi pendaftar dari listing lain yang duplikat.
   * jika ada nomor rumah/bangunan, harus dicantumkan
   * jika tidak ada nomor rumah/bangunan, harus menyertakan ancer-ancer yang membantu identifikasi properti
   * untuk apartemen, harus ada nomor lantai dan unit
   * untuk perumahan, harus ada nomor blok dan unit

   Jika tidak yakin apakah alamat sudah sesuai kriteria Daftar Properti, pastikan alamat dapat menjawab **semua** berikut:
   * jika alamat ini menjadi tujuan pengiriman surat/paket, apakah saya dapat menerima surat/paket tersebut?
   * jika saya menggunakan alamat ini untuk mengundang tamu, apakah dia dapat menuju properti saya tanpa tersesat?
   * apakah alamat ini cukup jelas, sehingga Daftar Properti dapat melindungi saya dari duplikat agen lain yang akan mendaftar properti yang sama?

1. Harus ada foto minimal 1, yaitu foto yg bisa untuk mengidentifikasi properti yang dijual/disewakan. Misalnya:
   * untuk rumah, foto tampak depan
   * untuk apartemen, foto ruang utama
   * untuk tanah, foto tanah yang terlihat dari jalan

1. Deskripsi atau gambar tidak boleh mengandung nomer kontak pendaftar (agen/pemilik properti).

   Untuk memastikan jaringan pemasar Daftar Properti terdorong untuk mencarikan pembeli/penyewa, semua pembukaan
   nomor kontak akan melalui sistem Daftar Properti sehingga tidak perlu dan tidak boleh disertakan nomor kontak di
   deskripsi atau gambar.

1. Koordinat harus ada dan harus cocok dengan alamat dan foto.
   * koordinat harus dapat diverifikasi di Google Street View dan cocok dengan foto
   * jika tidak ada di Google Street View, koordinat harus cocok dengan alamat dan/atau ancer-ancer

1. Informasi yang tercantum di deskripsi tidak boleh bertentangan dengan spek (misal KT, KM, LT, LB, dll).

1. Spek "hadap" harus cocok dengan koordinat di peta.

1. Satu listing hanya boleh mengandung satu set spek. Jika terdapat beberapa model properti yang sama dengan spek yang
   berbeda (misal apartemen atau cluster perumahan), maka harus dipisah menjadi beberapa listing.

   Hal ini adalah agar listing dapat ditemukan dengan *filter* secara tepat.

1. Walaupun tidak wajib, disarankan untuk mengisi nama pendaftar di bagian "Akun", sehingga listing terlihat lebih
   profesional (tidak harus nama lengkap, untuk pilihan privasi)

</x-markdown>

                </article>
            </div>
        </main>
        @include('partials.footer')
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js"></script>
</body>

</html>
