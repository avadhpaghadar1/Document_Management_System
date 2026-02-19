@props([
    'name',
    'show' => false,
    'maxWidth' => '2xl',
])

@php
$maxWidthClass = match ($maxWidth) {
    'sm' => 'sm:max-w-sm',
    'md' => 'sm:max-w-md',
    'lg' => 'sm:max-w-lg',
    'xl' => 'sm:max-w-xl',
    '2xl' => 'sm:max-w-2xl',
    default => 'sm:max-w-2xl',
};
@endphp

<div
    x-data="{ show: @js($show) }"
    x-init="
        window.addEventListener('open-modal', (event) => {
            if (event.detail === @js($name)) show = true
        })
        window.addEventListener('close-modal', (event) => {
            if (event.detail === @js($name)) show = false
        })
    "
    x-on:close.stop="show = false"
    x-on:keydown.escape.window="show = false"
    x-show="show"
    class="fixed inset-0 z-50 px-4 py-6 overflow-y-auto sm:px-0"
    style="display: none;"
>
    <div
        x-show="show"
        class="fixed inset-0 transition-all transform"
        x-on:click="show = false"
    >
        <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
    </div>

    <div
        x-show="show"
        class="mb-6 bg-white rounded-lg overflow-hidden shadow-xl transform transition-all sm:w-full {{ $maxWidthClass }} sm:mx-auto"
        x-on:click.stop
    >
        {{ $slot }}
    </div>
</div>
