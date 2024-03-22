<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.css" rel="stylesheet" />

    <title>{{ $agent->first_name }} {{ $agent->last_name }} - Daftar Properti</title>

    @vite('resources/css/app.css')
</head>

<body>
    <div class="min-h-screen bg-slate-100">
        <nav class="bg-ribbon-50 border-b border-slate-300">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 h-16 flex items-center gap-3">
                <div class="inline-block h-10 w-10 rounded-full bg-ribbon-100 flex items-center justify-center">
                    <svg class="block h-4" viewBox="0 0 273 315" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path fillRule="evenodd" clipRule="evenodd" d="M0 0V314.481H116.697C117.109 314.481 117.521 314.479 117.931 314.477V239.005H91.7245V314.481H31.4491H0L0.000941753 225.685L104.828 125.792L209.654 225.685V289.887C228.235 277.766 242.88 261.652 253.588 241.542C266.23 217.997 272.55 189.845 272.55 157.087C272.55 124.431 266.23 96.3814 253.588 72.9387C240.948 49.3936 222.964 31.3764 199.633 18.8873C176.407 6.29575 148.71 0 116.543 0H0Z" fill="#0C5AE9" />
                    </svg>
                </div>
                <h1 class="text-xl text-slate-800">{{ $agent->first_name }} {{ $agent->last_name }}</h1>
            </div>
        </nav>
        <div class="max-w-5xl mx-auto">
            <div class="relative">
                <div class="h-24 md:h-64 bg-ribbon-200 md:rounded-b-lg bg-cover bg-center bg-no-repeat" style="background-image: url('/images/header.jpg')"></div>
                <div class="absolute top-12 md:top-44 flex items-center gap-3 px-3 md:px-6">
                    <img class="h-24 w-24 md:h-40 md:w-40 rounded-full object-cover" src="{{ $agent->profile->picture }}" alt="{{ $agent->profile->name }}" onerror="this.src='/images/account.png'" />
                    <div class="relative">
                        @if(false)
                        {{-- Verified status is not yet available, so hide this for now --}}
                        <div class="absolute bottom-2">
                            <p class="drop-shadow bg-sky-500 text-white text-xs font-normal px-1.5 py-0.5 rounded-full">Terverifikasi</p>
                        </div>
                        @endif
                        <div class="absolute w-max mt-2">
                            <h1 class="text-xl md:text-2xl text-slate-800">{{ $agent->first_name }} {{ $agent->last_name }}</h1>
                            <p class="text-sm md:text-base text-slate-500">{{ $agent->profile->city }} &bull; {{ $agent->profile->company }}</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-20 pb-4 md:mt-28 px-4 md:px-6">
                <h1 class="text-base text-slate-500 uppercase">Tentang</h1>
                <p class="mt-2 text-sm text-slate-800">"{{ $agent->profile->description }}"</p>
                <h1 class="mt-10 mb-2 text-base text-slate-500 uppercase">Listing oleh {{ $agent->first_name }}</h1>
                <div class="grid md:grid-cols-2 gap-4">
                    @foreach ($listings as $listing)
                    <div class="flex flex-col justify-between rounded-lg bg-white drop-shadow-sm">
                        <a href="/public/listings/{{ $listing->id }}" target="_blank" class="grid grid-cols-5">
                            <div class="col-span-2">
                                @if(empty($listing->pictureUrls))
                                <img class="h-full w-full object-cover object-center rounded-tl-lg" src="/images/placeholder.png" alt="{{ $listing->title }}" />
                                @else
                                @foreach($listing->pictureUrls as $pictureUrl)
                                @if($loop->first)
                                <img class="h-full w-full object-cover object-center rounded-tl-lg" src="{{ $pictureUrl }}" alt="{{ $listing->title }}" onerror="this.src='/images/placeholder.png'" />
                                @endif
                                @endforeach
                                @endif
                            </div>
                            <div class="col-span-3 flex flex-1 flex-col px-3 py-2">
                                <div class="text-xs leading-4 text-slate-500">{{ $listing->title }}</div>
                                <div class="mt-2 flex flex-col">
                                    <div class="text-2xl font-semibold leading-8 text-slate-800">
                                        {{ $listing->formatted_price }}
                                    </div>
                                    <div class="mt-1.5 line-clamp-3 text-xs leading-4 text-slate-500">
                                        {{ $listing->address }}
                                    </div>
                                </div>
                                <div class="mt-1 flex flex-col flex-wrap content-start border-t border-solid border-t-slate-200 py-2">
                                    <div class="flex flex-wrap gap-3">
                                        <div class="flex items-center justify-between gap-1">
                                            <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M15.75 8.085V6C15.75 4.7625 14.7375 3.75 13.5 3.75H10.5C9.9225 3.75 9.3975 3.975 9 4.335C8.6025 3.975 8.0775 3.75 7.5 3.75H4.5C3.2625 3.75 2.25 4.7625 2.25 6V8.085C1.7925 8.4975 1.5 9.09 1.5 9.75V14.25H3V12.75H15V14.25H16.5V9.75C16.5 9.09 16.2075 8.4975 15.75 8.085ZM10.5 5.25H13.5C13.9125 5.25 14.25 5.5875 14.25 6V7.5H9.75V6C9.75 5.5875 10.0875 5.25 10.5 5.25ZM3.75 6C3.75 5.5875 4.0875 5.25 4.5 5.25H7.5C7.9125 5.25 8.25 5.5875 8.25 6V7.5H3.75V6ZM3 11.25V9.75C3 9.3375 3.3375 9 3.75 9H14.25C14.6625 9 15 9.3375 15 9.75V11.25H3Z" fill="#94A3B8" />
                                            </svg>
                                            <div class="grow self-stretch whitespace-nowrap text-base leading-6 text-slate-800">
                                                {{ $listing->bedroomCount }} KT
                                            </div>
                                        </div>
                                        <div class="flex items-center justify-between gap-1">
                                            <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M5.25 6.75C6.07843 6.75 6.75 6.07843 6.75 5.25C6.75 4.42157 6.07843 3.75 5.25 3.75C4.42157 3.75 3.75 4.42157 3.75 5.25C3.75 6.07843 4.42157 6.75 5.25 6.75Z" fill="#94A3B8" />
                                                <path d="M15 9.75V3.6225C15 2.4525 14.0475 1.5 12.8775 1.5C12.315 1.5 11.775 1.725 11.3775 2.1225L10.44 3.06C10.32 3.0225 10.1925 3 10.0575 3C9.7575 3 9.48 3.09 9.2475 3.24L11.3175 5.31C11.4675 5.0775 11.5575 4.8 11.5575 4.5C11.5575 4.365 11.535 4.245 11.505 4.1175L12.4425 3.18C12.5297 3.09384 12.6404 3.03541 12.7608 3.01207C12.8811 2.98873 13.0056 3.00153 13.1187 3.04884C13.2318 3.09616 13.3283 3.17588 13.3962 3.27796C13.464 3.38004 13.5002 3.49992 13.5 3.6225V9.75H8.3625C8.1375 9.5925 7.935 9.4125 7.7475 9.21L6.6975 8.0475C6.555 7.89 6.375 7.7625 6.18 7.6725C5.92273 7.54667 5.63769 7.48836 5.35168 7.50304C5.06567 7.51773 4.78809 7.60493 4.54507 7.75645C4.30204 7.90796 4.10156 8.11881 3.96247 8.36915C3.82338 8.6195 3.75027 8.90111 3.75 9.1875V9.75H1.5V14.25C1.5 15.075 2.175 15.75 3 15.75C3 16.1625 3.3375 16.5 3.75 16.5H14.25C14.6625 16.5 15 16.1625 15 15.75C15.825 15.75 16.5 15.075 16.5 14.25V9.75H15ZM15 14.25H3V11.25H15V14.25Z" fill="#94A3B8" />
                                            </svg>
                                            <div class="grow self-stretch whitespace-nowrap text-base leading-6 text-slate-800">
                                                {{ $listing->bathroomCount }} KM
                                            </div>
                                        </div>
                                        <div class="flex items-center justify-start gap-1">
                                            <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M9 4.2675L12.75 7.6425V13.5H11.25V9H6.75V13.5H5.25V7.6425L9 4.2675ZM9 2.25L1.5 9H3.75V15H8.25V10.5H9.75V15H14.25V9H16.5L9 2.25Z" fill="#94A3B8" />
                                            </svg>
                                            <div class="grow self-stretch whitespace-nowrap text-base leading-6 text-slate-800">
                                                {{ $listing->buildingSize }}m2
                                            </div>
                                        </div>
                                        <div class="flex items-center justify-between gap-1">
                                            <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M12.75 5.25L9 2.25L5.25 7.5L2.25 5.25V15H15.75V5.25H12.75ZM14.25 12.7125L9 8.625L6 12.75L3.75 10.95V8.25L5.58 9.6225L9.3 4.41L12.225 6.75H14.25V12.7125Z" fill="#94A3B8" />
                                            </svg>
                                            <div class="grow self-stretch whitespace-nowrap text-base leading-6 text-slate-800">
                                                {{ $listing->lotSize }}m2
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                        <div class="flex w-full justify-between gap-5 rounded-none bg-ribbon-100 px-3 py-2.5 rounded-b-lg">
                            @include('partials.copyButton')
                            <a href="tel:{{ $agent->profile->phoneNumber }}" class="justify-center self-center whitespace-nowrap rounded-lg bg-ribbon-500 px-4 py-2 text-center text-sm text-slate-50">
                                Tanya Properti
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js"></script>
</body>

</html>
