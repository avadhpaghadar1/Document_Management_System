<?php

namespace App\Http\Controllers;

use App\Models\Document_main;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function display()
    {
        $user = Auth::user();
        $userId = $user->id;

        $today = now()->toDateString();
        $in7 = now()->addDays(7)->toDateString();
        $in30 = now()->addDays(30)->toDateString();
        $in90 = now()->addDays(90)->toDateString();

        $visibleDocsQuery = Document_main::with('documentType')
            ->whereNull('deleted_at')
            ->where(function ($query) use ($userId, $user) {
                $query->whereHas('user_document_permission', function ($query) use ($userId) {
                    $query->where('users.id', $userId)
                        ->where('document_user_permissions.view', 1);
                })
                    ->orWhereHas('group_document_permission', function ($query) use ($user) {
                        $query->whereIn('groups.id', $user->groups()->pluck('groups.id')->toArray())
                            ->where('document_group_permissions.view', 1);
                    });
            });

        $total = (clone $visibleDocsQuery)->count();
        $expired = (clone $visibleDocsQuery)->where('expiry', '<=', $today)->count();
        $expiring7 = (clone $visibleDocsQuery)->whereBetween('expiry', [$today, $in7])->count();
        $expiring30 = (clone $visibleDocsQuery)->whereBetween('expiry', [$today, $in30])->count();
        $expiring90 = (clone $visibleDocsQuery)->whereBetween('expiry', [$today, $in90])->count();

        // Get documents with view permissions by user or group
        $documents = (clone $visibleDocsQuery)
            ->orderByDesc('id')
            ->take(10)
            ->get();

        // Get user-specific permissions
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

        // Get group-specific permissions
        $groupIds = $user->groups()->pluck('groups.id')->toArray();
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

        return view('dashboard/index', [
            'documents' => $documents,
            'userPermissions' => $userPermissions,
            'groupPermissions' => $groupPermissions,
            'total' => $total,
            'expired' => $expired,
            'expiring7' => $expiring7,
            'expiring30' => $expiring30,
            'expiring90' => $expiring90,
            'today' => $today,
            'in7' => $in7,
            'in30' => $in30,
            'in90' => $in90,
        ]);
    }
}
