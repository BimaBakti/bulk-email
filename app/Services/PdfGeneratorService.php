<?php

namespace App\Services;

use App\Models\LetterTemplate;
use App\Models\Recipient;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class PdfGeneratorService
{
    public function __construct(protected MergeTagParser $mergeTagParser) {}

    /**
     * Generate PDF from letter template + recipient data.
     * Returns path to temporary file.
     */
    public function generateForRecipient(
        LetterTemplate $template,
        Recipient $recipient,
        string $filename = 'Surat'
    ): string {
        // 1. Parse merge tags in template body
        $html = $this->mergeTagParser->parse($template->body, $recipient);

        // 2. Wrap HTML in a proper layout
        $fullHtml = $this->wrapInLayout($html, $template);

        // 3. Generate PDF via DomPDF
        $pdf = Pdf::loadHTML($fullHtml)
            ->setPaper($template->page_size ?? 'a4', $template->orientation ?? 'portrait');

        // 4. Save to temp file
        $tempDir = 'temp/letters';
        // Ensure directory exists
        if (!Storage::exists($tempDir)) {
            Storage::makeDirectory($tempDir);
        }
        
        $tempPath = $tempDir . '/' . uniqid() . '_' . $filename . '.pdf';
        Storage::put($tempPath, $pdf->output());

        return $tempPath;
    }

    /**
     * Wrap body HTML with CSS styling for formal letter.
     */
    protected function wrapInLayout(string $body, LetterTemplate $template): string
    {
        return view('pdf.letter-layout', compact('body'))->render();
    }
}
