<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SupabaseService;

class InventoryController extends Controller
{
    protected $supabase;

    public function __construct(SupabaseService $supabase)
    {
        $this->supabase = $supabase;
    }

    public function dashboard()
    {
        $data = $this->supabase->get('/rest/v1/inventory_tracking', [
            'select' => '*',
        ]);

        // Calculate totals
        $totalShark = 0;
        $totalGladiator = 0;
        $totalStone = 0;

        if (!empty($data)) {
            foreach ($data as $row) {
                $totalShark += $row['elshark_gran_maja'] ?? 0;
                $totalGladiator += $row['gladiator_shark'] ?? 0;
                $totalStone += $row['evolved_enchant_stone'] ?? 0;
            }
        }

        return view('inventory', compact('data', 'totalShark', 'totalGladiator', 'totalStone'));
    }

    public function trackItem(Request $request)
    {
        $username = $request->input('username');
        $items = $request->input('items', []);

        if (!$username || empty($items)) {
            return response()->json(['error' => 'Data tidak lengkap'], 400);
        }

        // Check existing user
        $existingUser = $this->supabase->get('/rest/v1/inventory_tracking', [
            'username' => 'eq.' . $username,
            'select' => '*',
        ]);

        $currentData = [
            'elshark_gran_maja' => 0,
            'gladiator_shark' => 0,
            'evolved_enchant_stone' => 0,
        ];

        if (!empty($existingUser)) {
            $currentData = $existingUser[0];
        }

        $updateData = [];
        if (in_array('Elshark Gran Maja', $items)) {
            $updateData['elshark_gran_maja'] = ($currentData['elshark_gran_maja'] ?? 0) + 1;
        }
        if (in_array('Gladiator Shark', $items)) {
            $updateData['gladiator_shark'] = ($currentData['gladiator_shark'] ?? 0) + 1;
        }
        if (in_array('Evolved Enchant Stone', $items)) {
            $updateData['evolved_enchant_stone'] = ($currentData['evolved_enchant_stone'] ?? 0) + 1;
        }

        if (!empty($existingUser)) {
            $this->supabase->patch('/rest/v1/inventory_tracking', [
                'username' => 'eq.' . $username,
                ...$updateData,
            ]);
        } else {
            $updateData['username'] = $username;
            $this->supabase->post('/rest/v1/inventory_tracking', $updateData);
        }

        return response()->json(['status' => 'success'], 200);
    }

    public function updateItem(Request $request)
    {
        $username = $request->input('username');
        
        if (!$username) {
            return response()->json(['error' => 'Username diperlukan'], 400);
        }

        $updateData = [
            'elshark_gran_maja' => $request->input('elshark_gran_maja', 0),
            'gladiator_shark' => $request->input('gladiator_shark', 0),
            'evolved_enchant_stone' => $request->input('evolved_enchant_stone', 0),
        ];

        $result = $this->supabase->patch('/rest/v1/inventory_tracking', [
            'username' => 'eq.' . $username,
            ...$updateData,
        ]);

        if ($result !== null) {
            return response()->json(['status' => 'success'], 200);
        }

        return response()->json(['status' => 'error', 'message' => 'Gagal update data'], 500);
    }

    public function resetCounts()
    {
        $this->supabase->patch('/rest/v1/inventory_tracking', [
            'username' => 'not.is.null',
            'elshark_gran_maja' => 0,
            'gladiator_shark' => 0,
            'evolved_enchant_stone' => 0,
        ]);

        return response()->json(['message' => 'Semua data berhasil direset ke 0'], 200);
    }
}