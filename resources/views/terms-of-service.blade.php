<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Newsreader:ital,opsz,wght@0,6..72,200..800;1,6..72,200..800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.css" rel="stylesheet" />

    <title>Daftar Properti - Kebijakan Privasi</title>

    @vite('resources/css/app.css')
</head>

<body>
    <div class="min-h-screen mx-auto w-full font-inter">
        @include('partials.header')
        <main>
            <div class="max-w-6xl mx-auto px-4 py-10 bg-white min-h-96">
                <article class="prose">
    <h1>Syarat dan Ketentuan</h1>

    <p>Dengan mengakses atau menggunakan platform Daftar Properti, Anda setuju untuk terikat oleh syarat dan ketentuan berikut:</p>

    <h2>1. Penggunaan Platform</h2>
    <p>Platform ini menyediakan layanan bagi pengguna untuk mendaftarkan dan mencari properti untuk dijual atau disewakan. Anda setuju untuk menggunakan platform ini hanya untuk tujuan yang sah dan sesuai dengan hukum yang berlaku.</p>

    <h2>2. Akun Pengguna</h2>
    <p>Untuk menggunakan beberapa fitur dari platform kami, Anda perlu membuat akun pengguna. Anda bertanggung jawab untuk menjaga kerahasiaan informasi akun Anda dan semua aktivitas yang terjadi di bawah akun Anda.</p>

    <h2>3. Konten Pengguna</h2>
    <p>Anda bertanggung jawab atas konten yang Anda unggah atau bagikan di platform ini, termasuk informasi properti, foto, dan deskripsi. Anda menjamin bahwa konten tersebut tidak melanggar hak pihak ketiga dan sesuai dengan hukum yang berlaku.</p>

    <h2>4. Hak Kekayaan Intelektual</h2>
    <p>Semua hak kekayaan intelektual di platform ini, termasuk desain, teks, grafik, dan perangkat lunak, dimiliki oleh kami atau pemberi lisensi kami. Anda tidak diperkenankan menggunakan materi tersebut tanpa izin tertulis dari pemilik hak kekayaan intelektual terkait.</p>

    <h2>5. Batasan Tanggung Jawab</h2>
    <p>Kami tidak bertanggung jawab atas kerugian atau kerusakan yang timbul dari penggunaan platform ini, termasuk namun tidak terbatas pada kesalahan atau kelalaian dalam konten yang disediakan oleh pengguna lain.</p>

    <h2>6. Perubahan pada Layanan</h2>
    <p>Kami berhak untuk mengubah, menangguhkan, atau menghentikan layanan atau bagian dari layanan kapan saja dengan atau tanpa pemberitahuan.</p>

    <h2>7. Pemutusan Akses</h2>
    <p>Kami dapat, atas kebijakan kami sendiri, menghentikan atau membatasi akses Anda ke platform ini tanpa pemberitahuan sebelumnya jika Anda melanggar syarat dan ketentuan ini atau hukum yang berlaku.</p>

    <h2>8. Hukum yang Berlaku</h2>
    <p>Syarat dan ketentuan ini diatur dan ditafsirkan sesuai dengan hukum Republik Indonesia. Setiap sengketa yang timbul dari atau terkait dengan syarat dan ketentuan ini akan diselesaikan di pengadilan yang berwenang di Indonesia.</p>

    <h2>Kontak Kami</h2>
    <p>Jika Anda memiliki pertanyaan tentang Kebijakan Privasi ini, silakan hubungi kami melalui
        email <a href="mailto:info@daftarproperti.org">info@daftarproperti.org</a>.
    </p>
                </article>
            </div>
        </main>
        @include('partials.footer')
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js"></script>
</body>

</html>
