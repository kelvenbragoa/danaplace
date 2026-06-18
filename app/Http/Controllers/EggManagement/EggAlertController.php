<?php

namespace App\Http\Controllers\EggManagement;

use App\Http\Controllers\Controller;
use App\Models\EggModule\EggAlert;
use Illuminate\Http\Request;

class EggAlertController extends Controller
{
    public function index(Request $request)
    {
        $searchQuery = $request->query('query');

        $query = EggAlert::with('flock')
            ->when($searchQuery, function ($q, $searchQuery) {
                $q->where(function ($sub) use ($searchQuery) {
                    $sub->where('title', 'like', "%{$searchQuery}%")
                        ->orWhere('message', 'like', "%{$searchQuery}%");
                });
            });

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->boolean('unread_only')) {
            $query->where('status', 'sent');
        }

        $alerts = $query->orderBy('alert_datetime', 'desc')->paginate(15);

        return response()->json($alerts);
    }

    public function show(EggAlert $eggAlert)
    {
        return response()->json($eggAlert->load('flock.house'));
    }

    public function unreadCount()
    {
        $count = EggAlert::where('status', 'sent')->count();

        return response()->json(['count' => $count]);
    }

    public function markAsRead(EggAlert $eggAlert)
    {
        $eggAlert->markAsRead();

        return response()->json($eggAlert->fresh('flock'));
    }

    public function markAsResolved(EggAlert $eggAlert)
    {
        $eggAlert->markAsResolved();

        return response()->json($eggAlert->fresh('flock'));
    }

    public function bulkMarkAsRead(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:egg_alerts,id',
        ]);

        EggAlert::whereIn('id', $request->ids)
            ->where('status', 'sent')
            ->update([
                'status' => 'read',
                'read_datetime' => now(),
            ]);

        return response()->json(['message' => 'Alertas marcados como lidos']);
    }

    public function triggerTestAlert()
    {
        $alert = EggAlert::create([
            'type' => 'inventory',
            'title' => 'Alerta de teste',
            'message' => 'Este é um alerta de teste gerado manualmente.',
            'alert_datetime' => now(),
            'status' => 'sent',
        ]);

        return response()->json($alert, 201);
    }
}
