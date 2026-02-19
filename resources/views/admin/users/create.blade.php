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
                                    <x-input-label for="role" :value="__('Role')" />
                                    <select class="form-control {{ $errors->has('role') ? 'is-invalid' : '' }}" id="role" name="role">
                                        <option value="viewer" {{ old('role', 'viewer') === 'viewer' ? 'selected' : '' }}>Viewer</option>
                                        <option value="manager" {{ old('role') === 'manager' ? 'selected' : '' }}>Manager</option>
                                        <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                                    </select>
                                    <x-input-error :messages="$errors->get('role')" />
                                    <div class="form-text">Role presets override the permissions checkboxes below.</div>
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

                                    <div class="d-flex w-50">
                                        <div class="input-group m-1">
                                            <span class="input-group-text">
                                                <input type="checkbox" class="form-check-input" name="permission[]" value="view_document_audit" id="view_document_audit" {{ in_array('view_document_audit', old('permission', [])) ? 'checked' : '' }}>
                                                <x-input-label for="view_document_audit" class="mt-2 ms-2" :value="__('View Document Audit')" />
                                            </span>
                                        </div>
                                        <div class="input-group m-1">
                                            <span class="input-group-text">
                                                <input type="checkbox" class="form-check-input" name="permission[]" value="export_document" id="export_document" {{ in_array('export_document', old('permission', [])) ? 'checked' : '' }}>
                                                <x-input-label for="export_document" class="mt-2 ms-2" :value="__('Export Documents')" />
                                            </span>
                                        </div>
                                    </div>

                                    <div class="d-flex w-50">
                                        <div class="input-group m-1">
                                            <span class="input-group-text">
                                                <input type="checkbox" class="form-check-input" name="permission[]" value="approve_document" id="approve_document" {{ in_array('approve_document', old('permission', [])) ? 'checked' : '' }}>
                                                <x-input-label for="approve_document" class="mt-2 ms-2" :value="__('Approve / Reject Documents')" />
                                            </span>
                                        </div>
                                        <div class="input-group m-1">
                                            <span class="input-group-text">
                                                <input type="checkbox" class="form-check-input" name="permission[]" value="view_document_versions" id="view_document_versions" {{ in_array('view_document_versions', old('permission', [])) ? 'checked' : '' }}>
                                                <x-input-label for="view_document_versions" class="mt-2 ms-2" :value="__('View Document Versions')" />
                                            </span>
                                        </div>
                                        <div class="input-group m-1">
                                            <span class="input-group-text">
                                                <input type="checkbox" class="form-check-input" name="permission[]" value="restore_document_version" id="restore_document_version" {{ in_array('restore_document_version', old('permission', [])) ? 'checked' : '' }}>
                                                <x-input-label for="restore_document_version" class="mt-2 ms-2" :value="__('Restore Document Version')" />
                                            </span>
                                        </div>
                                    </div>

                                    <div class="d-flex w-50">
                                        <div class="input-group m-1">
                                            <span class="input-group-text">
                                                <input type="checkbox" class="form-check-input" name="permission[]" value="share_document" id="share_document" {{ in_array('share_document', old('permission', [])) ? 'checked' : '' }}>
                                                <x-input-label for="share_document" class="mt-2 ms-2" :value="__('Share Document Links')" />
                                            </span>
                                        </div>
                                        <div class="input-group m-1">
                                            <span class="input-group-text">
                                                <input type="checkbox" class="form-check-input" name="permission[]" value="view_recycle_bin" id="view_recycle_bin" {{ in_array('view_recycle_bin', old('permission', [])) ? 'checked' : '' }}>
                                                <x-input-label for="view_recycle_bin" class="mt-2 ms-2" :value="__('View Recycle Bin')" />
                                            </span>
                                        </div>
                                        <div class="input-group m-1">
                                            <span class="input-group-text">
                                                <input type="checkbox" class="form-check-input" name="permission[]" value="restore_document" id="restore_document" {{ in_array('restore_document', old('permission', [])) ? 'checked' : '' }}>
                                                <x-input-label for="restore_document" class="mt-2 ms-2" :value="__('Restore Documents')" />
                                            </span>
                                        </div>
                                        <div class="input-group m-1">
                                            <span class="input-group-text">
                                                <input type="checkbox" class="form-check-input" name="permission[]" value="force_delete_document" id="force_delete_document" {{ in_array('force_delete_document', old('permission', [])) ? 'checked' : '' }}>
                                                <x-input-label for="force_delete_document" class="mt-2 ms-2" :value="__('Permanent Delete Documents')" />
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