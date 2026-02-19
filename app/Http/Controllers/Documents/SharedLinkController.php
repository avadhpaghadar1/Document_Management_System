<?php

namespace App\Http\Controllers\Documents;

use App\Http\Controllers\Controller;
use App\Models\Document_image;
use App\Models\Document_main;
use App\Models\Shared_link;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SharedLinkController extends Controller
{
    use AuthorizesRequests;

    public function create(Request $request)
    {
        try {
            $this->authorize('share_document');
        } catch (AuthorizationException) {
            return redirect()->back()->with('error', 'You do not have permission to share documents.');
        }

        $validated = $request->validate([
            'document_id' => 'required|integer',
            'file_name' => 'required|string',
            'expires_in_days' => 'nullable|integer|min:1|max:3650',
        ]);

        $documentId = (int) $validated['document_id'];
        $fileName = basename((string) $validated['file_name']);

        // Ensure user can view the document (sharing something you can't view should not be allowed).
        $userId = Auth::id();
        $groupIds = Auth::user()->groups()->pluck('id');
        $canView = Document_main::where('id', $documentId)
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

        if (!$canView) {
            return redirect()->back()->with('error', 'You do not have permission to share this document.');
        }

        $hasFile = Document_image::query()
            ->where('document_id', $documentId)
            ->where('name', $fileName)
            ->exists();

        if (!$hasFile) {
            return redirect()->back()->with('error', 'Attachment not found on this document.');
        }

        $storedPath = 'document_images/' . $fileName;
        if (!Storage::disk('local')->exists($storedPath)) {
            return redirect()->back()->with('error', 'File is missing from storage.');
        }

        $token = bin2hex(random_bytes(32));
        $expiresInDays = $validated['expires_in_days'] ?? 7;
        $expiresAt = $expiresInDays ? now()->addDays((int) $expiresInDays) : null;

        $link = Shared_link::create([
            'token' => $token,
            'document_id' => $documentId,
            'file_name' => $fileName,
            'created_by' => Auth::id(),
            'expires_at' => $expiresAt,
            'created_at' => now(),
        ]);

        $url = route('shared-links.download', ['token' => $link->token]);

        return redirect()->back()->with('success', 'Shared link created: ' . $url);
    }

    public function revoke(Request $request, $id)
    {
        try {
            $this->authorize('share_document');
        } catch (AuthorizationException) {
            return redirect()->back()->with('error', 'You do not have permission to revoke shared links.');
        }

        $link = Shared_link::query()->findOrFail($id);

        // Only allow revoking links for documents the user can view.
        $userId = Auth::id();
        $groupIds = Auth::user()->groups()->pluck('id');
        $canView = Document_main::where('id', $link->document_id)
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

        if (!$canView) {
            return redirect()->back()->with('error', 'You do not have permission to revoke this link.');
        }

        $link->delete();

        return redirect()->back()->with('success', 'Shared link revoked.');
    }

    public function download($token)
    {
        $token = (string) $token;
        if (strlen($token) !== 64) {
            abort(404);
        }

        $link = Shared_link::query()->where('token', $token)->first();
        if (!$link) {
            abort(404);
        }

        if ($link->expires_at && $link->expires_at->isPast()) {
            return response('Link expired.', 410);
        }

        $fileName = basename((string) $link->file_name);
        $storedPath = 'document_images/' . $fileName;

        if (!Storage::disk('local')->exists($storedPath)) {
            abort(404);
        }

        return response()->download(storage_path('app/' . $storedPath), $fileName);
    }
}
