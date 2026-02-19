<?php

namespace App\Http\Controllers\Documents;

use App\Http\Controllers\Controller;
use App\Models\Document_audit;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class DocumentAuditController extends Controller
{
    use AuthorizesRequests;

    public function display(Request $request)
    {
        try {
            $this->authorize('view_document_audit');
        } catch (AuthorizationException) {
            return redirect()->route('dashboard')->with('error', 'You do not have permission to perform this action.');
        }

        $sort_by = $request->input('sort_by', 'id');
        $allowedSortBy = ['id', 'document_type_id', 'user_id', 'action'];
        if (!in_array($sort_by, $allowedSortBy, true)) {
            $sort_by = 'id';
        }

        $sort_order = strtolower((string) $request->input('sort_order', 'asc'));
        if (!in_array($sort_order, ['asc', 'desc'], true)) {
            $sort_order = 'asc';
        }
        $search = $request->input('search');

        $query = Document_audit::with(['documentType', 'user']);
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%');
                })
                    ->orWhereHas('documentType', function ($q) use ($search) {
                        $q->where('name', 'like', '%' . $search . '%');
                    })
                    ->orWhere('id', 'like', '%' . $search . '%');
            });
        }
        $audits = $query->orderBy($sort_by, $sort_order)->paginate(5);

        return view('documents/audit/index', ['audits' => $audits, 'sort_by' => $sort_by, 'sort_order' => $sort_order, 'search' => $search]);
    }
}
