<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

class OcrService
{
    /**
     * @param  string  $tesseractCommand  Command or absolute path
     * @param  string  $pdfToTextCommand  Command or absolute path
     * @param  string  $pdfToPpmCommand   Command or absolute path
     */
    public function __construct(
        private readonly string $tesseractCommand = '',
        private readonly string $pdfToTextCommand = '',
        private readonly string $pdfToPpmCommand = '',
    ) {
    }

    /**
     * @param  string  $diskRelativePath  e.g. "document_images/foo.png"
     * @param  string  $language          e.g. "eng"
     * @return array{ text: string|null, engine: string|null, language: string, error: string|null }
     */
    public function extractTextFromLocalStoredFile(string $diskRelativePath, string $language = 'eng'): array
    {
        if (!Storage::disk('local')->exists($diskRelativePath)) {
            return ['text' => null, 'engine' => null, 'language' => $language, 'error' => 'File not found'];
        }

        $absolutePath = Storage::disk('local')->path($diskRelativePath);
        $mimeType = $this->detectMimeType($absolutePath);

        if ($mimeType === 'application/pdf') {
            return $this->extractFromPdf($absolutePath, $language);
        }

        if (is_string($mimeType) && str_starts_with($mimeType, 'image/')) {
            return $this->extractFromImage($absolutePath, $language);
        }

        return ['text' => null, 'engine' => null, 'language' => $language, 'error' => 'Unsupported file type'];
    }

    /**
     * @return array{ tesseract: bool, pdftotext: bool, pdftoppm: bool, language: string, tesseract_cmd: string, pdftotext_cmd: string, pdftoppm_cmd: string }
     */
    public function availability(): array
    {
        $tesseract = $this->tesseractCommand !== '' ? $this->tesseractCommand : (string) env('TESSERACT_CMD', 'tesseract');
        $pdftotext = $this->pdfToTextCommand !== '' ? $this->pdfToTextCommand : (string) env('PDFTOTEXT_CMD', 'pdftotext');
        $pdftoppm = $this->pdfToPpmCommand !== '' ? $this->pdfToPpmCommand : (string) env('PDFTOPPM_CMD', 'pdftoppm');
        $language = (string) env('OCR_LANGUAGE', 'eng');

        return [
            'tesseract' => $this->commandExists($tesseract),
            'pdftotext' => $this->commandExists($pdftotext),
            'pdftoppm' => $this->commandExists($pdftoppm),
            'language' => $language,
            'tesseract_cmd' => $tesseract,
            'pdftotext_cmd' => $pdftotext,
            'pdftoppm_cmd' => $pdftoppm,
        ];
    }

    private function extractFromImage(string $absolutePath, string $language): array
    {
        $tesseract = $this->tesseractCommand !== '' ? $this->tesseractCommand : (string) env('TESSERACT_CMD', 'tesseract');

        if (!$this->commandExists($tesseract)) {
            return ['text' => null, 'engine' => null, 'language' => $language, 'error' => 'Tesseract not found'];
        }

        // tesseract <input> stdout -l eng
        $process = new Process([$tesseract, $absolutePath, 'stdout', '-l', $language]);
        $process->setTimeout((float) env('OCR_TIMEOUT_SECONDS', 25));
        $process->run();

        if (!$process->isSuccessful()) {
            return ['text' => null, 'engine' => 'tesseract', 'language' => $language, 'error' => trim($process->getErrorOutput()) ?: 'OCR failed'];
        }

        $text = trim($process->getOutput());
        return ['text' => $text !== '' ? $text : null, 'engine' => 'tesseract', 'language' => $language, 'error' => null];
    }

