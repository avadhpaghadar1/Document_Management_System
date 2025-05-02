<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Mail\DocumentExpiryReminder;
use App\Models\Document_notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendDocumentExpiryReminders extends Command
{
    protected $signature = 'command:sendDocumentExpiryReminders';
    protected $description = 'Send document expiry reminders';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $today = now()->format('Y-m-d');

        $notifications = Document_notification::with(['document.users', 'document.documentType', 'document.documentDetail'])
            ->whereHas('document', function ($query) use ($today) {
                $query->where(function ($q) use ($today) {
                    $q->where(function ($subQuery) use ($today) {
                        $subQuery->whereRaw('DATE_SUB(expiry, INTERVAL day DAY) = ?', [$today])
                            ->where('name', 'dayBefore');
                    })
                        ->orWhere(function ($subQuery) use ($today) {
                            $subQuery->whereRaw('DATE_ADD(expiry, INTERVAL day DAY) = ?', [$today])
                                ->where('name', 'dayAfter');
                        });
                });
            })->get();

        foreach ($notifications as $notification) {
            $document = $notification->document;
            if ($document) {
                foreach ($document->users as $user) { 
                    Mail::to($user->email)->send((new DocumentExpiryReminder($document->id)));
                }
            }
        }
    }
}
