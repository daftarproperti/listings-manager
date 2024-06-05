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
    <h1>Kebijakan Privasi</h1>

    <p>Daftar Properti berkomitmen untuk melindungi dan menghormati privasi Anda.
    Kebijakan Privasi ini menjelaskan bagaimana kami mengumpulkan, menggunakan, dan melindungi informasi pribadi Anda.</p>

    <h2>1. Informasi yang Kami Kumpulkan</h2>
    <p>Kami dapat mengumpulkan dan memproses informasi berikut:</p>
    <ul>
        <li>Informasi yang Anda berikan saat mendaftar, seperti nama, alamat email, dan nomor telepon.</li>
        <li>Detail properti yang Anda daftarkan, termasuk foto dan deskripsi properti.</li>
        <li>Informasi tentang penggunaan Anda atas platform kami, seperti log aktivitas dan preferensi pencarian.</li>
    </ul>

    <h2>2. Penggunaan Informasi</h2>
    <p>Informasi yang kami kumpulkan digunakan untuk:</p>
    <ul>
        <li>Menyediakan, mengoperasikan, dan memelihara platform kami.</li>
        <li>Menghubungi Anda untuk tujuan pemasaran, layanan pelanggan, atau informasi lain yang relevan.</li>
        <li>Meningkatkan layanan dan fitur platform kami.</li>
    </ul>

    <h2>3. Berbagi Informasi Anda</h2>
    <p>Kami tidak akan menjual atau menyewakan informasi pribadi Anda kepada pihak ketiga. Kami dapat berbagi informasi Anda dengan:</p>
    <ul>
        <li>Penyedia layanan pihak ketiga yang membantu operasional platform kami.</li>
        <li>Pihak berwenang jika diwajibkan oleh hukum atau untuk melindungi hak dan keselamatan kami.</li>
    </ul>

    <h2>4. Keamanan Informasi</h2>
    <p>Kami mengambil langkah-langkah yang wajar untuk melindungi informasi pribadi Anda dari akses, penggunaan, atau pengungkapan yang tidak sah. Namun, kami tidak dapat menjamin keamanan mutlak dari informasi yang Anda kirimkan secara online.</p>

    <h2>5. Hak Anda</h2>
    <p>Anda memiliki hak untuk mengakses, memperbarui, atau menghapus informasi pribadi Anda yang kami simpan. Jika Anda ingin menggunakan hak ini, silakan hubungi kami melalui detail kontak yang tersedia di platform kami.</p>

    <h2>6. Perubahan Kebijakan Privasi</h2>
    <p>Kami dapat memperbarui Kebijakan Privasi ini dari waktu ke waktu. Kami akan memberi tahu Anda tentang perubahan apa pun dengan memposting kebijakan privasi baru di halaman ini. Anda disarankan untuk meninjau Kebijakan Privasi ini secara berkala untuk setiap perubahan.</p>

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
