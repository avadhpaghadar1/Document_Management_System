@extends('layouts.main')
@section('title','Update User')
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
                        <form method="POST" action="{{ route('update-user',['id'=>$user->id]) }}">
                            @method('PATCH')
                            @csrf
                            <div class="border-bottom mb-3">
                                <h4 class="h4">Add New User</h4>
                            </div>
                            <div class="w-100 d-flex flex-column align-items-center">
                                <div class="mb-3 w-50">
                                    <x-input-label for="name" :value="__('Name')" />
                                    <x-input type="text" name="name" class="{{ $errors->has('name') ? 'is-invalid' : '' }}" :value="old('name',$user->name)" placeholder="Enter Name" />
                                    <x-input-error :messages="$errors->get('name')" />
                                </div>
                                <div class="mb-3 w-50">
                                    <x-input-label for="role" :value="__('Role')" />
                                    <select class="form-control {{ $errors->has('role') ? 'is-invalid' : '' }}" id="role" name="role">
                                        @php
                                            $currentRole = old('role', $user->role ?? 'viewer');
                                        @endphp
                                        <option value="viewer" {{ $currentRole === 'viewer' ? 'selected' : '' }}>Viewer</option>
                                        <option value="manager" {{ $currentRole === 'manager' ? 'selected' : '' }}>Manager</option>
                                        <option value="admin" {{ $currentRole === 'admin' ? 'selected' : '' }}>Admin</option>
                                    </select>
                                    <x-input-error :messages="$errors->get('role')" />
                                    <div class="form-text">Role presets override the permissions checkboxes below.</div>
                                </div>
                                <div class="mb-3 w-50">
                                    <x-input-label for="email" :value="__('Email')" />
                                    <x-input type="email" name="email" class="{{ $errors->has('email') ? 'is-invalid' : '' }}" :value="old('email',$user->email)" placeholder="Enter Email" />
                                    <x-input-error :messages="$errors->get('email')" />
                                </div>
                                <div class="mb-3 w-50">
                                    <x-input-label for="country" :value="__('Country Code')" />
                                    <select class="form-control {{ $errors->has('country') ? 'is-invalid' : '' }}" id="country" name="country">
                                        <option value="2">None</option>
                                        @foreach($countryCodes as $countryCode)
                                        <option value="{{ $countryCode['code'] }}" {{ ($countryCode['code'] == $user->country || $countryCode['code']==old('country')) ? 'selected' : '' }}>{{ $countryCode['code'] }} {{ $countryCode['name'] }}</option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('country')" />
                                </div>
                                <div class="mb-3 w-50">
                                    <x-input-label for="mobile" :value="__('Mobile Number')" />
                                    <x-input type="text" name="mobile" class="{{ $errors->has('mobile') ? 'is-invalid' : '' }}" :value="old('mobile',$user->mobile)" placeholder="Enter Mobile Number" />
                                    <x-input-error :messages="$errors->get('mobile')" />
                                </div>
                                <span class="w-50 my-3">Only if you want to change users password</span>
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
                                <div class="container w-50">
                                    <div class="row">
                                        @foreach ([
                                        'add_user' => 'Add Users',
                                        'view_user' => 'View Users',
                                        'edit_user' => 'Edit Users',
                                        'delete_user' => 'Delete Users',
                                        'create_group' => 'Create Group',
                                        'view_group' => 'View Groups',
                                        'edit_group' => 'Edit Groups',
                                        'delete_group' => 'Delete Groups',
                                        'create_document_type' => 'Create Document Type',
                                        'view_document_type' => 'View Document Type',
                                        'edit_document_type' => 'Edit Document Type',
                                        'delete_document_type' => 'Delete Document Type',
                                        'view_document_audit' => 'View Document Audit',
                                        'export_document' => 'Export Documents',
                                        'approve_document' => 'Approve / Reject Documents',
                                        'view_document_versions' => 'View Document Versions',
                                        'restore_document_version' => 'Restore Document Version',
                                        'share_document' => 'Share Document Links',
                                        'view_recycle_bin' => 'View Recycle Bin',
                                        'restore_document' => 'Restore Documents',
                                        'force_delete_document' => 'Permanent Delete Documents'
                                        ] as $permission => $label)
                                        @if (($loop->index) % 4 == 0)
                                        <br>
                                        @endif
                                        <div class="col-4">
                                            <div class="input-group m-1">
                                                <span class="input-group-text">
                                                    <input type="checkbox" class="form-check-input" name="permission[]" value="{{ $permission }}" id="{{ $permission }}" {{ in_array($permission, $permissions) || in_array($permission, old('permission', [])) ? 'checked' : '' }}>
                                                    <x-input-label for="{{ $permission }}" class="mt-2 ms-2" :value="__($label)" />
                                                </span>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>

                                </div>
                            </div>
                            <div class="mt-3 w-100 d-flex justify-content-center">
                                <x-button class="btn-primary w-50">Update User</x-button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection