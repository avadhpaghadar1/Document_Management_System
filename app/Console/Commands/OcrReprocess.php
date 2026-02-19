<?php

namespace App\Console\Commands;

use App\Models\Document_file_analysis;
use App\Models\Document_upload;
use App\Services\OcrService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class OcrReprocess extends Command
{
    protected $signature = 'dms:ocr-reprocess {--uploads : Reprocess temp uploads} {--documents : Reprocess document attachments} {--limit=200 : Max rows to process}';

    protected $description = 'Re-run OCR for existing uploads/attachments (use after configuring OCR tools)';

    public function __construct(private readonly OcrService $ocrService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $doUploads = (bool) $this->option('uploads');
        $doDocuments = (bool) $this->option('documents');

        if (!$doUploads && !$doDocuments) {
            $doUploads = true;
            $doDocuments = true;
        }

        $limit = (int) $this->option('limit');
        if ($limit <= 0) {
            $limit = 200;
        }

        $language = (string) env('OCR_LANGUAGE', 'eng');

        if ($doUploads) {
            $this->info('Reprocessing uploads...');
            $rows = Document_upload::query()
                ->where(function ($q) {
                    $q->whereNull('ocr_completed_at')
                        ->orWhereNotNull('ocr_error');
                })
                ->orderBy('id')
                ->limit($limit)
                ->get();

            foreach ($rows as $row) {
                $path = 'temp/' . $row->file_name;
                if (!Storage::disk('local')->exists($path)) {
                    $row->update([
                        'ocr_text' => null,
                        'ocr_error' => 'File not found',
                        'ocr_engine' => null,
                        'ocr_language' => $language,
                        'ocr_completed_at' => now(),
                    ]);
                    continue;
                }

                $ocr = $this->ocrService->extractTextFromLocalStoredFile($path, $language);
                $row->update([
                    'ocr_text' => $ocr['text'],
                    'ocr_error' => $ocr['error'],
                    'ocr_engine' => $ocr['engine'],
                    'ocr_language' => $ocr['language'],
                    'ocr_completed_at' => now(),
                ]);
            }
        }

        if ($doDocuments) {
            $this->info('Reprocessing document attachments...');
            $rows = Document_file_analysis::query()
                ->where(function ($q) {
                    $q->whereNull('ocr_completed_at')
                        ->orWhereNotNull('ocr_error');
                })
                ->orderBy('id')
                ->limit($limit)
                ->get();

            foreach ($rows as $row) {
                $path = 'document_images/' . $row->file_name;
                if (!Storage::disk('local')->exists($path)) {
                    $row->update([
                        'ocr_text' => null,
                        'ocr_error' => 'File not found',
                        'ocr_engine' => null,
                        'ocr_language' => $language,
                        'ocr_completed_at' => now(),
                    ]);
                    continue;
                }

                $ocr = $this->ocrService->extractTextFromLocalStoredFile($path, $language);
                $row->update([
                    'ocr_text' => $ocr['text'],
                    'ocr_error' => $ocr['error'],
                    'ocr_engine' => $ocr['engine'],
                    'ocr_language' => $ocr['language'],
                    'ocr_completed_at' => now(),
                ]);
            }
        }

        $this->info('Done.');
        return self::SUCCESS;
    }
}
