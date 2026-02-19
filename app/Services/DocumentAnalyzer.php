<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class DocumentAnalyzer
{
    /**
     * Analyze a file stored on the local disk.
     *
     * @param  string  $diskRelativePath  e.g. "document_images/foo.pdf"
     * @return array<string, mixed>
     */
    public function analyzeLocalStoredFile(string $diskRelativePath): array
    {
        $analysis = [
            'mime_type' => null,
            'file_size' => null,
            'sha256' => null,
            'pdf_page_count' => null,
            'image_width' => null,
            'image_height' => null,
            'exif' => null,
        ];

        if (!Storage::disk('local')->exists($diskRelativePath)) {
            return $analysis;
        }

        $absolutePath = Storage::disk('local')->path($diskRelativePath);

        $analysis['file_size'] = @filesize($absolutePath) ?: null;
        $analysis['sha256'] = @hash_file('sha256', $absolutePath) ?: null;

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $analysis['mime_type'] = @$finfo->file($absolutePath) ?: null;

        $mime = (string) ($analysis['mime_type'] ?? '');

        if (str_starts_with($mime, 'image/')) {
            $size = @getimagesize($absolutePath);
            if (is_array($size) && isset($size[0], $size[1])) {
                $analysis['image_width'] = (int) $size[0];
                $analysis['image_height'] = (int) $size[1];
            }

            if (function_exists('exif_read_data')) {
                try {
                    $exif = @exif_read_data($absolutePath, null, true, false);
                    if (is_array($exif)) {
                        $analysis['exif'] = $this->sanitizeExif($exif);
                    }
                } catch (\Throwable) {
                    // ignore EXIF failures
                }
            }

            return $analysis;
        }

        if ($mime === 'application/pdf') {
            $analysis['pdf_page_count'] = $this->estimatePdfPageCount($absolutePath);
        }

        return $analysis;
    }

    private function estimatePdfPageCount(string $absolutePath): ?int
    {
        $contents = @file_get_contents($absolutePath);
        if ($contents === false || $contents === '') {
            return null;
        }

        $count = preg_match_all('/\\/Type\\s*\\/Page\b/', $contents) ?: 0;

        if ($count <= 0) {
            return null;
        }

        return $count;
    }

    /**
     * EXIF can contain resources / invalid UTF-8; keep a small safe subset.
     *
     * @param  array<mixed>  $exif
     * @return array<string, mixed>
     */
    private function sanitizeExif(array $exif): array
    {
        $allowedKeys = [
            'FILE',
            'COMPUTED',
            'IFD0',
            'EXIF',
            'GPS',
        ];

        $sanitized = [];
        foreach ($allowedKeys as $key) {
            if (!isset($exif[$key]) || !is_array($exif[$key])) {
                continue;
            }

            $sanitized[$key] = $this->stringifyValues($exif[$key]);
        }

        return $sanitized;
    }

    /**
     * @param  array<mixed>  $values
     * @return array<string, mixed>
     */
    private function stringifyValues(array $values): array
    {
        $out = [];
        foreach ($values as $k => $v) {
            $key = is_string($k) ? $k : (string) $k;

            if (is_scalar($v) || $v === null) {
                if (is_string($v) && function_exists('mb_convert_encoding')) {
                    $out[$key] = @mb_convert_encoding($v, 'UTF-8', 'UTF-8');
                } else {
                    $out[$key] = $v;
                }
                continue;
            }

            if (is_array($v)) {
                $out[$key] = $this->stringifyValues($v);
                continue;
            }

            $out[$key] = (string) $v;
        }

        return $out;
    }
}
