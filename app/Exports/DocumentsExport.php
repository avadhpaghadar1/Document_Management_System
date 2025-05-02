<?php

namespace App\Exports;

use App\Models\Document_main;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DocumentsExport implements FromCollection, WithHeadings
{
    public function collection()
    {
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

        // Merge user and group permissions
        $mergedPermissions = [];
        foreach ($userPermissions as $docId => $permissions) {
            $mergedPermissions[$docId] = $permissions;
        }

        foreach ($groupPermissions as $docId => $permissions) {
            if (!isset($mergedPermissions[$docId])) {
                $mergedPermissions[$docId] = $permissions;
            } else {
                $mergedPermissions[$docId]['view'] = $mergedPermissions[$docId]['view'] || $permissions['view'];
                $mergedPermissions[$docId]['edit'] = $mergedPermissions[$docId]['edit'] || $permissions['edit'];
                $mergedPermissions[$docId]['delete'] = $mergedPermissions[$docId]['delete'] || $permissions['delete'];
            }
        }

        // Filter documents based on merged permissions
        $documentIdsWithViewPermission = array_keys(array_filter($mergedPermissions, function ($permissions) {
            return $permissions['view'] == 1;
        }));

        return Document_main::with('documentType', 'documentDetail')
            ->whereIn('id', $documentIdsWithViewPermission)
            ->take(10)
            ->get()
            ->map(function ($document) {
                return [
                    'Document ID' => $document->id,
                    'Document Type Name' => $document->documentType->name ?? 'N/A',
                    'Document Field Name' => $document->documentDetail->field_name ?? 'N/A',
                    'Document Field Value' => $document->documentDetail->field_value ?? 'N/A',
                    'Expiry' => $document->expiry,
                ];
            });
    }


    public function headings(): array
    {
        return [
            'Document ID',
            'Document Type Name',
            'Document Field Name',
            'Document Field Value',
            'Expiry',
        ];
    }
}
