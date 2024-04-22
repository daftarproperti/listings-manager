<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Newsreader:ital,opsz,wght@0,6..72,200..800;1,6..72,200..800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.css" rel="stylesheet" />

    <title>Daftar Properti - Halaman tidak Ditemukan</title>

    @vite('resources/css/app.css')
</head>

<body>
    <div class="min-h-screen mx-auto w-full font-inter">
        @include('partials.header')
        <main>
            <div class="max-w-6xl mx-auto px-4 py-10 bg-white min-h-96 text-center">
                <h1 class="font-newsreader text-4xl pt-0 md:pt-5 mb-10 tracking-wide font-medium">Halaman tidak ditemukan</h1>
                <p class="mb-10">
                    Maaf halaman yang Anda cari tidak ditemukan.<br />
                    Silahkan kembali ke beranda.
                </p>
                <p>
                <a href="/" class="bg-blue-500 text-white text-sm rounded-lg py-2 px-7 ml-0 inline-block border border-solid border-blue-500 hover:bg-white hover:text-blue-500 mb-5 md:mb-0">Beranda</a>
                </p>
            </div>
        </main>
        @include('partials.footer')
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js"></script>
</body>

</html>
