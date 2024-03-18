<div>
    <button type="button" onclick="handleClickCopy('{{ $listing->id }}')" onmouseleave="handleMouseoutCopy('{{ $listing->id }}')" data-tooltip-target="tooltip-{{ $listing->id }}" class="justify-center self-center whitespace-nowrap focus:ring-2 rounded focus:outline-none focus:ring-blue-300 p-2">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M16 1H4C2.9 1 2 1.9 2 3V17H4V3H16V1ZM15 5H8C6.9 5 6.01 5.9 6.01 7L6 21C6 22.1 6.89 23 7.99 23H19C20.1 23 21 22.1 21 21V11L15 5ZM8 21V7H14V12H19V21H8Z" fill="#64748B" />
        </svg>
    </button>
    <div id="tooltip-{{ $listing->id }}" role="tooltip" class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-700">
        Salin link
        <div class="tooltip-arrow" data-popper-arrow></div>
    </div>
    <script>
        function handleClickCopy(id) {
            navigator.clipboard.writeText(`${location.host}/public/listings/${id}`);
            const tooltip = document.getElementById(`tooltip-${id}`);
            tooltip.innerHTML = "Link disalin";
        }

        function handleMouseoutCopy(id) {
            const tooltip = document.getElementById(`tooltip-${id}`);
            tooltip.innerHTML = "Salin link";
        }
    </script>
</div>
