@extends('layouts.main')
@section('title','Add Document')
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
                        <form method="POST" id="myForm" action="{{route('add-document')}}" enctype="multipart/form-data">
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
                                        <select class="form-control {{ $errors->has('document_type_id') ? 'is-invalid' : '' }}" id="document_type" name="document_type_id">
                                            <option value="none" disabled>Select Document Type</option>
                                            @foreach ($documentTypes as $documentType)
                                            <option value="{{$documentType->id}}" {{$documentType->id==old('document_type_id') ? "selected" : " "}}>
                                                {{$documentType->name}}
                                            </option>
                                            @endforeach
                                        </select>
                                        <x-input-error :messages="$errors->get('document_type_id')" />
                                    </div>
                                    <div class="mb-3">
                                        <x-input-label for="note" :value="__('Notes')" />
                                        <textarea class="form-control" name="note" placeholder="Enter..."></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <div class="input-group ">
                                            <span class="input-group-text  {{ $errors->has('expiry') ? 'border border-danger' : '' }}" id="basic-addon1">Expiration Date<span class="text-danger">*</span></span>
                                            <x-input type="date" class="{{ $errors->has('expiry') ? 'is-invalid' : '' }}" name="expiry" :value="old('expiry')" />
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
                                        <div id="dynamic_fields_container"></div>
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
                                    <x-input-label for="notification" :value="__('Notifications')" />
                                    @foreach (old('notifications', $notifications) as $index => $notification)
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
                                    <div class="alert alert-info">
                                        Upload attachments from the <a href="{{ route('uploads') }}">Uploads</a> menu, then select them here.
                                    </div>

                                    <div class="border p-3">
                                        @php
                                            $selectedFiles = old('file_names', []);
                                        @endphp

                                        @if(isset($uploads) && $uploads->isNotEmpty())
                                            <div class="row">
                                                @foreach($uploads as $upload)
                                                    <div class="col-6 col-xl-4">
                                                        <div class="form-check my-2">
                                                            <input class="form-check-input" type="checkbox" name="file_names[]" value="{{ $upload->file_name }}" id="upload_{{ $upload->id }}" {{ in_array($upload->file_name, $selectedFiles) ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="upload_{{ $upload->id }}">
                                                                {{ $upload->file_name }}
                                                            </label>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="text-muted">No uploads yet. Go to <a href="{{ route('uploads') }}">Uploads</a> to add files.</div>
                                        @endif
                                    </div>
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
                                            <i class="mb-1 me-1" data-feather="plus"></i>Add Group
                                        </x-button>
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
                                                @if(old('groupPermissions') && is_array(old('groupPermissions')))
                                                @foreach(old('groupPermissions') as $index => $groupPermission)
                                                @if(is_array($groupPermission))
                                                <tr data-group-id="{{ $groupPermission['groupId'] }}">
                                                    <td class="w-25 group-name">
                                                        @php
                                                        $group = $groups->find($groupPermission['groupId']);
                                                        @endphp
                                                        {{ $group ? $group->name : 'Group Not Found' }}
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
                                                    <!-- Hidden inputs to pass the data on form submission -->
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
                                                            <option class="text-body-tertiary" value="" disabled>Select
                                                                Group</option>
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

                                    <!-- -----------User Permission--------------- -->

                                    <div class="w-100 d-flex justify-content-end">
                                        <x-button class="btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#AddUserModel">
                                            <i class="mb-1 me-1" data-feather="plus"></i>Add User
                                        </x-button>
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
                                                @if(old('userPermissions') && is_array(old('userPermissions')))
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
                                                            <option class="text-body-tertiary" value="" disabled>Select
                                                                User</option>
                                                            @foreach ($users as $user)
                                                            <option value="{{ $user->id }}">{{$user->name}}</option>
                                                            @endforeach
                                                        </select>
                                                        <div class="d-flex mt-4 w-75">
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

                                    <!-- ----------Owners ------------->

                                    <div class="border-bottom my-4">
                                        <h4 class="h4">Owners</h4>
                                    </div>
                                    <div class="col-12">
                                        <x-input-label for="owners" :value="__('Owners')" /><span class="text-danger">*</span>
                                        <select class="form-select  {{ $errors->has('owners') ? 'is-invalid' : '' }}" id="select" name="owners[]" multiple="multiple">
                                            <option value="none" disabled>Select User</option>
                                            @foreach ($users as $user)
                                            <option value="{{ $user->id }}" {{ in_array($user->id, old('owners', [])) ? 'selected' : '' }}>
                                                {{ $user->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                        <x-input-error :messages="$errors->get('owners')" />
                                    </div>
                                </div>
                                <div class="d-block mt-3 text-end">
                                    <x-button class="btn-primary"><i class="mb-1 me-1" data-feather="save"></i>Save
                                        Document</x-button>
                                </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection