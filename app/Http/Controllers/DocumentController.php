<?php

namespace App\Http\Controllers;

use App\Exports\DocumentsExport;
use App\Http\Requests\DocumentRequest;
use App\Models\Document_audit;
use App\Models\Document_detail;
use App\Models\Document_group_permission;
use App\Models\Document_image;
use App\Models\Document_main;
use App\Models\Document_notification;
use App\Models\Document_owner;
use App\Models\Document_type;
use App\Models\Document_user_permission;
use App\Models\Group;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class DocumentController extends Controller
{
    public function display(Request $request)
    {
        $sort_by = $request->input('sort_by', 'id');
        $sort_order = $request->input('sort_order', 'asc');
        $search = $request->input('search');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $user = Auth::user();
        $userId = $user->id;

        // Get user's document permissions
        $userPermissions = $user->document_main()
            ->withPivot('view', 'edit', 'delete')
            ->get()
            ->mapWithKeys(function ($item) {
                return [
                    $item->pivot->document_main_id => [
                        'view' => $item->pivot->view,
                        'edit' => $item->pivot->edit,
                        'delete' => $item->pivot->delete
                    ]
                ];
            })
            ->toArray();

        // Get group IDs the user belongs to
        $groupIds = $user->groups()->pluck('groups.id')->toArray();

        // Get group document permissions
        $groupPermissions = Document_main::whereHas('group_document_permission', function ($query) use ($groupIds) {
            $query->whereIn('groups.id', $groupIds)
                ->where('document_group_permissions.view', 1);
        })->with(['group_document_permission' => function ($query) {
            $query->select('document_main_id', 'group_id', 'view', 'edit', 'delete');
        }])->get()
            ->mapWithKeys(function ($document) {
                return [
                    $document->id => $document->group_document_permission->mapWithKeys(function ($groupPermission) {
                        return [
                            'view' => $groupPermission->pivot->view,
                            'edit' => $groupPermission->pivot->edit,
                            'delete' => $groupPermission->pivot->delete
                        ];
                    })->toArray()
                ];
            })
            ->toArray();

        // Query for documents with view permission either by user or group
        $query = Document_main::with('documentType')
            ->where(function ($query) use ($userId, $groupIds) {
                $query->whereHas('user_document_permission', function ($query) use ($userId) {
                    $query->where('users.id', $userId)
                        ->where('document_user_permissions.view', 1);
                })
                    ->orWhereHas('group_document_permission', function ($query) use ($groupIds) {
                        $query->whereIn('groups.id', $groupIds)
                            ->where('document_group_permissions.view', 1);
                    });
            });

        if ($search) {
            $query->whereHas("documentType", function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%');
            });
        }
        if ($startDate && $endDate) {
            $query->whereBetween('expiry', [$startDate, $endDate]);
        }

        $documents = $query->orderBy($sort_by, $sort_order)->paginate(5);

        return view('main/document', [
            'documents' => $documents,
            'sort_by' => $sort_by,
            'sort_order' => $sort_order,
            'search' => $search,
            'userPermissions' => $userPermissions,
            'groupPermissions' => $groupPermissions
        ]);
    }

    public function view($id)
    {
        $userId = Auth::id();
        $groupIds = Auth::user()->groups()->pluck('id');
        $hasPermission = Document_main::where('id', $id) // Ensure we're checking the specific document
            ->where(function ($query) use ($userId, $groupIds) {
                $query->whereHas('user_document_permission', function ($query) use ($userId) {
                    $query->where('users.id', $userId)
                        ->where('document_user_permissions.view', 1);
                })
                    ->orWhereHas('group_document_permission', function ($query) use ($groupIds) {
                        $query->whereIn('groups.id', $groupIds)
                            ->where('document_group_permissions.view', 1);
                    });
            })
            ->exists();
        if (!$hasPermission) {
            return redirect()->route('dashboard')->with('error', 'You do not have permission to view this document.');
        }
        $document = Document_main::with(['documentType', 'users'])->find($id);
        $fields = Document_detail::where('document_id', $id)->get();
        $notification = Document_notification::where('document_id', $id)->get();
        $images = Document_image::where('document_id', $id)->get();
        $imageData = [];

        if ($images->isNotEmpty()) {
            foreach ($images as $image) {
                $filePath = 'document_images/' . $image->name;
                if (Storage::exists($filePath)) {
                    $imageData[] = [
                        'url' => Storage::url($filePath),
                        'name' => $image->name,
                        'size' => Storage::size($filePath)
                    ];
                }
            }
        } else {
            $imageData[] = [
                'url' => null,
                'name' => 'No Image',
                'size' => 0
            ];
        }

        return view('main/view-document', [
            'document' => $document,
            'fields' => $fields,
            'notifications' => $notification,
            'owners' => $document->users,
            'images' => $imageData
        ]);
    }

    public function download($filename)
    {
        $filePath = storage_path('app/document_images/' . $filename);

        if (!file_exists($filePath)) {
            return redirect()->back()->with('error', 'File not found.');
        }
        return response()->download($filePath);
    }
    public function share()
    {
        return Excel::download(new DocumentsExport, 'documents.xlsx');
    }
    public function create()
    {
        $documentTypes = Document_type::with('documentFields')->get();
        $user_id = Auth::user()->id;
        $notification = Notification::where('user_id', $user_id)->get();
        $groups = Group::query()->get();
        $users = User::query()->get();
        return view('main/add-document', ['documentTypes' => $documentTypes, 'groups' => $groups, 'users' => $users, 'notifications' => $notification]);
    }
    public function uploadImage(Request $request)
    {
        $files = $request->file('file', []);
        $filePaths = [];
        foreach ($files as $file) {
            if ($file instanceof \Illuminate\Http\UploadedFile) {
                $originalName = $file->getClientOriginalName();
                $path = $file->storeAs('temp', $originalName);
                $filePaths[] = 'temp/' . $originalName;
            }
        }
        return response()->json(['file_paths' => $filePaths]);
    }
    public function removeImage(Request $request)
    {
        $filePath = $request->input('path');
        $documentId = $request->input('document_id');
        $fileName = basename($filePath);
        if (Storage::disk('local')->exists($filePath)) {
            Storage::disk('local')->delete($filePath);
            $deleted = Document_image::where('document_id', $documentId)
                ->where('name', $fileName)
                ->delete();

            if ($deleted) {
                return response()->json(['success' => true, 'message' => 'File and database record deleted successfully']);
            } else {
                return response()->json(['success' => false, 'message' => 'File deleted but database record not found']);
            }
        } else {
            return response()->json(['success' => false, 'message' => 'File not found']);
        }
    }
    public function restoreImage()
    {
        $files = Storage::files('document_images');

        $fileData = array_map(function ($filePath) {
            return [
                'name' => basename($filePath),
                'url' => Storage::url($filePath),
            ];
        }, $files);

        return view('your-view', ['files' => $fileData]);
    }
    public function store(DocumentRequest $request)
    {
        $validated = $request->validated();
        $loggedInUserId = Auth::id();
        $validated['user_id'] = $loggedInUserId;
        $document = Document_main::create($validated);
        $documentId = $document->id;

        if ($documentId) {
            $files = $request->validated('file_names', []);
            foreach ($files as $file) {
                $fileName = basename($file);
                $permanentPath = 'document_images/' . $fileName;
                $tempPath = 'temp/' . $fileName;

                if (Storage::disk('local')->exists($tempPath)) {
                    try {
                        Storage::disk('local')->move($tempPath, $permanentPath);
                        Document_image::create([
                            'document_id' => $documentId,
                            'name' => $fileName,
                        ]);
                    } catch (\Exception $e) {
                        continue;
                    }
                } else {
                    continue;
                }
            }
            if (isset($validated['fields']) && is_array($validated['fields'])) {
                foreach ($validated['fields'] as $fieldName => $fieldData) {
                    $detailData = [
                        'document_id' => $documentId,
                        'field_name' => $fieldName,
                        'field_type' => $fieldData['type'],
                        'field_value' => $fieldData['value']
                    ];
                    Document_detail::create($detailData);
                }
            }
            if (isset($validated['notifications']) && is_array($validated['notifications'])) {
                foreach ($validated['notifications'] as $notification) {
                    $notificationData = [
                        'document_id' => $documentId,
                        'name' => $notification['name'],
                        'day' => $notification['day'],
                    ];
                    Document_notification::create($notificationData);
                }
            }
            if (isset($validated['groupPermissions']) && is_array($validated['groupPermissions'])) {
                foreach ($validated['groupPermissions'] as $groupPermission) {
                    $groupId = $groupPermission['groupId'];
                    $permissions = json_decode($groupPermission['permissions'], true);
                    $pivotData = [
                        'view' => in_array('view', $permissions) ? 1 : 0,
                        'edit' => in_array('edit', $permissions) ? 1 : 0,
                        'delete' => in_array('delete', $permissions) ? 1 : 0,
                    ];
                    $document = Document_main::find($documentId);
                    $document->group_document_permission()->attach($groupId, $pivotData);
                }
            }
            if (isset($validated['userPermissions']) && is_array($validated['userPermissions'])) {
                foreach ($validated['userPermissions'] as $userPermission) {
                    $userId = $userPermission['userId'];
                    $permissions = json_decode($userPermission['permissions'], true);
                    $pivotData = [
                        'view' => in_array('view', $permissions) ? 1 : 0,
                        'edit' => in_array('edit', $permissions) ? 1 : 0,
                        'delete' => in_array('delete', $permissions) ? 1 : 0,
                    ];
                    $document = Document_main::find($documentId);
                    $document->user_document_permission()->attach($userId, $pivotData);
                }
            }
            $loggedInUserId = $document->user_id;
            $pivotDataForLoggedInUser = [
                'view' => 1,
                'edit' => 1,
                'delete' => 1,
            ];
            $document = Document_main::find($documentId);
            $document->user_document_permission()->attach($loggedInUserId, $pivotDataForLoggedInUser);

            if (isset($validated['owners']) && is_array($validated['owners'])) {
                $document->users()->attach($validated['owners']);
            }
        }

        //Document Audit
        $userId = Auth::user()->id;
        $auditData = [
            'document_id' => $document->id,
            'document_type_id' => $validated['document_type_id'],
            'user_id' => $userId,
            'action' => "created"
        ];
        Document_audit::create($auditData);
        return redirect()->to('document')->with('success', 'Document Created Successfully.');
    }

    public function edit(Request $request, $id)
    {
        $userId = Auth::id();
        $groupIds = Auth::user()->groups()->pluck('id');
        $hasPermission = Document_main::where('id', $id) 
            ->where(function ($query) use ($userId, $groupIds) {
                $query->whereHas('user_document_permission', function ($query) use ($userId) {
                    $query->where('users.id', $userId)
                        ->where('document_user_permissions.view', 1);
                })
                    ->orWhereHas('group_document_permission', function ($query) use ($groupIds) {
                        $query->whereIn('groups.id', $groupIds)
                            ->where('document_group_permissions.view', 1);
                    });
            })
            ->exists();
        if (!$hasPermission) {
            return redirect()->route('dashboard')->with('error', 'You do not have permission to view this document.');
        }
        $document = Document_main::where('id', $id)->with('documentType')->find($id);
        $creatorId = $document->user_id;
        $groups = Group::query()->get();
        $users = User::query()->get();
        $document_details = Document_detail::where('document_id', $id)->get();
        $Document_notification = Document_notification::where('document_id', $id)->get();
        $document_groups = Document_group_permission::where('document_main_id', $id)->with('group')->get();
        $document_users = Document_user_permission::where('document_main_id', $id)->get();
        $document_images = Document_image::where('document_id', $id)->get('name');
        $document_owners = Document_owner::where('document_main_id', $id)->get();
        $document_id = $id;
        $documentTypes = Document_type::all();
        return view('main/update-document', ['id' => $document_id, 'creatorId' => $creatorId, 'documentTypes' => $documentTypes, 'documents' => $document, 'groups' => $groups, 'users' => $users, 'document_details' => $document_details, 'document_groups' => $document_groups, 'document_users' => $document_users, 'document_notifications' => $Document_notification, 'document_images' => $document_images, 'document_owners' => $document_owners]);
    }

    public function update(DocumentRequest $request)
    {
        $documentId = $request->input('id');
        $validated = $request->validated();

        $document = Document_main::findOrFail($documentId);
        $isUpdating = $document->exists;
        $creatorId = $document->user_id;
        $document->update($validated);
        $files = $validated['file_names'] ?? [];

        $existingImages = Document_image::where('document_id', $documentId)->pluck('name')->toArray();
        $newImages = array_diff($files, $existingImages);
        $removedImages = array_diff($existingImages, $files);

        Document_image::where('document_id', $documentId)
            ->whereIn('name', $removedImages)
            ->delete();

        foreach ($newImages as $file) {
            $fileName = basename($file);
            $permanentPath = 'document_images/' . $fileName;
            $tempPath = 'temp/' . $fileName;

            if (Storage::disk('local')->exists($tempPath)) {
                try {
                    Storage::disk('local')->move($tempPath, $permanentPath);
                    Document_image::create([
                        'document_id' => $documentId,
                        'name' => $fileName,
                    ]);
                } catch (\Exception $e) {
                    continue;
                }
            }
        }


        if (isset($validated['fields']) && is_array($validated['fields'])) {
            foreach ($validated['fields'] as $fieldName => $fieldData) {
                Document_detail::updateOrCreate(
                    ['document_id' => $documentId, 'field_name' => $fieldName],
                    ['field_type' => $fieldData['type'], 'field_value' => $fieldData['value']]
                );
            }

            $existingFields = Document_detail::where('document_id', $documentId)->pluck('field_name')->toArray();
            $newFields = array_keys($validated['fields']);
            $removedFields = array_diff($existingFields, $newFields);

            Document_detail::where('document_id', $documentId)
                ->whereIn('field_name', $removedFields)
                ->delete();
        }

        if (isset($validated['notifications']) && is_array($validated['notifications'])) {
            $document->Document_notifications()->delete();
            foreach ($validated['notifications'] as $notification) {
                $name = $notification['name'];
                $day = $notification['day'];
                $document->Document_notifications()->create([
                    'name' => $name,
                    'day' => $day,
                    'document_id' => $document->id,
                ]);
            }
        }

        // Update group permissions
        if (isset($validated['groupPermissions']) && is_array($validated['groupPermissions'])) {
            $groupPermissions = [];
            foreach ($validated['groupPermissions'] as $groupPermission) {
                $groupId = $groupPermission['groupId'];
                $permissions = json_decode($groupPermission['permissions'], true);
                $pivotData = [
                    'view' => in_array('view', $permissions) ? 1 : 0,
                    'edit' => in_array('edit', $permissions) ? 1 : 0,
                    'delete' => in_array('delete', $permissions) ? 1 : 0,
                ];
                $groupPermissions[$groupId] = $pivotData;
            }
            $document->group_document_permission()->sync($groupPermissions);
        } else {
            $document->group_document_permission()->detach();
        }
        // Update user permissions
        if (isset($validated['userPermissions']) && is_array($validated['userPermissions'])) {
            $userPermissions = [];
            foreach ($validated['userPermissions'] as $userPermission) {
                $userId = $userPermission['userId'];
                $permissions = json_decode($userPermission['permissions'], true);
                $pivotData = [
                    'view' => in_array('view', $permissions) ? 1 : 0,
                    'edit' => in_array('edit', $permissions) ? 1 : 0,
                    'delete' => in_array('delete', $permissions) ? 1 : 0,
                ];
                $userPermissions[$userId] = $pivotData;
            }

            $document->user_document_permission()->sync($userPermissions);
            if ($isUpdating) {
                $userPermissions[$creatorId] = [
                    'view' => 1,
                    'edit' => 1,
                    'delete' => 1,
                ];
                $document->user_document_permission()->sync($userPermissions);
            }
        } else {
            $document->user_document_permission()->where('user_id', '!=', $creatorId)->detach();
        }

        // Update document owners
        if (isset($validated['owners']) && is_array($validated['owners'])) {
            $document->users()->sync($validated['owners']);
        }

        // Document Audit
        $userId = Auth::user();
        Document_audit::where('document_id', $documentId)->update(['action' => 'updated', 'user_id' => $userId->id]);

        return redirect()->to('document')->with('success', 'Document Updated Successfully.');
    }
    public function delete(Request $request)
    {
        $documentId = $request->input('delete');

        // Document Audit
        $userId = Auth::user();

        $document = Document_main::find($documentId);
        if ($document->delete()) {

            Document_audit::where('document_id', $documentId)->update(['action' => 'deleted', 'user_id' => $userId->id]);
            return redirect()->to('document')->with('success', 'Document Deleted Successfully.');
        } else {
            return redirect()->to('document')->with('error', 'Document Deleted Failed.');
        }
    }
}
