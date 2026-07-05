<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SupabaseService;
use Carbon\Carbon;

class BotController extends Controller
{
    protected $supabase;

    public function __construct(SupabaseService $supabase)
    {
        $this->supabase = $supabase;
    }

    public function dashboard()
    {
        return view('bot');
    }

    public function poll(Request $request)
    {
        $username = $request->input('username');
        
        if (!$username) {
            return response()->json(['error' => 'Username required'], 400);
        }

        $cleanUsername = strtolower(trim($username));
        $currentTime = Carbon::now('UTC')->toIso8601String();

        // Check if bot exists
        $existingBot = $this->supabase->get('/rest/v1/bots', [
            'username' => 'eq.' . $cleanUsername,
            'select' => '*',
        ]);

        if (!empty($existingBot)) {
            // Update last seen
            $this->supabase->patch('/rest/v1/bots', [
                'username' => 'eq.' . $cleanUsername,
                'last_seen' => $currentTime,
            ]);
        } else {
            // Create new bot
            $this->supabase->post('/rest/v1/bots', [
                'username' => $cleanUsername,
                'last_seen' => $currentTime,
                'command' => 'none',
            ]);
        }

        // Get current command
        $command = 'none';
        $botData = $this->supabase->get('/rest/v1/bots', [
            'username' => 'eq.' . $cleanUsername,
            'select' => 'command',
        ]);

        if (!empty($botData)) {
            $command = $botData[0]['command'] ?? 'none';
        }

        // Clear respawn command if set
        if ($command === 'respawn') {
            $this->supabase->patch('/rest/v1/bots', [
                'username' => 'eq.' . $cleanUsername,
                'command' => 'none',
            ]);
        }

        return response()->json(['command' => $command]);
    }

    public function getUsers()
    {
        $bots = $this->supabase->get('/rest/v1/bots', [
            'select' => '*',
            'order' => 'last_seen.desc',
        ]);

        return response()->json(['bots' => $bots]);
    }

    public function triggerRespawn(Request $request)
    {
        $username = $request->input('username');
        
        if (!$username) {
            return response()->json(['error' => 'Username required'], 400);
        }

        $cleanUsername = strtolower(trim($username));
        $result = $this->supabase->patch('/rest/v1/bots', [
            'username' => 'eq.' . $cleanUsername,
            'command' => 'respawn',
        ]);

        if ($result !== null) {
            return response()->json(['success' => true]);
        }

        return response()->json(['error' => 'Failed to update database'], 500);
    }
}