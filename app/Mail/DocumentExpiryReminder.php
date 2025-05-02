<?php

namespace App\Mail;

use App\Models\Document_main;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;


class DocumentExpiryReminder extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $document;

    public function __construct($documentId)
    {
        $this->document = Document_main::with(['documentDetail', 'documentType', 'documentImages'])->find($documentId);
    }

    public function build()
    {
        $detailsArray = [];
        $documentDetails = optional($this->document)->documentDetail;

        if ($documentDetails) {
            foreach ($documentDetails as $detail) {
                $detailsArray[$detail->field_name] = $detail->field_value;
            }
        }

        $documentTypeName = optional($this->document->documentType)->name ?? "Undefined";

        return $this->markdown('mail.expiry-reminder')
            ->with([
                'expiryDate' => $this->document->expiry,
                'documentType' => $documentTypeName,
                'documentDetails' => $detailsArray,
            ])
            ->withAttachments($this->attachments());
    }

    public function envelope(): Envelope
    {
        $documentTypeName = $this->document->documentType ? $this->document->documentType->name : "Document";
        $expiryDate = \Carbon\Carbon::parse($this->document->expiry);
        $isExpired = $expiryDate->isPast();
        $subject = $isExpired
            ? "{$documentTypeName} is Expired"
            : "{$documentTypeName} will expire on {$expiryDate->format('Y-m-d')}";

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.expiry-reminder',
        );
    }

    public function attachments(): array
    {
        $attachments = [];

        if ($this->document->documentImages) {
            foreach ($this->document->documentImages as $image) {
                $filePath = "document_images/{$image->name}";
                $attachments[] = Attachment::fromStorage($filePath);
            }
        }
        return $attachments;
    }
}
