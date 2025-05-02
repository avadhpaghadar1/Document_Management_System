@foreach ($documentType->documentFields as $field)
<div class="my-3">
    <div class="input-group">
        <span class="input-group-text">{{ $field->field_name }}</span>
        <x-input type="{{ $field->field_type }}" name="fields[{{ $field->field_name }}][value]" />
        <x-input type="hidden" name="fields[{{ $field->field_name }}][type]" value="{{ $field->field_type }}" />
        <x-button type="button" class="btn-danger ms-2 remove-field">
            <i class="align-middle" data-feather="trash-2"></i>
        </x-button>
    </div>
</div>
@endforeach
