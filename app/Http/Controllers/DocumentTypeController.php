<?php

namespace App\Http\Controllers;

use App\Models\Document_type;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DocumentTypeController extends Controller
{
    use AuthorizesRequests;
    public function view(Request $request)
    {
        try {
            $this->authorize('view_document_type');

            $sort_by = $request->input('sort_by', 'id');
            $sort_order = $request->input('sort_order', 'asc');
            $search = $request->input('search');
            $query = Document_type::query();
            if ($search) {
                $query->where('name', 'like', '%' . $search . '%');
            }
            $documentTypes = $query->orderBy($sort_by, $sort_order)->paginate(5);
            return view('main/document-type', ['documentTypes' => $documentTypes, 'sort_by' => $sort_by, 'sort_order' => $sort_order, 'search' => $search]);
        } catch (AuthorizationException $e) {
            return redirect()->route('dashboard')->with('error', 'You do not have permission to perform this action.');
        }
    }
    public function create()
    {
        try {
            $this->authorize('create_document_type');
            return view('main/add-document-type');
        } catch (AuthorizationException $e) {
            return redirect()->route('dashboard')->with('error', 'You do not have permission to perform this action.');
        }
    }
    public function getFields($id)
    {
        $documentType = Document_type::with('documentFields')->find($id);
        if ($documentType) {
            return view('components/document-field', ['documentType' => $documentType]);
        } else {
            return response()->json(['message' => 'Document Type not found'], 404);
        }
    }
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|unique:document_types,name',
            'fields' => 'array',
            'fields.*.name' => 'required|string',
            'fields.*.type' => 'required|string|in:TEXT,DATE,DATETIME',
        ]);
        if ($validatedData['fields']) {
            $fields = $validatedData['fields'];
        }
        $loggedInUserId = Auth::id();
        try {
            $documentType = Document_type::create([
                'name' => $validatedData['name'],
                'user_id' => $loggedInUserId
            ]);

            if ($documentType) {
                foreach ($fields as $field) {
                    $documentType->documentFields()->create([
                        'field_name' => $field['name'],
                        'field_type' => $field['type']
                    ]);
                }

                return redirect()->to('document-type')->with('success', 'Document Type Created Successfully.');
            } else {
                return redirect()->back()->with('error', 'Document Type creation failed.');
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }
    public function edit(Request $request, $id)
    {
        try {
            $this->authorize('edit_document_type');
            $documentType = Document_type::with('documentFields')->where('id', $id)->first();
            return view('main/update-document-type', ['documentType' => $documentType]);
        } catch (AuthorizationException $e) {
            return redirect()->route('dashboard')->with('error', 'You do not have permission to perform this action.');
        }
    }
    public function update(Request $request, $id)
    {

        $validatedData = $request->validate([
            'name' => [
                'required',
                'string',
                Rule::unique('document_types')->ignore($id),
            ],
            'fields' => 'array',
            'fields.*.name' => 'required|string',
            'fields.*.type' => 'required|string|in:TEXT,DATE,DATETIME',
        ]);
        try {
            $documentType = Document_type::findOrFail($id);


            $documentType->name = $validatedData['name'];
            $documentType->save();

            $documentType->documentFields()->delete();

            foreach ($validatedData['fields'] as $field) {
                $documentType->documentFields()->create([
                    'field_name' => $field['name'],
                    'field_type' => $field['type'],
                ]);
            }
            return redirect()->to('document-type')->with('success', 'Document Type updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update Document Type: ' . $e->getMessage());
        }
    }
    public function delete(Request $request)
    {
        try {
            $this->authorize('delete_document_type');
            $documentTypeid = $request->input('delete');
            $documentType = Document_type::find($documentTypeid);
            if ($documentType->delete()) {
                return redirect()->to('document-type')->with('success', 'Group Deleted Successfully.');
            } else {
                return redirect()->to('document-type')->with('error', 'Group Deleted Failed.');
            }
        } catch (AuthorizationException $e) {
            return redirect()->route('dashboard')->with('error', 'You do not have permission to perform this action.');
        }
    }
}
