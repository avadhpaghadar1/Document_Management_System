@extends('layouts.main')
@section('title','Update Document Type')
@section('content')
<div class="container-fluid">
<div class="row ">
        <div class="col-12 d-flex justify-content-between">
            <div class="d-flex">
                <h3 class="h3 mb-3">@yield('title')</h3>
                <p class="ps-2"><small>Manage document Types</small></p>
            </div>
            <div>
                <p><i class="mb-1" data-feather="home"></i><span class="mx-1">Home</span><span><i data-feather="chevron-right"></i></span><span>@yield('title')</span></p>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="m-sm-3">
                        <form id="document-form" method="POST" action="{{ route('update-document-type',['id'=>$documentType->id]) }}">
                            @csrf
                            @method("PATCH")
                            <div class="row">
                                <div class="col-6 d-flex flex-column">
                                    <div class="mb-3">
                                        <x-input-label for="document_type" :value="__('Name')" />
                                        <x-input name="name" id="document_type" class="{{ $errors->has('name') ? 'is-invalid' : '' }}" :value="old('name',$documentType->name)" />
                                        <x-input-error :messages="$errors->get('name')" />
                                    </div>
                                    <div class="mb-3">
                                        <x-input-label for="fields" :value="__('Document Fields')" />
                                        <div class="d-flex">
                                            <select id="field-type" class="w-25 form-control">
                                                <option value="TEXT">Text</option>
                                                <option value="DATE">Date</option>
                                                <option value="DATETIME">Date Time</option>
                                            </select>
                                            <div class="input-group ms-2 w-75">
                                                <x-input type="text" id="field-name" name="field-name" />
                                                <x-button class="btn-info" type="button" id="add-field-button"><i class="align-middle" data-feather="plus"></i></x-button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6 mt-5 pt-4">
                                    <table class="table" id="fields-table">
                                        <thead>
                                            <tr>
                                                <th>Field Name</th>
                                                <th>Type</th>
                                                <th>Delete</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($documentType->documentFields as $field)
                                            <tr>
                                                <td class="border border-0">{{ $field->field_name }}</td>
                                                <td class="border border-0">{{ $field->field_type }}</td>
                                                <td class="border border-0">
                                                    <button type="button" class="btn btn-danger remove-field-button">
                                                        <i data-feather="trash-2"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="d-block mt-3">
                                <x-button class="btn-primary" type="submit"><i class="mb-1 me-1" data-feather="save"></i>Save</x-button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
