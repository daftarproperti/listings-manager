<div>
    <h1 class="font-newsreader text-4xl pt-0 md:pt-5 mb-2 tracking-wide font-medium">Contact Us</h1>
    <p class="mb-5">Silahkan isi data di bawah ini.</p>
    <form class="py-2 max-w-xl" action="/">
        <div class="mb-4">
            <label class="block mb-2 text-sm font-medium text-slate-700">Nama</label>
            <input type="text" name="name" class="border border-slate-500 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 "/>
        </div>
        <div class="mb-4">
            <label class="block mb-2 text-sm font-medium text-slate-700">No HP</label>
            <input type="text" name="phone_number" class="border border-slate-500 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 "/>
        </div>
        <div class="mb-4">
            <label class="block mb-2 text-sm font-medium text-slate-700">Alamat Email</label>
            <input type="text" name="email" class="border border-slate-500 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 "/>
        </div>
        <div class="mb-4">
            <label class="block mb-2 text-sm font-medium text-slate-700">Topik</label>
            <div class="flex items-center mb-4">
                <input type="radio" name="topics" checked class="w-4 h-4 border-gray-300 focus:ring-2 focus:ring-blue-300" />
                <label class="block ms-2  text-sm font-medium text-gray-900">Layanan</label>
            </div>
            <div class="flex items-center mb-4">
                <input type="radio" name="topics" class="w-4 h-4 border-gray-300 focus:ring-2 focus:ring-blue-300" />
                <label class="block ms-2  text-sm font-medium text-gray-900">Kerjasama</label>
            </div>
            <div class="flex items-center mb-4">
                <input type="radio" name="topics" class="w-4 h-4 border-gray-300 focus:ring-2 focus:ring-blue-300" />
                <label class="block ms-2  text-sm font-medium text-gray-900">Lainnya</label>
            </div>
        </div>
        <div class="mb-4">
            <label class="block mb-2 text-sm font-medium text-slate-700">Pesan</label>
            <textarea name="message" class="block p-2.5 w-full min-h-[200px] text-sm text-gray-900 rounded-lg border border-slate-500 focus:ring-blue-500 focus:border-blue-500"></textarea>
        </div>
        <div class="mb-4">
            <label class="block mb-2 text-sm font-medium text-slate-700">Dari mana Anda mengetahui tentang Daftar Properti?</label>
            <select name="from" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                <option value="google">Google</option>
                <option value="telegram">Telegram</option>
                <option value="instagram">Instagram</option>
                <option value="facebook">Facebook</option>
                <option value="linkedin">LinkedIn</option>
                <option value="teman">Teman/Kerabat</option>
            </select>
        </div>
        <div class="mb-6">
            <input type="checkbox" name="agreement" class="w-4 h-4 border border-gray-300 rounded bg-gray-50 focus:ring-3 focus:ring-blue-300" required />
            <label class="ml-1 inline mb-2 text-sm font-medium text-slate-700">Apakah Anda menyetujui <a href="/" class="text-blue-500 hover:text-blue-700">Syarat dan Ketentuan</a>?</label>
        </div>
        <div class="mb-4">
            <input type="submit" name="submit" value="Kirim" class="text-white min-w-[200px] bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm w-full sm:w-auto px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800"/>
        </div>
    </form>
</div>