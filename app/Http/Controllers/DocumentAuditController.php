<?php

namespace App\Http\Controllers;

use App\Models\Document_audit;
use Illuminate\Http\Request;

class DocumentAuditController extends Controller
{
    public function display(Request $request)
    {
        $sort_by = $request->input('sort_by', 'id');
        $sort_order = $request->input('sort_order', 'asc');
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

        return view('main/audit-document', ['audits' => $audits, 'sort_by' => $sort_by, 'sort_order' => $sort_order, 'search' => $search]);
    }
}
