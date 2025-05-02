<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\TestEmail;

class TestEmailController extends Controller
{
    public function sendTestEmail()
    {
        $details = [
            'title' => 'Test Email from Laravel',
            'body' => 'This is a test email to check the mail configuration in Laravel.'
        ];

        Mail::to('recipient@example.com')->send(new TestEmail($details));

        return 'Test email sent!';
    }
}