    private function extractFromPdf(string $absolutePath, string $language): array
    {
        // 1) Try text extraction first (fast) via pdftotext if available
        $pdftotext = $this->pdfToTextCommand !== '' ? $this->pdfToTextCommand : (string) env('PDFTOTEXT_CMD', 'pdftotext');
        if ($this->commandExists($pdftotext)) {
            $process = new Process([$pdftotext, '-layout', $absolutePath, '-']);
            $process->setTimeout((float) env('OCR_TIMEOUT_SECONDS', 25));
            $process->run();

            if ($process->isSuccessful()) {
                $text = trim($process->getOutput());
                $len = function_exists('mb_strlen') ? mb_strlen($text) : strlen($text);
                if ($len >= 50) {
                    return ['text' => $text, 'engine' => 'pdftotext', 'language' => $language, 'error' => null];
                }
            }
        }

        // 2) Fallback to OCR via pdftoppm + tesseract, if available
        $tesseract = $this->tesseractCommand !== '' ? $this->tesseractCommand : (string) env('TESSERACT_CMD', 'tesseract');
        $pdftoppm = $this->pdfToPpmCommand !== '' ? $this->pdfToPpmCommand : (string) env('PDFTOPPM_CMD', 'pdftoppm');

        if (!$this->commandExists($tesseract)) {
            return ['text' => null, 'engine' => null, 'language' => $language, 'error' => 'Tesseract not found'];
        }
        if (!$this->commandExists($pdftoppm)) {
            return ['text' => null, 'engine' => null, 'language' => $language, 'error' => 'pdftotext did not extract text and pdftoppm is not available'];
        }

        $tmpDir = storage_path('app/ocr_tmp/' . bin2hex(random_bytes(8)));
        if (!@mkdir($tmpDir, 0777, true) && !is_dir($tmpDir)) {
            return ['text' => null, 'engine' => null, 'language' => $language, 'error' => 'Failed to create temp dir'];
        }

        $base = $tmpDir . DIRECTORY_SEPARATOR . 'page';
        $maxPages = (int) env('OCR_PDF_MAX_PAGES', 10);

        // pdftoppm -f 1 -l <max> -png input base
        $process = new Process([$pdftoppm, '-f', '1', '-l', (string) $maxPages, '-png', $absolutePath, $base]);
        $process->setTimeout((float) env('OCR_TIMEOUT_SECONDS', 25));
        $process->run();

        if (!$process->isSuccessful()) {
            $this->deleteDir($tmpDir);
            return ['text' => null, 'engine' => 'pdftoppm+tesseract', 'language' => $language, 'error' => trim($process->getErrorOutput()) ?: 'PDF render failed'];
        }

        $allText = [];
        $images = glob($tmpDir . DIRECTORY_SEPARATOR . 'page-*.png') ?: [];
        sort($images);

        foreach ($images as $imagePath) {
            $result = $this->extractFromImage($imagePath, $language);
            if (!empty($result['text'])) {
                $allText[] = $result['text'];
            }
        }

        $this->deleteDir($tmpDir);

        $text = trim(implode("\n\n", $allText));
        return ['text' => $text !== '' ? $text : null, 'engine' => 'pdftoppm+tesseract', 'language' => $language, 'error' => null];
    }

    private function detectMimeType(string $absolutePath): ?string
    {
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        return @$finfo->file($absolutePath) ?: null;
    }

    private function commandExists(string $commandOrPath): bool
    {
        $commandOrPath = trim($commandOrPath);
        if ($commandOrPath === '') {
            return false;
        }

        // If it's an absolute/relative path, check file exists
        if (str_contains($commandOrPath, '\\') || str_contains($commandOrPath, '/')) {
            return is_file($commandOrPath) || is_file($commandOrPath . '.exe');
        }

        $where = strtoupper(substr(PHP_OS_FAMILY, 0, 3)) === 'WIN' ? 'where' : 'command';
        $args = $where === 'where' ? [$where, $commandOrPath] : [$where, '-v', $commandOrPath];
        $process = new Process($args);
        $process->setTimeout(3);
        $process->run();
        return $process->isSuccessful();
    }

    private function deleteDir(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $items = scandir($dir);
        if (!is_array($items)) {
            return;
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $path = $dir . DIRECTORY_SEPARATOR . $item;
            if (is_dir($path)) {
                $this->deleteDir($path);
            } else {
                @unlink($path);
            }
        }

        @rmdir($dir);
    }
}
