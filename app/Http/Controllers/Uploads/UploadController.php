<?php

namespace App\Http\Controllers\Uploads;

use App\Http\Controllers\Controller;
use App\Models\Document_upload;
use App\Services\DocumentAnalyzer;
use App\Services\OcrService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    public function __construct(
        private readonly DocumentAnalyzer $documentAnalyzer,
        private readonly OcrService $ocrService,
    ) {
    }

    public function display(Request $request)
    {
        $uploads = Document_upload::query()
            ->where('user_id', Auth::id())
            ->orderByDesc('id')
            ->paginate(10);

        $ocrAvailability = $this->ocrService->availability();

        return view('uploads/index', [
            'uploads' => $uploads,
            'ocrAvailability' => $ocrAvailability,
        ]);
    }

    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|array',
            'file.*' => 'file|max:20480|mimes:pdf,jpg,jpeg,png',
        ]);

        $userId = (int) Auth::id();

        $files = $request->file('file', []);
        $uploaded = [];

        foreach ((array) $files as $file) {
            if (!$file instanceof \Illuminate\Http\UploadedFile) {
                continue;
            }

            $original = $file->getClientOriginalName();
            $base = pathinfo($original, PATHINFO_FILENAME);
            $ext = strtolower((string) $file->getClientOriginalExtension());
            $safeBase = preg_replace('/[^A-Za-z0-9_\-]+/', '_', (string) $base);
            $safeBase = trim((string) $safeBase, '_');
            if ($safeBase === '') {
                $safeBase = 'upload';
            }

            $unique = $safeBase . '_' . now()->format('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $storedPath = $file->storeAs('temp', $unique);
            $diskRelativePath = 'temp/' . basename((string) $storedPath);

            $analysis = $this->documentAnalyzer->analyzeLocalStoredFile($diskRelativePath);
            $language = (string) env('OCR_LANGUAGE', 'eng');
            $ocr = $this->ocrService->extractTextFromLocalStoredFile($diskRelativePath, $language);

            $row = Document_upload::query()->create([
                'user_id' => $userId,
                'file_name' => basename($diskRelativePath),
                'mime_type' => $analysis['mime_type'] ?? null,
                'file_size' => $analysis['file_size'] ?? null,
                'sha256' => $analysis['sha256'] ?? null,
                'pdf_page_count' => $analysis['pdf_page_count'] ?? null,
                'image_width' => $analysis['image_width'] ?? null,
                'image_height' => $analysis['image_height'] ?? null,
                'exif' => $analysis['exif'] ?? null,
                'analyzed_at' => now(),
                'ocr_text' => $ocr['text'] ?? null,
                'ocr_error' => $ocr['error'] ?? null,
                'ocr_engine' => $ocr['engine'] ?? null,
                'ocr_language' => $ocr['language'] ?? $language,
                'ocr_completed_at' => now(),
                'created_at' => now(),
            ]);

            $uploaded[] = ['id' => $row->id, 'file_name' => $row->file_name];
        }

        return response()->json(['uploads' => $uploaded]);
    }

    public function download(int $id)
    {
        $upload = Document_upload::query()
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $path = 'temp/' . $upload->file_name;
        if (!Storage::disk('local')->exists($path)) {
            return redirect()->back()->with('error', 'File not found.');
        }

        return response()->download(storage_path('app/' . $path), $upload->file_name);
    }

    public function delete(Request $request)
    {
        $data = $request->validate([
            'id' => 'required|integer',
        ]);

        $upload = Document_upload::query()
            ->where('id', (int) $data['id'])
            ->where('user_id', Auth::id())
            ->first();

        if (!$upload) {
            return response()->json(['success' => false, 'message' => 'Upload not found'], 404);
        }

        $path = 'temp/' . $upload->file_name;
        if (Storage::disk('local')->exists($path)) {
            Storage::disk('local')->delete($path);
        }

        $upload->delete();

        return response()->json(['success' => true]);
    }
}
