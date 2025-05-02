@extends('layouts.main')
@section('title','Add User')
@section('content')
<div class="container-fluid">
    <div class="row ">
        <div class="col-12 d-flex justify-content-between">
            <div class="d-flex">
                <h3 class="h3 mb-3">@yield('title')</h3>
                <p class="ps-2"><small>Manage users</small></p>
            </div>
            <div>
                <p><i class="mb-1" data-feather="home"></i><span class="mx-1">Home</span>
                    <span><i data-feather="chevron-right"></i></span>
                    <span>Users</span>
                    <span><i data-feather="chevron-right"></i></span>
                    <span>@yield('title')</span>
                </p>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="m-sm-3">
                        <form method="POST" action="{{ route('add-user') }}">
                            @csrf
                            <div class="border-bottom mb-3">
                                <h4 class="h4 ">Add New User</h4>
                            </div>
                            <div class="w-100 d-flex flex-column align-items-center">
                                <div class="mb-3 w-50">
                                    <x-input-label for="name" :value="__('Name')" />
                                    <x-input type="text" name="name" class="{{ $errors->has('name') ? 'is-invalid' : '' }}" :value="old('name')" placeholder="Enter Name" />
                                    <x-input-error :messages="$errors->get('name')" />
                                </div>
                                <div class="mb-3 w-50">
                                    <x-input-label for="email" :value="__('Email')" />
                                    <x-input type="email" name="email" class="{{ $errors->has('email') ? 'is-invalid' : '' }}" :value="old('email')" placeholder="Enter Email" />
                                    <x-input-error :messages="$errors->get('email')" />
                                </div>
                                <div class="mb-3 w-50">
                                    <x-input-label for="country" :value="__('Country Code')" />
                                    <select class="form-control {{ $errors->has('country') ? 'is-invalid' : '' }}" id="country" name="country">
                                        <option value="2">None</option>
                                        @foreach($countryCodes as $countryCode)
                                        <option value="{{ $countryCode['code'] }}" {{$countryCode['code']==old('country')?"selected":""}}>{{ $countryCode['code'] }} {{ $countryCode['name'] }}</option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('country')" />
                                </div>
                                <div class="mb-3 w-50">
                                    <x-input-label for="mobile" :value="__('Mobile Number')" />
                                    <x-input type="text" name="mobile" class="{{ $errors->has('mobile') ? 'is-invalid' : '' }}" :value="old('mobile')" placeholder="Enter Mobile Number" />
                                    <x-input-error :messages="$errors->get('mobile')" />
                                </div>
                                <div class="mb-3 w-50">
                                    <x-input-label for="password" :value="__('Password')" />
                                    <x-input type="password" name="password" class="{{ $errors->has('password') ? 'is-invalid' : '' }}" placeholder="Enter password" />
                                    <x-input-error :messages="$errors->get('password')" />
                                </div>
                                <div class="mb-3 w-50">
                                    <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
                                    <x-input type="password" name="password_confirmation" class="{{ $errors->has('password_confirmation') ? 'is-invalid' : '' }}" placeholder="Enter Confirm Password" />
                                    <x-input-error :messages="$errors->get('password_confirmation')" />
                                </div>
                                <div class="text-start  w-50">
                                    <x-input-label :value="__('Permissions')" />
                                </div>
                                <div class="w-100 d-flex flex-column align-items-center  mb-3">
                                    <div class="d-flex w-50">
                                        <div class="input-group m-1">
                                            <span class="input-group-text">
                                                <input type="checkbox" class="form-check-input" name="permission[]" value="add_user" id="add_user" {{ in_array('add_user', old('permission', [])) ? 'checked' : '' }}>
                                                <x-input-label for="add_user" class="mt-2 ms-2" :value="__('Add Users')" />
                                            </span>
                                        </div>
                                        <div class="input-group m-1">
                                            <span class="input-group-text">
                                                <input type="checkbox" class="form-check-input" name="permission[]" value="view_user" id="view_user" {{ in_array('view_user', old('permission', [])) ? 'checked' : '' }}>
                                                <x-input-label for="view_user" class="mt-2 ms-2" :value="__('View Users')" />
                                            </span>
                                        </div>
                                        <div class="input-group m-1">
                                            <span class="input-group-text">
                                                <input type="checkbox" class="form-check-input" name="permission[]" value="edit_user" id="edit_user" {{ in_array('edit_user', old('permission', [])) ? 'checked' : '' }}>
                                                <x-input-label for="edit_user" class="mt-2 ms-2" :value="__('Edit Users')" />
                                            </span>
                                        </div>
                                        <div class="input-group m-1">
                                            <span class="input-group-text">
                                                <input type="checkbox" class="form-check-input" name="permission[]" value="delete_user" id="delete_user" {{ in_array('delete_user', old('permission', [])) ? 'checked' : '' }}>
                                                <x-input-label for="delete_user" class="mt-2 ms-2" :value="__('Delete Users')" />
                                            </span>
                                        </div>
                                    </div>
                                    <div class="d-flex w-50">
                                        <div class="input-group m-1">
                                            <span class="input-group-text">
                                                <input type="checkbox" class="form-check-input" name="permission[]" value="create_group" id="create_group" {{ in_array('create_group', old('permission', [])) ? 'checked' : '' }}>
                                                <x-input-label for="create_group" class="mt-2 ms-2" :value="__('Create Group')" />
                                            </span>
                                        </div>
                                        <div class="input-group m-1">
                                            <span class="input-group-text">
                                                <input type="checkbox" class="form-check-input" name="permission[]" value="view_group" id="view_group" {{ in_array('view_group', old('permission', [])) ? 'checked' : '' }}>
                                                <x-input-label for="view_group" class="mt-2 ms-2" :value="__('View Groups')" />
                                            </span>
                                        </div>
                                        <div class="input-group m-1">
                                            <span class="input-group-text">
                                                <input type="checkbox" class="form-check-input" name="permission[]" value="edit_group" id="edit_group" {{ in_array('edit_group', old('permission', [])) ? 'checked' : '' }}>
                                                <x-input-label for="edit_group" class="mt-2 ms-2" :value="__('Edit Groups')" />
                                            </span>
                                        </div>
                                        <div class="input-group m-1">
                                            <span class="input-group-text">
                                                <input type="checkbox" class="form-check-input" name="permission[]" value="delete_group" id="delete_group" {{ in_array('delete_group', old('permission', [])) ? 'checked' : '' }}>
                                                <x-input-label for="delete_group" class="mt-2 ms-2" :value="__('Delete Groups')" />
                                            </span>
                                        </div>
                                    </div>
                                    <div class="d-flex w-50">
                                        <div class="input-group m-1">
                                            <span class="input-group-text">
                                                <input type="checkbox" class="form-check-input" name="permission[]" value="create_document_type" id="create_document_type" {{ in_array('create_document_type', old('permission', [])) ? 'checked' : '' }}>
                                                <x-input-label for="create_document_type" class="mt-2 ms-2" :value="__('Create Document Type')" />
                                            </span>
                                        </div>
                                        <div class="input-group m-1">
                                            <span class="input-group-text">
                                                <input type="checkbox" class="form-check-input" name="permission[]" value="view_document_type" id="view_document_type" {{ in_array('view_document_type', old('permission', [])) ? 'checked' : '' }}>
                                                <x-input-label for="view_document_type" class="mt-2 ms-2" :value="__('View Document Type')" />
                                            </span>
                                        </div>
                                        <div class="input-group m-1">
                                            <span class="input-group-text">
                                                <input type="checkbox" class="form-check-input" name="permission[]" value="edit_document_type" id="edit_document_type" {{ in_array('edit_document_type', old('permission', [])) ? 'checked' : '' }}>
                                                <x-input-label for="edit_document_type" class="mt-2 ms-2" :value="__('Edit Document Type')" />
                                            </span>
                                        </div>
                                    </div>
                                    <div class="d-flex w-50">
                                        <div class="input-group m-1">
                                            <span class="input-group-text">
                                                <input type="checkbox" class="form-check-input" name="permission[]" value="delete_document_type" id="delete_document_type" {{ in_array('delete_document_type', old('permission', [])) ? 'checked' : '' }}>
                                                <x-input-label for="delete_document_type" class="mt-2 ms-2" :value="__('Delete Document Type')" />
                                            </span>
                                        </div>
                                    </div>

                                </div>
                                <div class="mt-3 w-100 d-flex justify-content-center">
                                    <x-button class="btn-primary w-50"><i class="mb-1 me-1" data-feather="user-plus"></i>Add New User</x-button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection