<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Newsreader:ital,opsz,wght@0,6..72,200..800;1,6..72,200..800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.css" rel="stylesheet" />

    <title>Daftar Properti - Beranda</title>

    @vite('resources/css/app.css')
</head>

<body>
    <div class="min-h-screen mx-auto w-full font-inter">
        @include('partials.header')
        <main>
            <section style="background-image: url('{{ asset('/images/background.png') }}');" class="w-full min-h-40 md:min-h-80 bg-cover bg-center pt-10 pb-5 md:pt-16 md:pb-14">
                <div class="max-w-6xl mx-auto px-2 text-center container mb-6 md:mb-24">
                    <h1 class="text-white font-normal font-newsreader text-4xl leading-snug md:text-5xl md:leading-tight mb-5">Membangun ekosistem transparan<br />real estate Indonesia</h1>
                    <p class="text-white text-base mb-5">Daftar terbuka properti dijual/disewakan di seluruh Indonesia</p>
                    <div class="mt-5 md:mt-10">
                        <a href="/app" class="bg-blue-500 text-white text-sm rounded-lg py-2 px-7 ml-0 inline-block border border-solid border-blue-500 hover:bg-white hover:text-blue-500 mb-5 md:mb-0">Buka Aplikasi</a>
                        @if (false)
                        <a href="/about" class="bg-white text-sm rounded-lg py-2 px-7 ml-0 md:ml-3 inline-block border border-solid border-blue-500 text-blue-500 hover:bg-blue-500 hover:text-white">Pelajari Selengkapnya</a>
                        @endif
                    </div>
                </div>
            </section>
            <section class="max-w-6xl mx-auto">
                <div class="md:flex justify-between gap-0 md:columns-3 md:gap-3 px-6 mb-12 md:px-2 md:mb-8 -mt-1">
                    <div class="bg-blue-50 rounded-lg p-6 -mt-4 w-full md:w-1/3">
                        <img src="/images/kolaborasi_icon.svg" alt="Kolaborasi" />
                        <h3 class="font-newsreader text-2xl mt-1 mb-2">Kolaborasi</h3>
                        <p class="text-gray-500 text-sm">Kami mendorong kolaborasi para anggota dengan menyediakan fasilitas teknologi untuk berbagi listing secara terpusat.</p>
                    </div>
                    <div class="bg-green-50 rounded-lg p-6 mt-6 md:-mt-4 w-full md:w-1/3">
                        <img src="/images/transparansi_icon.svg" alt="Kolaborasi" />
                        <h3 class="font-newsreader text-2xl mt-1 mb-2">Transparansi</h3>
                        <p class="text-gray-500 text-sm"><i>Source code</i> tersedia untuk publik, data listing dapat digunakan oleh semua pihak termasuk individu, agen, atau bisnis teknologi.</p>
                    </div>
                    <div class="bg-yellow-50 rounded-lg p-6 mt-6 md:-mt-4 w-full md:w-1/3">
                        <img src="/images/kualitas_icon.svg" alt="Kolaborasi" />
                        <h3 class="font-newsreader text-2xl mt-1 mb-2">Kualitas</h3>
                        <p class="text-gray-500 text-sm">Memastikan data yang tersedia untuk publik seakurat mungkin sehingga menciptakan budaya saling melengkapi antara pengguna.</p>
                    </div>
                </div>
                <div class="flex flex-wrap md:flex-nowrap justify-between columns-2 md:gap-2 px-2 mb-20 md:mb-16 pt-1">
                    <div class="w-full py-4 px-2 order-2 md:order-1">
                        <div class="flex items-center pt-2 md:pt-4 pl-2">
                            <div class="w-1 h-6 bg-blue-400 -mt-9 md:-mt-5"></div>
                            <h2 class="font-newsreader text-4xl ml-5 pt-0 md:pt-5 mb-5">Cara Kerja Daftar Properti</h2>
                        </div>
                        <article class="prose p-6 shadow-lg rounded-lg bg-blue-50 max-w-none">
                            <h3>1. Daftarkan properti Anda untuk dijual/disewakan</h3>
                            <p>
                            Tidak seperti <i>marketplace</i>, properti yang terdaftar kami pastikan adalah <i>unique</i>, transparan,
                            dan berkualitas, sehingga pemasar maupun calon pembeli/penyewa dapat dengan mudah menemukan.
                            </p>
                            <h3>2. Pemasar memasarkan properti Anda dengan transparansi terjaga</h3>
                            <p>
                            Properti Anda yang terdaftar boleh dipakai oleh pemasar dengan bebas.
                            Tidak seperti <i>marketplace</i>, data listing Anda adalah data terbuka, sehingga properti anda
                            lebih cepat tersebar dan ditemukan pembeli/penyewa.
                            </p>
                            <h3>3. Properti terjual/tersewa</h3>
                            <p>
                            Setelah properti terjual/tersewa, lakukan <i>update</i> di Daftar Properti sehingga pemasaran dapat berhenti.
                            Data penjualan/penyewaan akan disediakan secara terbuka untuk umum (secara anonim, privasi terjaga)
                            sehingga industri jual/beli properti menjadi lebih efisien.
                            </p>
                        </article>
                    </div>
                </div>
            </section>
            @if(false) {-- Not used yet --}
            <section class="bg-blue-200 py-8">
                <div class="container mx-auto max-w-6xl">
                    <div class="flex items-center pt-1 md:pt-4 px-6 md:px-8 md:pl-20 mb-2 md:pb-0">
                        <div class="w-1 h-6 bg-blue-400 -mt-16 md:-mt-5"></div>
                        <h2 class="font-newsreader text-4xl ml-5 pt-5 mb-2 leading-normal md:leading-tight">Prinsip Daftar Properti</h2>
                    </div>
                    <div class="flex flex-wrap justify-between gap-4 px-6 pt-1 pb-4 md:pb-4 md:pt-8 md:px-2 mb-0">
                        <figure class="md:flex-1 md:basis-1/3 max-w-full md:max-w-[calc(33%-1rem)] mb-2 md:mb-6">
                            <img src="/images/prinsip_1.png" alt="Prinsip Daftar Properti"/>
                            <figcaption class="text-gray-500 text-base mt-3 font-light">Pemilik properti berhak mempublikasikan listing yang berkualitas dan transparan di pasar real estate.</figcaption>
                        </figure>
                        <figure class="md:flex-1 md:basis-1/3 max-w-full md:max-w-[calc(33%-1rem)] mb-2 md:mb-6">
                            <img src="/images/prinsip_2.png" alt="Prinsip Daftar Properti" />
                            <figcaption class="text-gray-500 text-base mt-3 font-light">Pembeli properti berhak melihat listing yang berkualitas dan transparan di pasar real estate.</figcaption>
                        </figure>
                        <figure class="md:flex-1 md:basis-1/3 max-w-full md:max-w-[calc(33%-1rem)] mb-2 md:mb-6">
                            <img src="/images/prinsip_3.png" alt="Prinsip Daftar Properti" />
                            <figcaption class="text-gray-500 text-base mt-3 font-light">Agen/Broker berhak menjalankan perannya untuk mempelajari kondisi pasar, mempertemukan pemilik dan pembeli, bekerja sama antar agen, tanpa mengorbankan kualitas dan transparansi data.</figcaption>
                        </figure>
                        <figure class="md:flex-1 md:basis-1/3 max-w-full md:max-w-[calc(33%-1rem)] mb-2 md:mb-6">
                            <img src="/images/prinsip_4.png" alt="Prinsip Daftar Properti" />
                            <figcaption class="text-gray-500 text-base mt-3 font-light">Masyarakat luas berhak teredukasi terhadap kondisi pasar real estate secara transparan, termasuk data sejarah jual beli properti, tanpa mengorbankan privasi.</figcaption>
                        </figure>
                        <figure class="md:flex-1 md:basis-1/3 max-w-full md:max-w-[calc(33%-1rem)] mb-2 md:mb-6">
                            <img src="/images/prinsip_5.png" alt="Prinsip Daftar Properti" />
                            <figcaption class="text-gray-500 text-base mt-3 font-light">Instansi pemerintah berhak mendapatkan visibilitas/kejelasan terhadap transaksi real estate yang terjadi sehingga dapat memaksimalkan kebijakan publik.</figcaption>
                        </figure>
                        <figure class="md:flex-1 md:basis-1/3 max-w-full md:max-w-[calc(33%-1rem)] mb-2 md:mb-6">
                            <img src="/images/prinsip_4.png" alt="Prinsip Daftar Properti" />
                            <figcaption class="text-gray-500 text-base mt-3 font-light">Bisnis berhak mendapatkan teknologi yang memfasilitasi pertumbuhan industri dengan efektif.</figcaption>
                        </figure>
                    </div>
                </div>
            </section>
            <section class="bg-blue-700 py-8">
                <div class="container mx-auto max-w-6xl md-4 md:mb-6">
                    <div class="flex items-center pt-3 md:pl-20 mb-4">
                        <div class="w-20 h-0.5 bg-blue-400 ml-6"></div>
                        <h3 class="text-sm md:text-xl ml-3 mb-2 pt-1 text-blue-300 font-light">Rika, Jual Rumah di Tangerang</h3>
                    </div>
                    <div class="font-newsreader text-3xl md:text-4xl text-blue-50 font-light px-4 md:px-24 mx-2 leading-normal md:leading-tight">
                        <q>Sangat terbantu dengan adanya Daftar Properti. <br /> Rumahku langsung terjual dengan harga yang sesuai harapan keluarga!</q>
                    </div>
                </div>
            </section>
            <section class="bg-white">
                <div class="container mx-auto max-w-6xl py-6 md:py-20">
                    <div class="flex flex-wrap justify-between gap-3 px-6 md:px-2 mb-5 md:mb-0">
                        <div class="max-w-full md:max-w-[calc(33%-1rem)] flex-1 mb-2 md:mb-0">
                            <img src="/images/rumah_kunci.png" alt="Rumah Daftar Properti" class="w-full"/>
                        </div>
                        <div class="md:max-w-[calc(67%-1rem)]">
                            <h3 class="text-3xl lg:text-5xl font-light lg:mt-1 mb-4 leading-relaxed lg:leading-tight text-slate-700">Kita berfokus untuk membantu Anda agar jual beli properti makin mudah dan transparan</h3>
                            <p><a href="/app" class="bg-blue-500 text-sm text-white rounded-lg py-2 px-6 ml-0 inline-block border border-solid border-blue-500 hover:bg-white hover:text-blue-500 mr-2">Buka Aplikasi</a></p>
                        </div>
                    </div>
                </div>
            </section>
            @endif
        </main>
        @include('partials.footer')
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js"></script>
</body>

</html>
