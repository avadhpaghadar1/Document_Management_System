@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'text primary']) }}>
        {{ $status }}
    </div>
@endif
