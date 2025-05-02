@props(['messages'])

@if ($messages)
    <ul {{ $attributes->merge(['class' => 'invalid-feedback ps-0']) }}>
        @foreach ((array) $messages as $message)
            {{ $message }}
        @endforeach
    </ul>
@endif

