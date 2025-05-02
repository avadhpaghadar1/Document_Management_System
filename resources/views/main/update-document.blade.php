@extends('layouts.main')
@section('title','Update Document')
@section('content')
<div class="container-fluid">
    <div class="row ">
        <div class="col-12 d-flex justify-content-between">
            <div class="d-flex">
                <h3 class="h3 mb-3">@yield('title')</h3>
                <p class="ps-2"><small>Manage documents</small></p>
            </div>
            <div>
                <p><i class="mb-1" data-feather="home"></i><span class="mx-1">Home</span>
                    <span><i data-feather="chevron-right"></i></span>
                    <span>Documents</span>
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
                        <form method="POST" id="myForm" action="{{route('update-document',['id'=>$documents->id])}}" enctype="multipart/form-data">
                            @csrf
                            <div class="border-bottom mb-3">
                                <h4 class="h4">Document Data</h4>
                            </div>

                            <!-- --------------Document Type Form -------------------->
                            <div class="row">
                                <div class="col-6 d-flex flex-column">
                                    <div class=" mb-3">
                                        <x-input-label for="document_type" :value="__('Document Type')" />
                                        <span class="text-danger">*</span>
                                        <span class="form-control" style="background-color: #e9ecef; cursor: not-allowed;">
                                            {{ $documents->documentType->name }}
                                        </span>
                                        <input type="hidden" name="document_type_id" value="{{ $documents->documentType->id }}" />
                                        <x-input-error :messages="$errors->get('document_type_id')" />
                                    </div>
                                    <div class="mb-3">
                                        <x-input-label for="note" :value="__('Notes')" />
                                        <textarea class="form-control" name="note" placeholder="Enter...">{{old('note',$documents->note)}}</textarea>
                                    </div>
                                    <div class="mb-3">
                                        <div class="input-group ">
                                            <span class="input-group-text  {{ $errors->has('expiry') ? 'border border-danger' : '' }}" id="basic-addon1">Expiration Date<span class="text-danger">*</span></span>
                                            <x-input type="date" class="{{ $errors->has('expiry') ? 'is-invalid' : '' }}" name="expiry" :value="old('expiry',$documents->expiry)" />
                                            <x-input-error :messages="$errors->get('expiry')" />
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="mb-3 ">
                                        <x-input-label for="notes" :value="__('Document Fields')" />
                                        <div class="d-flex">
                                            <select class="w-25 form-control" id="input-type-select">
                                                <option value="TEXT">Text</option>
                                                <option value="DATE">Date</option>
                                                <option value="DATETIME-LOCAL">Date Time</option>
                                            </select>
                                            <div class="input-group ms-2 w-75">
                                                <x-input type="text" id="input-name" placeholder="Enter field name" />
                                                <x-button class="btn-info" type="button" id="add-field"><i class="align-middle" data-feather="plus"></i></x-button>
                                            </div>
                                        </div>

                                        <div id="dynamic_fields_container">
                                            @if(!old('fields'))
                                            @foreach ($document_details as $document_detail)

                                            <div class="my-3 input-group">
                                                <span class="input-group-text {{ $errors->has('fields.' . $document_detail->field_name . '.value') ? 'border border-danger' : '' }}">
                                                    {{ $document_detail->field_name }}
                                                </span>
                                                <x-input type="hidden" name="fields[{{ $document_detail->field_name }}][type]" value="{{ $document_detail->field_type }}"></x-input>
                                                <x-input type="{{ $document_detail->field_type }}" class="form-control" name="fields[{{ $document_detail->field_name }}][value]" value="{{ old('fields.' . $document_detail->field_name . '.value', $document_detail->field_value) }}"></x-input>
                                                <x-input-error :messages="$errors->get('fields.' . $document_detail->field_name . '.value')" />
                                                <x-button type="button" class="btn btn-danger ms-2 remove-field">
                                                    <i class="align-middle" data-feather="trash-2"></i>
                                                </x-button>
                                            </div>
                                            @endforeach
                                            @endif
                                        </div>
                                    </div>
                                    <div id="fields-container">
                                        @if(old('fields'))
                                        @foreach(old('fields') as $fieldName => $fieldData)
                                        <div class="mb-3 input-group">
                                            <span class="input-group-text">{{ $fieldName }}</span>
                                            <input type="{{ $fieldData['type'] }}" class="form-control" name="fields[{{ $fieldName }}][value]" value="{{ $fieldData['value'] }}">
                                            <input type="hidden" name="fields[{{ $fieldName }}][type]" value="{{ $fieldData['type'] }}">
                                            <button type="button" class="btn btn-danger ms-2 remove-field">
                                                <i class="align-middle" data-feather="trash-2"></i>
                                            </button>
                                        </div>
                                        @endforeach
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="small">
                                <p>Note:<span class="text-danger">*</span> Required fields</p>
                            </div>

                            <!-- -------------Notification Fields------------ -->

                            <div class="row">
                                <div class="border-bottom my-3">
                                    <h4 class="h4 ">Notifications</h4>
                                </div>
                                <div class="col-12" id="addNotification">
                                    <x-input-label for="notes" :value="__('Notifications')" />
                                    @if(!old('notifications'))
                                    @foreach ($document_notifications as $index => $notification)
                                    <div class="mb-3 d-flex w-50 ">
                                        <x-input type="day" name="notifications[{{ $index }}][day]" value="{{$notification->day}}" placeholder="Enter days" />
                                        @php
                                        $selectBefore = '';
                                        $selectAfter = '';

                                        if ($notification->name == "dayBefore") {
                                        $selectBefore = 'selected';
                                        } elseif ($notification->name == "dayAfter") {
                                        $selectAfter = 'selected';
                                        }
                                        @endphp
                                        <select class="form-select mx-2" name="notifications[{{ $index }}][name]">
                                            <option class="text-success" value="dayBefore" {{ $selectBefore }}>days before expiration/due date</option>
                                            <option class="text-danger" value="dayAfter" {{ $selectAfter }}>days after expiration/due date</option>
                                        </select>
                                        <x-button type="button" class="btn-danger mx-3 remove-button"><i class="align-middle" data-feather="trash-2"></i></x-button>
                                    </div>
                                    @endforeach
                                    @endif
                                    @if(old('notifications'))
                                    @foreach (old('notifications') as $index => $notification)
                                    <div class="mb-3 d-flex w-50">
                                        <x-input type="number" name="notifications[{{ $index }}][day]" value="{{ old('notifications.'.$index.'.day', $notification['day'] ?? $notification->day) }}" placeholder="Enter days" />
                                        @php
                                        $selectBefore = old('notifications.'.$index.'.name', $notification['name'] ??
                                        $notification->name) == 'dayBefore' ? 'selected' : '';
                                        $selectAfter = old('notifications.'.$index.'.name', $notification['name'] ??
                                        $notification->name) == 'dayAfter' ? 'selected' : '';
                                        @endphp
                                        <select class="form-select mx-2" name="notifications[{{ $index }}][name]">
                                            <option class="text-success" value="dayBefore" {{ $selectBefore }}>days
                                                before expiration/due date</option>
                                            <option class="text-danger" value="dayAfter" {{ $selectAfter }}>days after
                                                expiration/due date</option>
                                        </select>
                                        <x-button type="button" class="btn-danger mx-3 remove-button"><i class="align-middle" data-feather="trash-2"></i></x-button>
                                    </div>
                                    @endforeach
                                    @endif
                                </div>
                                <div class="d-block">
                                    <x-button class="btn-info" id="add" type="button"><i class="align-middle" data-feather="plus"></i> Add Notification</x-button>
                                </div>
                            </div>

                            <!-- --------------Attachment------------- -->
                            <div class="row">
                                <div class="border-bottom my-4">
                                    <h4 class="h4">Attachment</h4>
                                </div>
                                <div class="col-12">
                                    <div class="dropzone" id="myDropzone">
                                        @if(old('file_names'))
                                        @foreach (old('file_names', []) as $file)
                                        <input type="hidden" name="file_names[]" value="{{ $file }}" id="file-input-{{ $file }}">
                                        @endforeach
                                        @else
                                        <input type="hidden" id="existingImages" value="{{ json_encode($document_images) }}">
                                        @endif
                                    </div>

                                </div>
                                <div class="w-100 d-flex justify-content-end">
                                    <img src="{{ asset('/storage/icons.png') }}" class="mt-3 w-25 img-fluid" alt="No-Image" />
                                </div>
                            </div>
                            <div class="row">
                                <div class="border-bottom my-4">
                                    <h4 class="h4">Permissions</h4>
                                </div>

                                <!----------------- Group Permission --------------->

                                <div class="col-12">
                                    <div class="w-100 d-flex justify-content-end">
                                        <x-button class="btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#AddGroupModel">
                                            <i class="mb-1 me-1" data-feather="plus"></i>Add Group</x-button>
                                    </div>
                                    <div>
                                        <table class="table" id="groupPermissionTable">
                                            <thead>
                                                <tr>
                                                    <th class="w-25">Group</th>
                                                    <th class="w-50 text-center">Permission</th>
                                                    <th class="w-25">Remove</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @if(!old('groupPermissions') && empty($errors)==0)
                                                @foreach ($document_groups as $index => $group_permission)
                                                <tr data-group-id="{{$group_permission->group_id}}">
                                                    <td class="w-25 group-name">{{$group_permission->group->name}}</td>
                                                    @php
                                                    $permissions = [];
                                                    if ($group_permission->view) {
                                                    $permissions[] = 'view';
                                                    }
                                                    if ($group_permission->edit) {
                                                    $permissions[] = 'edit';
                                                    }
                                                    if ($group_permission->delete) {
                                                    $permissions[] = 'delete';
                                                    }
                                                    $permissionsString = json_encode($permissions);
                                                    @endphp
                                                    <td class="w-50 text-center group-permissions">
                                                        @if ($group_permission->view)
                                                        <span class="badge bg-primary">View</span>
                                                        @endif
                                                        @if ($group_permission->edit)
                                                        <span class="badge bg-info">Edit</span>
                                                        @endif
                                                        @if ($group_permission->delete)
                                                        <span class="badge bg-danger">Delete</span>
                                                        @endif
                                                    </td>
                                                    <td class="w-25">
                                                        <x-button type="button" class="btn-danger remove-row">
                                                            <i class="align-middle" data-feather="trash-2"></i>
                                                        </x-button>
                                                    </td>
                                                    <x-input type="hidden" name="groupPermissions[{{$index}}][groupId]" value="{{$group_permission->group_id}}"></x-input>
                                                    <x-input type="hidden" name="groupPermissions[{{ $index }}][permissions]" value="{{ $permissionsString }}"></x-input>
                                                </tr>
                                                @endforeach
                                                @endif
                                                @if(old('groupPermissions') && is_array(old('groupPermissions')))
                                                @foreach(old('groupPermissions') as $index => $groupPermission)
                                                @if(is_array($groupPermission))
                                                <tr data-group-id="{{ $groupPermission['groupId'] }}">
                                                    <td class="w-25 group-name">
                                                        @php
                                                        $group = $groups->find($groupPermission['groupId']);
                                                        @endphp
                                                    </td>
                                                    <td class="w-50 text-center group-permissions">
                                                        @php
                                                        $permissions = json_decode($groupPermission['permissions'], true);
                                                        @endphp
                                                        @if(is_array($permissions))
                                                        @foreach($permissions as $permission)
                                                        @php
                                                        $badgeClasses = [
                                                        'view' => 'badge bg-primary',
                                                        'edit' => 'badge bg-info',
                                                        'delete' => 'badge bg-danger',
                                                        ];
                                                        @endphp
                                                        <span class="{{ $badgeClasses[$permission] ?? 'badge bg-secondary' }}">{{ ucfirst($permission) }}</span>
                                                        @endforeach
                                                        @endif
                                                    </td>
                                                    <td class="w-25">
                                                        <button type="button" class="btn btn-danger remove-row">
                                                            <i class="align-middle" data-feather="trash-2"></i>
                                                        </button>
                                                    </td>
                                                    <input type="hidden" name="groupPermissions[{{ $index }}][groupId]" value="{{ $groupPermission['groupId'] }}">
                                                    <input type="hidden" name="groupPermissions[{{ $index }}][permissions]" value="{{ $groupPermission['permissions'] }}">
                                                </tr>
                                                @endif
                                                @endforeach
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- --Group Model-- -->

                                    <div class="modal fade" id="AddGroupModel" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h1 class="modal-title fs-5" id="staticBackdropLabel">Add Group</h1>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <form>
                                                        <select class="form-control" id="groupSelect">
                                                            <option class="text-body-tertiary" value="" disabled>Select Group</option>
                                                            @foreach ($groups as $group)
                                                            <option value="{{$group->id}}">{{$group->name}}</option>
                                                            @endforeach
                                                        </select>
                                                        <div class="d-flex my-4 w-75">
                                                            <div class="input-group">
                                                                <span class="input-group-text">
                                                                    <input type="checkbox" class="form-check-input" name="groupPermissions[]" value="view" id="viewGroup">
                                                                    <x-input-label for="viewGroup" class="mt-2 ms-2" :value="__('View')" />
                                                                </span>
                                                            </div>
                                                            <div class="input-group">
                                                                <span class="input-group-text">
                                                                    <input type="checkbox" class="form-check-input" name="groupPermissions[]" value="edit" id="editGroup">
                                                                    <x-input-label for="editGroup" class="mt-2 ms-2" :value="__('Edit')" />
                                                                </span>
                                                            </div>
                                                            <div class="input-group">
                                                                <span class="input-group-text">
                                                                    <input type="checkbox" class="form-check-input" name="groupPermissions[]" value="delete" id="deleteGroup">
                                                                    <x-input-label for="deleteGroup" class="mt-2 ms-2" :value="__('Delete')" />
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                            <button type="button" class="btn btn-primary" id="saveGroupPermission">Save</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- -----------User Permission--------------- -->
                                <div class="col-12">
                                    <div class="w-100 d-flex justify-content-end">
                                        <x-button class="btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#AddUserModel">
                                            <i class="mb-1 me-1" data-feather="plus"></i>Add User</x-button>
                                    </div>
                                    <div>
                                        <table class="table" id="userPermissionTable">
                                            <thead>
                                                <tr>
                                                    <th class="w-25">User</th>
                                                    <th class="w-50 text-center">Permission</th>
                                                    <th class="w-25">Remove</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @if(!old('userPermissions') && empty($errors)==0)
                                                @foreach ($document_users as $index => $user_permission)
                                                @if($user_permission->user_id==$creatorId)
                                                <tr data-user-id="{{ $user_permission->user_id }}" class="d-none">
                                                    <td class="w-25 user-name">{{ $user_permission->user->name }}</td>
                                                    <x-input type="hidden" name="userPermissions[{{ $index }}][userId]" value="{{ $user_permission->user_id }}"></x-input>
                                                    @php
                                                    $permissions = [];
                                                    if ($user_permission->view) {
                                                    $permissions[] = 'view';
                                                    }
                                                    if ($user_permission->edit) {
                                                    $permissions[] = 'edit';
                                                    }
                                                    if ($user_permission->delete) {
                                                    $permissions[] = 'delete';
                                                    }
                                                    $permissionsString = json_encode($permissions);
                                                    @endphp
                                                    <x-input type="hidden" name="userPermissions[{{ $index }}][permissions]" value="{{ $permissionsString }}"></x-input>
                                                    <td class="w-50 text-center user-permissions">
                                                        @if ($user_permission->view)
                                                        <span class="badge bg-primary">View</span>
                                                        @endif
                                                        @if ($user_permission->edit)
                                                        <span class="badge bg-info">Edit</span>
                                                        @endif
                                                        @if ($user_permission->delete)
                                                        <span class="badge bg-danger">Delete</span>
                                                        @endif
                                                    </td>
                                                    <td class="w-25">
                                                        <x-button type="button" class="btn-danger remove-row">
                                                            <i class="align-middle" data-feather="trash-2"></i>
                                                        </x-button>
                                                    </td>
                                                </tr>
                                                @else
                                                <tr data-user-id="{{ $user_permission->user_id }}">
                                                    <td class="w-25 user-name">{{ $user_permission->user->name }}</td>
                                                    <x-input type="hidden" name="userPermissions[{{ $index }}][userId]" value="{{ $user_permission->user_id }}"></x-input>
                                                    @php
                                                    $permissions = [];
                                                    if ($user_permission->view) {
                                                    $permissions[] = 'view';
                                                    }
                                                    if ($user_permission->edit) {
                                                    $permissions[] = 'edit';
                                                    }
                                                    if ($user_permission->delete) {
                                                    $permissions[] = 'delete';
                                                    }
                                                    $permissionsString = json_encode($permissions);
                                                    @endphp
                                                    <x-input type="hidden" name="userPermissions[{{ $index }}][permissions]" value="{{ $permissionsString }}"></x-input>
                                                    <td class="w-50 text-center user-permissions">
                                                        @if ($user_permission->view)
                                                        <span class="badge bg-primary">View</span>
                                                        @endif
                                                        @if ($user_permission->edit)
                                                        <span class="badge bg-info">Edit</span>
                                                        @endif
                                                        @if ($user_permission->delete)
                                                        <span class="badge bg-danger">Delete</span>
                                                        @endif
                                                    </td>
                                                    <td class="w-25">
                                                        <x-button type="button" class="btn-danger remove-row">
                                                            <i class="align-middle" data-feather="trash-2"></i>
                                                        </x-button>
                                                    </td>
                                                </tr>
                                                @endif
                                                @endforeach
                                                @endif
                                                @if(old('userPermissions') && is_array(old('userPermissions') ))
                                                @foreach(old('userPermissions') as $index => $userPermission)
                                                @if(is_array($userPermission))
                                                <tr data-user-id="{{ $userPermission['userId'] }}">
                                                    <td class="w-25 user-name">
                                                        @php
                                                        $user = $users->find($userPermission['userId']);
                                                        @endphp
                                                        {{ $user ? $user->name : 'user Not Found' }}
                                                    </td>
                                                    <td class="w-50 text-center user-permissions">
                                                        @php
                                                        $permissions = json_decode($userPermission['permissions'], true);
                                                        @endphp
                                                        @if(is_array($permissions))
                                                        @foreach($permissions as $permission)
                                                        @php
                                                        $badgeClasses = [
                                                        'view' => 'badge bg-primary',
                                                        'edit' => 'badge bg-info',
                                                        'delete' => 'badge bg-danger',
                                                        ];
                                                        @endphp
                                                        <span class="{{ $badgeClasses[$permission] ?? 'badge bg-secondary' }}">{{ ucfirst($permission) }}</span>
                                                        @endforeach
                                                        @endif
                                                    </td>
                                                    <td class="w-25">
                                                        <button type="button" class="btn btn-danger remove-row">
                                                            <i class="align-middle" data-feather="trash-2"></i>
                                                        </button>
                                                    </td>
                                                    <!-- Hidden inputs to pass the data on form submission -->
                                                    <input type="hidden" name="userPermissions[{{ $index }}][userId]" value="{{ $userPermission['userId'] }}">
                                                    <input type="hidden" name="userPermissions[{{ $index }}][permissions]" value="{{ $userPermission['permissions'] }}">
                                                </tr>
                                                @endif
                                                @endforeach
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- ------User Model------>

                                    <div class="modal fade" id="AddUserModel" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-bs-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h1 class="modal-title fs-5" id="staticBackdropLabel">Add User</h1>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <form>
                                                        <select class="form-control" id="userSelect">
                                                            <option class="text-body-tertiary" value="" disabled>Select User</option>
                                                            @foreach ($users as $user)
                                                            <option value="{{ $user->id }}">{{$user->name}}</option>
                                                            @endforeach
                                                        </select>
                                                        <div class="d-flex my-4 w-75">
                                                            <div class="input-group">
                                                                <span class="input-group-text">
                                                                    <input type="checkbox" class="form-check-input" name="userPermissions[]" value="view" id="viewUser">
                                                                    <x-input-label for="viewUser" class="mt-2 ms-2" :value="__('View')" />
                                                                </span>
                                                            </div>
                                                            <div class="input-group">
                                                                <span class="input-group-text">
                                                                    <input type="checkbox" class="form-check-input" name="userPermissions[]" value="edit" id="editUser">
                                                                    <x-input-label for="editUser" class="mt-2 ms-2" :value="__('Edit')" />
                                                                </span>
                                                            </div>
                                                            <div class="input-group">
                                                                <span class="input-group-text">
                                                                    <input type="checkbox" class="form-check-input" name="userPermissions[]" value="delete" id="deleteUser">
                                                                    <x-input-label for="deleteUser" class="mt-2 ms-2" :value="__('Delete')" />
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                            <button type="button" class="btn btn-primary" id="saveUserPermission">Save</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- ----------Owners ------------->
                                <div class="border-bottom my-4">
                                    <h4 class="h4">Owners</h4>
                                </div>
                                <div class="col-12">
                                    <x-input-label for="notes" :value="__('Owners')" /><span class="text-danger">*</span>
                                    <select class="form-select {{ $errors->has('owners') ? 'is-invalid ' : '' }}" id="users" name="owners[]" multiple="multiple">
                                        <option disabled>Select User</option>
                                        @foreach ($users as $user)
                                        <option value="{{$user->id}}" @if(in_array($user->id, $document_owners->pluck('user_id')->toArray()))
                                            selected
                                            @endif {{ in_array($user->id, old('owners', [])) ? 'selected' : '' }}>{{$user->name}}</option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('owners')" />
                                </div>
                                <div class="d-block mt-3 text-end">
                                    <x-button class="btn-primary"><i class="mb-1 me-1" data-feather="edit"></i>Update Document</x-button>
                                </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection