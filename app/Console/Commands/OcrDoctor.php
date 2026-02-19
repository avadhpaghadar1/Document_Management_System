<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class OcrDoctor extends Command
{
    protected $signature = 'dms:ocr-doctor';

    protected $description = 'Check OCR tools availability (tesseract, pdftotext, pdftoppm)';

    public function handle(): int
    {
        $this->checkTool('tesseract', (string) env('TESSERACT_CMD', 'tesseract'), ['--version']);
        $this->checkTool('pdftotext', (string) env('PDFTOTEXT_CMD', 'pdftotext'), ['-v']);
        $this->checkTool('pdftoppm', (string) env('PDFTOPPM_CMD', 'pdftoppm'), ['-v']);

        $this->newLine();
        $this->line('Env overrides: TESSERACT_CMD, PDFTOTEXT_CMD, PDFTOPPM_CMD, OCR_LANGUAGE, OCR_TIMEOUT_SECONDS, OCR_PDF_MAX_PAGES');

        return self::SUCCESS;
    }

    /**
     * @param  list<string>  $versionArgs
     */
    private function checkTool(string $label, string $command, array $versionArgs): void
    {
        $this->line($label . ': ' . $command);

        $process = new Process(array_merge([$command], $versionArgs));
        $process->setTimeout(5);
        $process->run();

        if (!$process->isSuccessful()) {
            $this->error('  NOT OK');
            $err = trim($process->getErrorOutput());
            if ($err !== '') {
                $this->line('  ' . $err);
            }
            return;
        }

        $this->info('  OK');
        $out = trim($process->getOutput());
        if ($out !== '') {
            $firstLine = explode("\n", $out)[0] ?? '';
            $this->line('  ' . $firstLine);
        }
    }
}
