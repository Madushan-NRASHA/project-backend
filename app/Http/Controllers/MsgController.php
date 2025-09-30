<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Msg;

class MsgController extends Controller
{
    /**
     * Store a new message without validation
     */
    public function store(Request $request)
    {
        $msg = Msg::create([
            'job_id' => $request->job_id,
            'sender_id' => $request->current_user_id,
            'receiver_id' => $request->job_poster_id,
            'message' => $request->message,
            'extra_data' => [
                'user_theme' => $request->user_theme,
                'job_data' => $request->job_data,
                'job_poster_name' => $request->job_poster_name,
            ],
        ]);

        return response()->json([
            'success' => true,
            'data' => $msg
        ], 201);
    }

    /**
     * Get all messages related to the logged-in user
     */
    public function getUserMessages($userId)
    {
        // Sender or Receiver is the logged-in user
        $messages = Msg::where('sender_id', $userId)
            ->orWhere('receiver_id', $userId)
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $messages
        ]);
    }
}
