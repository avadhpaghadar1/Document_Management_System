<?php

namespace App\Http\Controllers\Documents;

use App\Exports\DocumentsExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\DocumentRequest;
use App\Models\Document_audit;
use App\Models\Document_detail;
use App\Models\Document_group_permission;
use App\Models\Document_image;
use App\Models\Document_file_analysis;
use App\Models\Document_upload;
use App\Models\Document_main;
use App\Models\Document_version;
use App\Models\Shared_link;
use App\Models\Document_notification;
use App\Models\Document_owner;
use App\Models\Document_type;
use App\Models\Document_user_permission;
use App\Models\Group;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Services\DocumentAnalyzer;
use App\Services\OcrService;

class DocumentController extends Controller
{
    use AuthorizesRequests;

    private DocumentAnalyzer $documentAnalyzer;
    private OcrService $ocrService;

    public function __construct(DocumentAnalyzer $documentAnalyzer, OcrService $ocrService)
    {
        $this->documentAnalyzer = $documentAnalyzer;
        $this->ocrService = $ocrService;
    }

    public function display(Request $request)
    {
        $allowedSortBy = ['id', 'document_type_id', 'expiry'];
        $sort_by = $request->input('sort_by', 'id');
        if (!in_array($sort_by, $allowedSortBy, true)) {
            $sort_by = 'id';
        }

        $sort_order = strtolower((string) $request->input('sort_order', 'asc'));
        if (!in_array($sort_order, ['asc', 'desc'], true)) {
            $sort_order = 'asc';
        }
        $search = $request->input('search');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $status = $request->input('status', 'all');
        if (!in_array($status, ['all', 'valid', 'expired'], true)) {
            $status = 'all';
        }
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
            $query->where(function ($q) use ($search) {
                if (ctype_digit((string) $search)) {
                    $q->orWhere('id', (int) $search);
                }

                $q->orWhere('note', 'like', '%' . $search . '%')
                    ->orWhereHas('documentType', function ($sub) use ($search) {
                        $sub->where('name', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('users', function ($sub) use ($search) {
                        $sub->where('name', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('fileAnalyses', function ($sub) use ($search) {
                        $sub->where('ocr_text', 'like', '%' . $search . '%');
                    });
            });
        }
        if ($startDate && $endDate) {
            $query->whereBetween('expiry', [$startDate, $endDate]);
        }

        if ($status === 'valid') {
            $query->where('expiry', '>', now()->toDateString());
        } elseif ($status === 'expired') {
            $query->where('expiry', '<=', now()->toDateString());
        }

        $documents = $query->orderBy($sort_by, $sort_order)->paginate(5);

        return view('documents/index', [
            'documents' => $documents,
            'sort_by' => $sort_by,
            'sort_order' => $sort_order,
            'search' => $search,
            'status' => $status,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'userPermissions' => $userPermissions,
            'groupPermissions' => $groupPermissions
        ]);
    }

    public function view($id)
    {
        $userId = Auth::id();
        $groupIds = Auth::user()->groups()->pluck('id');
        if (!$this->hasDocumentPermission($id, 'view', $userId, $groupIds)) {
            return redirect()->route('dashboard')->with('error', 'You do not have permission to view this document.');
        }

        $canEdit = $this->hasDocumentPermission($id, 'edit', $userId, $groupIds);
        $canDelete = $this->hasDocumentPermission($id, 'delete', $userId, $groupIds);

        $document = Document_main::with(['documentType', 'users'])->find($id);
        $fields = Document_detail::where('document_id', $id)->get();
        $notification = Document_notification::where('document_id', $id)->get();
        $images = Document_image::where('document_id', $id)->get();
        $sharedLinks = Shared_link::query()
            ->where('document_id', $id)
            ->orderByDesc('id')
            ->get();
        $versionsCount = Document_version::query()->where('document_id', $id)->count();
        $analysesByFileName = Document_file_analysis::query()
            ->where('document_id', $id)
            ->get()
            ->keyBy('file_name');
        $imageData = [];

        if ($images->isNotEmpty()) {
            foreach ($images as $image) {
                $filePath = 'document_images/' . $image->name;
                if (Storage::exists($filePath)) {
                    $analysis = $analysesByFileName->get($image->name);
                    $imageData[] = [
                        'url' => Storage::url($filePath),
                        'name' => $image->name,
                        'size' => Storage::size($filePath),
                        'analysis' => $analysis,
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

        return view('documents/view', [
            'document' => $document,
            'fields' => $fields,
            'notifications' => $notification,
            'owners' => $document->users,
            'images' => $imageData,
            'sharedLinks' => $sharedLinks,
            'versionsCount' => $versionsCount,
            'canEdit' => $canEdit,
            'canDelete' => $canDelete,
        ]);
    }

    public function download($document, $filename)
    {
        $documentId = (int) $document;
        $filename = basename((string) $filename);

        $userId = Auth::id();
        $groupIds = Auth::user()->groups()->pluck('id');
        if (!$this->hasDocumentPermission($documentId, 'view', $userId, $groupIds)) {
            return redirect()->back()->with('error', 'You do not have permission to download this file.');
        }

        $hasFile = Document_image::query()
            ->where('document_id', $documentId)
            ->where('name', $filename)
            ->exists();

        if (!$hasFile) {
            return redirect()->back()->with('error', 'File not found.');
        }

        $filePath = storage_path('app/document_images/' . $filename);

        if (!file_exists($filePath)) {
            return redirect()->back()->with('error', 'File not found.');
        }
        return response()->download($filePath);
    }
    public function share()
    {
        try {
            $this->authorize('export_document');
        } catch (AuthorizationException) {
            return redirect()->back()->with('error', 'You do not have permission to perform this action.');
        }
        return Excel::download(new DocumentsExport, 'documents.xlsx');
    }
    public function create()
    {
        $documentTypes = Document_type::with('documentFields')->get();
        $user_id = Auth::user()->id;
        $notification = Notification::where('user_id', $user_id)->get();
        $groups = Group::query()->get();
        $users = User::query()->get();
        $uploads = Document_upload::query()
            ->where('user_id', $user_id)
            ->orderByDesc('id')
            ->get();

        return view('documents/create', [
            'documentTypes' => $documentTypes,
            'groups' => $groups,
            'users' => $users,
            'notifications' => $notification,
            'uploads' => $uploads,
        ]);
    }
    public function uploadImage(Request $request)
    {
        $request->validate([
            'file' => 'required|array',
            'file.*' => 'file|max:20480|mimes:pdf,jpg,jpeg,png,doc,gif,xls',
        ]);

        $files = $request->file('file', []);
        $filePaths = [];
        foreach ($files as $file) {
            if ($file instanceof \Illuminate\Http\UploadedFile) {
                $originalName = $file->getClientOriginalName();
                $base = pathinfo($originalName, PATHINFO_FILENAME);
                $ext = strtolower((string) $file->getClientOriginalExtension());
                $safeBase = preg_replace('/[^A-Za-z0-9_\-]+/', '_', (string) $base);
                $safeBase = trim((string) $safeBase, '_');
                if ($safeBase === '') {
                    $safeBase = 'upload';
                }
                $unique = $safeBase . '_' . now()->format('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;

                $path = $file->storeAs('temp', $unique);
                $filePaths[] = 'temp/' . basename((string) $path);
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
            $files = array_map('basename', (array) $request->validated('file_names', []));
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

                        Document_upload::query()
                            ->where('user_id', $loggedInUserId)
                            ->where('file_name', $fileName)
                            ->delete();

                        $analysis = $this->documentAnalyzer->analyzeLocalStoredFile($permanentPath);
                        $analysisRow = Document_file_analysis::query()->updateOrCreate(
                            ['document_id' => $documentId, 'file_name' => $fileName],
                            array_merge($analysis, ['analyzed_at' => now()])
                        );

                        try {
                            $language = (string) env('OCR_LANGUAGE', 'eng');
                            $ocr = $this->ocrService->extractTextFromLocalStoredFile($permanentPath, $language);
                            $analysisRow->update([
                                'ocr_text' => $ocr['text'],
                                'ocr_error' => $ocr['error'],
                                'ocr_engine' => $ocr['engine'],
                                'ocr_language' => $ocr['language'],
                                'ocr_completed_at' => now(),
                            ]);
                        } catch (\Throwable $e) {
                            $analysisRow->update([
                                'ocr_text' => null,
                                'ocr_error' => 'OCR exception: ' . $e->getMessage(),
                                'ocr_engine' => null,
                                'ocr_language' => (string) env('OCR_LANGUAGE', 'eng'),
                                'ocr_completed_at' => now(),
                            ]);
                        }
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

        $this->createVersionSnapshot($document);

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
                        ->where('document_user_permissions.edit', 1);
                })
                    ->orWhereHas('group_document_permission', function ($query) use ($groupIds) {
                        $query->whereIn('groups.id', $groupIds)
                            ->where('document_group_permissions.edit', 1);
                    });
            })
            ->exists();
        if (!$hasPermission) {
            return redirect()->route('dashboard')->with('error', 'You do not have permission to edit this document.');
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
        return view('documents/edit', ['id' => $document_id, 'creatorId' => $creatorId, 'documentTypes' => $documentTypes, 'documents' => $document, 'groups' => $groups, 'users' => $users, 'document_details' => $document_details, 'document_groups' => $document_groups, 'document_users' => $document_users, 'document_notifications' => $Document_notification, 'document_images' => $document_images, 'document_owners' => $document_owners]);
    }

    public function update(DocumentRequest $request)
    {
        $documentId = $request->input('id');
        $validated = $request->validated();

        $userId = Auth::id();
        $groupIds = Auth::user()->groups()->pluck('id');
        $hasPermission = Document_main::where('id', $documentId)
            ->where(function ($query) use ($userId, $groupIds) {
                $query->whereHas('user_document_permission', function ($query) use ($userId) {
                    $query->where('users.id', $userId)
                        ->where('document_user_permissions.edit', 1);
                })
                    ->orWhereHas('group_document_permission', function ($query) use ($groupIds) {
                        $query->whereIn('groups.id', $groupIds)
                            ->where('document_group_permissions.edit', 1);
                    });
            })
            ->exists();
        if (!$hasPermission) {
            return redirect()->route('dashboard')->with('error', 'You do not have permission to edit this document.');
        }

        $document = Document_main::findOrFail($documentId);
        $isUpdating = $document->exists;
        $creatorId = $document->user_id;
        $document->update($validated);
        $files = array_map('basename', (array) ($validated['file_names'] ?? []));

        $existingImages = Document_image::where('document_id', $documentId)->pluck('name')->toArray();
        $newImages = array_diff($files, $existingImages);
        $removedImages = array_diff($existingImages, $files);

        Document_image::where('document_id', $documentId)
            ->whereIn('name', $removedImages)
            ->delete();

        if (!empty($removedImages)) {
            Document_file_analysis::query()
                ->where('document_id', $documentId)
                ->whereIn('file_name', $removedImages)
                ->delete();
        }

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

                    Document_upload::query()
                        ->where('user_id', $userId)
                        ->where('file_name', $fileName)
                        ->delete();

                    $analysis = $this->documentAnalyzer->analyzeLocalStoredFile($permanentPath);
                    $analysisRow = Document_file_analysis::query()->updateOrCreate(
                        ['document_id' => $documentId, 'file_name' => $fileName],
                        array_merge($analysis, ['analyzed_at' => now()])
                    );

                    try {
                        $language = (string) env('OCR_LANGUAGE', 'eng');
                        $ocr = $this->ocrService->extractTextFromLocalStoredFile($permanentPath, $language);
                        $analysisRow->update([
                            'ocr_text' => $ocr['text'],
                            'ocr_error' => $ocr['error'],
                            'ocr_engine' => $ocr['engine'],
                            'ocr_language' => $ocr['language'],
                            'ocr_completed_at' => now(),
                        ]);
                    } catch (\Throwable $e) {
                        $analysisRow->update([
                            'ocr_text' => null,
                            'ocr_error' => 'OCR exception: ' . $e->getMessage(),
                            'ocr_engine' => null,
                            'ocr_language' => (string) env('OCR_LANGUAGE', 'eng'),
                            'ocr_completed_at' => now(),
                        ]);
                    }
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

        $this->createVersionSnapshot($document);

        return redirect()->to('document')->with('success', 'Document Updated Successfully.');
    }
    public function delete(Request $request)
    {
        $documentId = $request->input('delete');

        $userId = Auth::id();
        $groupIds = Auth::user()->groups()->pluck('id');
        $hasPermission = Document_main::where('id', $documentId)
            ->where(function ($query) use ($userId, $groupIds) {
                $query->whereHas('user_document_permission', function ($query) use ($userId) {
                    $query->where('users.id', $userId)
                        ->where('document_user_permissions.delete', 1);
                })
                    ->orWhereHas('group_document_permission', function ($query) use ($groupIds) {
                        $query->whereIn('groups.id', $groupIds)
                            ->where('document_group_permissions.delete', 1);
                    });
            })
            ->exists();
        if (!$hasPermission) {
            return redirect()->route('dashboard')->with('error', 'You do not have permission to delete this document.');
        }

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

    public function submitForApproval($id)
    {
        $documentId = (int) $id;
        $userId = Auth::id();
        $groupIds = Auth::user()->groups()->pluck('id');

        if (!$this->hasDocumentPermission($documentId, 'edit', $userId, $groupIds)) {
            return redirect()->back()->with('error', 'You do not have permission to submit this document.');
        }

        $document = Document_main::query()->findOrFail($documentId);
        if ($document->approval_status !== 'draft' && $document->approval_status !== 'rejected') {
            return redirect()->back()->with('error', 'Only draft/rejected documents can be submitted.');
        }

        $document->update([
            'approval_status' => 'pending',
            'approved_by' => null,
            'approved_at' => null,
            'rejected_reason' => null,
        ]);

        Document_audit::create([
            'document_id' => $documentId,
            'document_type_id' => $document->document_type_id,
            'user_id' => $userId,
            'action' => 'submitted_for_approval',
        ]);

        return redirect()->back()->with('success', 'Document submitted for approval.');
    }

    public function approve($id)
    {
        try {
            $this->authorize('approve_document');
        } catch (AuthorizationException) {
            return redirect()->back()->with('error', 'You do not have permission to approve documents.');
        }

        $documentId = (int) $id;
        $userId = Auth::id();
        $groupIds = Auth::user()->groups()->pluck('id');

        if (!$this->hasDocumentPermission($documentId, 'view', $userId, $groupIds)) {
            return redirect()->back()->with('error', 'You do not have permission to view this document.');
        }

        $document = Document_main::query()->findOrFail($documentId);
        if ($document->approval_status !== 'pending') {
            return redirect()->back()->with('error', 'Only pending documents can be approved.');
        }

        $document->update([
            'approval_status' => 'approved',
            'approved_by' => $userId,
            'approved_at' => now(),
            'rejected_reason' => null,
        ]);

        Document_audit::create([
            'document_id' => $documentId,
            'document_type_id' => $document->document_type_id,
            'user_id' => $userId,
            'action' => 'approved',
        ]);

        return redirect()->back()->with('success', 'Document approved.');
    }

    public function reject(Request $request, $id)
    {
        try {
            $this->authorize('approve_document');
        } catch (AuthorizationException) {
            return redirect()->back()->with('error', 'You do not have permission to reject documents.');
        }

        $documentId = (int) $id;
        $userId = Auth::id();
        $groupIds = Auth::user()->groups()->pluck('id');
        if (!$this->hasDocumentPermission($documentId, 'view', $userId, $groupIds)) {
            return redirect()->back()->with('error', 'You do not have permission to view this document.');
        }

        $validated = $request->validate([
            'rejected_reason' => 'required|string|max:255',
        ]);

        $document = Document_main::query()->findOrFail($documentId);
        if ($document->approval_status !== 'pending') {
            return redirect()->back()->with('error', 'Only pending documents can be rejected.');
        }

        $document->update([
            'approval_status' => 'rejected',
            'approved_by' => null,
            'approved_at' => null,
            'rejected_reason' => $validated['rejected_reason'],
        ]);

        Document_audit::create([
            'document_id' => $documentId,
            'document_type_id' => $document->document_type_id,
            'user_id' => $userId,
            'action' => 'rejected',
        ]);

        return redirect()->back()->with('success', 'Document rejected.');
    }

    public function versions($id)
    {
        try {
            $this->authorize('view_document_versions');
        } catch (AuthorizationException) {
            return redirect()->back()->with('error', 'You do not have permission to view versions.');
        }

        $documentId = (int) $id;
        $userId = Auth::id();
        $groupIds = Auth::user()->groups()->pluck('id');
        if (!$this->hasDocumentPermission($documentId, 'view', $userId, $groupIds)) {
            return redirect()->route('dashboard')->with('error', 'You do not have permission to view this document.');
        }

        $document = Document_main::with('documentType')->findOrFail($documentId);
        $versions = Document_version::query()
            ->with('creator')
            ->where('document_id', $documentId)
            ->orderByDesc('version')
            ->get();

        return view('documents/versions', [
            'document' => $document,
            'versions' => $versions,
        ]);
    }

    public function restoreVersion($id)
    {
        try {
            $this->authorize('restore_document_version');
        } catch (AuthorizationException) {
            return redirect()->back()->with('error', 'You do not have permission to restore versions.');
        }

        $versionId = (int) $id;
        $version = Document_version::query()->findOrFail($versionId);

        $userId = Auth::id();
        $groupIds = Auth::user()->groups()->pluck('id');
        if (!$this->hasDocumentPermission($version->document_id, 'edit', $userId, $groupIds)) {
            return redirect()->back()->with('error', 'You do not have permission to restore this version.');
        }

        $snapshot = (array) ($version->snapshot ?? []);

        DB::transaction(function () use ($version, $snapshot, $userId) {
            $document = Document_main::query()->findOrFail($version->document_id);

            $docData = (array) ($snapshot['document'] ?? []);
            $document->forceFill([
                'document_type_id' => $docData['document_type_id'] ?? $document->document_type_id,
                'note' => $docData['note'] ?? $document->note,
                'expiry' => $docData['expiry'] ?? $document->expiry,
                'approval_status' => $docData['approval_status'] ?? $document->approval_status,
                'approved_by' => $docData['approved_by'] ?? $document->approved_by,
                'approved_at' => $docData['approved_at'] ?? $document->approved_at,
                'rejected_reason' => $docData['rejected_reason'] ?? $document->rejected_reason,
            ])->save();

            $owners = array_values(array_filter((array) ($snapshot['owners'] ?? [])));
            if (!empty($owners)) {
                $document->users()->sync($owners);
            }

            $details = (array) ($snapshot['details'] ?? []);
            Document_detail::where('document_id', $document->id)->delete();
            foreach ($details as $detail) {
                if (!is_array($detail) || empty($detail['field_name'])) {
                    continue;
                }
                Document_detail::create([
                    'document_id' => $document->id,
                    'field_name' => (string) $detail['field_name'],
                    'field_type' => (string) ($detail['field_type'] ?? 'text'),
                    'field_value' => (string) ($detail['field_value'] ?? ''),
                ]);
            }

            $notifications = (array) ($snapshot['notifications'] ?? []);
            Document_notification::where('document_id', $document->id)->delete();
            foreach ($notifications as $notif) {
                if (!is_array($notif) || empty($notif['name'])) {
                    continue;
                }
                Document_notification::create([
                    'document_id' => $document->id,
                    'name' => (string) $notif['name'],
                    'day' => (int) ($notif['day'] ?? 0),
                ]);
            }

            $files = array_values(array_filter((array) ($snapshot['images'] ?? [])));
            $existing = Document_image::where('document_id', $document->id)->pluck('name')->toArray();
            $removed = array_diff($existing, $files);
            if (!empty($removed)) {
                Document_file_analysis::query()
                    ->where('document_id', $document->id)
                    ->whereIn('file_name', $removed)
                    ->delete();
            }
            Document_image::where('document_id', $document->id)->delete();
            foreach ($files as $file) {
                $name = basename((string) $file);
                if ($name === '') {
                    continue;
                }
                if (!Storage::disk('local')->exists('document_images/' . $name)) {
                    continue;
                }
                Document_image::create([
                    'document_id' => $document->id,
                    'name' => $name,
                ]);
            }

            $groupPermissions = (array) ($snapshot['group_permissions'] ?? []);
            $groupSync = [];
            foreach ($groupPermissions as $row) {
                if (!is_array($row) || empty($row['group_id'])) {
                    continue;
                }
                $groupSync[(int) $row['group_id']] = [
                    'view' => !empty($row['view']) ? 1 : 0,
                    'edit' => !empty($row['edit']) ? 1 : 0,
                    'delete' => !empty($row['delete']) ? 1 : 0,
                ];
            }
            $document->group_document_permission()->sync($groupSync);

            $userPermissions = (array) ($snapshot['user_permissions'] ?? []);
            $userSync = [];
            foreach ($userPermissions as $row) {
                if (!is_array($row) || empty($row['user_id'])) {
                    continue;
                }
                $userSync[(int) $row['user_id']] = [
                    'view' => !empty($row['view']) ? 1 : 0,
                    'edit' => !empty($row['edit']) ? 1 : 0,
                    'delete' => !empty($row['delete']) ? 1 : 0,
                ];
            }
            if (!empty($userSync)) {
                // Always ensure creator retains full rights.
                $userSync[(int) $document->user_id] = ['view' => 1, 'edit' => 1, 'delete' => 1];
                $document->user_document_permission()->sync($userSync);
            }

            Document_audit::create([
                'document_id' => $document->id,
                'document_type_id' => $document->document_type_id,
                'user_id' => $userId,
                'action' => 'restored_version_' . $version->version,
            ]);
        });

        return redirect()->route('view-document', ['id' => $version->document_id])->with('success', 'Version restored.');
    }

    public function recycleBin(Request $request)
    {
        try {
            $this->authorize('view_recycle_bin');
        } catch (AuthorizationException) {
            return redirect()->back()->with('error', 'You do not have permission to view the recycle bin.');
        }

        $user = Auth::user();
        $userId = $user->id;
        $groupIds = $user->groups()->pluck('groups.id')->toArray();

        $query = Document_main::onlyTrashed()
            ->with('documentType')
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

        $documents = $query->orderByDesc('deleted_at')->paginate(10);

        return view('documents/recycle-bin', [
            'documents' => $documents,
        ]);
    }

    public function restoreDeleted($id)
    {
        try {
            $this->authorize('restore_document');
        } catch (AuthorizationException) {
            return redirect()->back()->with('error', 'You do not have permission to restore documents.');
        }

        $documentId = (int) $id;
        $document = Document_main::onlyTrashed()->findOrFail($documentId);

        $userId = Auth::id();
        $groupIds = Auth::user()->groups()->pluck('id');
        if (!$this->hasDocumentPermission($documentId, 'view', $userId, $groupIds, true)) {
            return redirect()->back()->with('error', 'You do not have permission to restore this document.');
        }

        $document->restore();
        Document_audit::create([
            'document_id' => $documentId,
            'document_type_id' => $document->document_type_id,
            'user_id' => $userId,
            'action' => 'restored',
        ]);

        return redirect()->back()->with('success', 'Document restored.');
    }

    public function forceDelete($id)
    {
        try {
            $this->authorize('force_delete_document');
        } catch (AuthorizationException) {
            return redirect()->back()->with('error', 'You do not have permission to permanently delete documents.');
        }

        $documentId = (int) $id;
        $document = Document_main::onlyTrashed()->findOrFail($documentId);

        $userId = Auth::id();
        $groupIds = Auth::user()->groups()->pluck('id');
        if (!$this->hasDocumentPermission($documentId, 'delete', $userId, $groupIds, true)) {
            return redirect()->back()->with('error', 'You do not have permission to delete this document.');
        }

        $document->forceDelete();

        Document_audit::create([
            'document_id' => $documentId,
            'document_type_id' => $document->document_type_id,
            'user_id' => $userId,
            'action' => 'force_deleted',
        ]);

        return redirect()->back()->with('success', 'Document permanently deleted.');
    }

    private function createVersionSnapshot(Document_main $document): void
    {
        try {
            $document->refresh();
        } catch (\Throwable) {
        }

        $docId = (int) $document->id;
        if ($docId <= 0) {
            return;
        }

        $nextVersion = (int) (Document_version::query()->where('document_id', $docId)->max('version') ?? 0) + 1;

        $snapshot = [
            'document' => [
                'document_type_id' => $document->document_type_id,
                'note' => $document->note,
                'expiry' => optional($document->expiry)->toDateString() ?? (string) $document->expiry,
                'approval_status' => $document->approval_status,
                'approved_by' => $document->approved_by,
                'approved_at' => optional($document->approved_at)->toISOString(),
                'rejected_reason' => $document->rejected_reason,
            ],
            'owners' => $document->users()->pluck('users.id')->toArray(),
            'details' => Document_detail::query()
                ->where('document_id', $docId)
                ->get(['field_name', 'field_type', 'field_value'])
                ->map(fn ($r) => [
                    'field_name' => $r->field_name,
                    'field_type' => $r->field_type,
                    'field_value' => $r->field_value,
                ])
                ->toArray(),
            'notifications' => Document_notification::query()
                ->where('document_id', $docId)
                ->get(['name', 'day'])
                ->map(fn ($r) => [
                    'name' => $r->name,
                    'day' => $r->day,
                ])
                ->toArray(),
            'images' => Document_image::query()
                ->where('document_id', $docId)
                ->pluck('name')
                ->toArray(),
            'group_permissions' => Document_group_permission::query()
                ->where('document_main_id', $docId)
                ->get(['group_id', 'view', 'edit', 'delete'])
                ->map(fn ($r) => [
                    'group_id' => $r->group_id,
                    'view' => $r->view,
                    'edit' => $r->edit,
                    'delete' => $r->delete,
                ])
                ->toArray(),
            'user_permissions' => Document_user_permission::query()
                ->where('document_main_id', $docId)
                ->get(['user_id', 'view', 'edit', 'delete'])
                ->map(fn ($r) => [
                    'user_id' => $r->user_id,
                    'view' => $r->view,
                    'edit' => $r->edit,
                    'delete' => $r->delete,
                ])
                ->toArray(),
        ];

        Document_version::query()->create([
            'document_id' => $docId,
            'version' => $nextVersion,
            'created_by' => Auth::id(),
            'snapshot' => $snapshot,
            'created_at' => now(),
        ]);
    }

    private function hasDocumentPermission(int $documentId, string $permission, int $userId, $groupIds, bool $includeTrashed = false): bool
    {
        $docQuery = Document_main::query();
        if ($includeTrashed) {
            $docQuery->withTrashed();
        }

        return $docQuery
            ->where('id', $documentId)
            ->where(function ($query) use ($permission, $userId, $groupIds) {
                $query->whereHas('user_document_permission', function ($query) use ($permission, $userId) {
                    $query->where('users.id', $userId)
                        ->where('document_user_permissions.' . $permission, 1);
                })
                    ->orWhereHas('group_document_permission', function ($query) use ($permission, $groupIds) {
                        $query->whereIn('groups.id', $groupIds)
                            ->where('document_group_permissions.' . $permission, 1);
                    });
            })
            ->exists();
    }
}
