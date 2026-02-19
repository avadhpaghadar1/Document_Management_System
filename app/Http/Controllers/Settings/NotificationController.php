<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function display()
    {
        $userId = Auth::user()->id;
        $details = Notification::where('user_id', $userId)->get();
        return view('settings/index', ['details' => $details]);
    }
    public function store(Request $request)
    {
        $request->validate([
            'inputs.*.day' => 'required',
            'inputs.*.name' => 'required',
        ]);
        $details = $request->input('inputs');
        $userId = Auth::user()->id;
        Notification::where('user_id', $userId)->delete();

        foreach ($details as $detail) {
            Notification::create([
                'user_id' => $userId,
                'day' => $detail['day'],
                'name' => $detail['name'],
            ]);
        }
        return redirect()->to('setting')->with('success', 'Notifications set successfully');
    }
}
