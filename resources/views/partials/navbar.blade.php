<nav class="space-x-2 sm:flex-grow md:flex flex-auto">
    <div class="max-w-screen-xl flex flex-wrap justify-between py-4 px-4 md:px-2">
        <a href="/" class="flex items-center space-x-3 rtl:space-x-reverse">
            <img src="/images/logo_type.svg" class="h-2.5 md:h-3 lg:h-3.5" alt="Daftar Properti Logo" />
        </a>
        <button type="button" class="border bg-white border-solid border-blue-500 flex items-center py-1 px-2.5 min-w-20 h-9 justify-center rounded-lg md:hidden hover:bg-gray-100 " aria-controls="navbar-default" aria-expanded="false">
            <a href="/app">
            <span class="text-md text-blue-500 ml-1">Buka Aplikasi</span>
            </a>
        </button>
        @if(false)
        <div class="hidden w-full ml-3 top-20 right-0 bg-blue-50 absolute md:block md:bg-transparent md:w-auto md:relative md:top-0 md:right-auto" id="navbar-default">
            <ul class="flex flex-col p-4 mt-0 md:flex-row md:space-x-5 rtl:space-x-reverse">
                <li>
                    <a href="/mission" class="block py-2 px-3 font-medium text-sm lg:text-base text-blue-500 hover:bg-gray-100 md:hover:bg-transparent md:border-0 md:hover:text-blue-700 md:p-0 md:active:text-blue-700">Mission</a>
                </li>
                <li>
                    <a href="/community" class="block py-2 px-3 font-medium text-sm lg:text-base text-blue-500 hover:bg-gray-100 md:hover:bg-transparent md:border-0 md:hover:text-blue-700 md:p-0 md:active:text-blue-700">Community</a>
                </li>
            </ul>
        </div>
        @endif
    </div>
</nav>

