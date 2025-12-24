<?php

namespace App\Http\Controllers\Associacao;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QrReaderController extends Controller
{
    public function index()
    {
        return view('associacao.qr_reader.index');
    }

    public function validateToken(Request $request)
    {
        $data = $request->validate([
            'token' => ['required', 'string'],
        ]);

        $associationId = Auth::user()->association_id;

        $ticket = Ticket::where('qr_token', $data['token'])
            ->with(['order', 'ticketType.event', 'owner'])
            ->first();

        if (! $ticket) {
            return response()->json([
                'success' => false,
                'code' => 'not_found',
                'message' => 'Ingresso não encontrado',
            ], 404);
        }

        if (! $ticket->order || $ticket->order->association_id !== $associationId) {
            return response()->json([
                'success' => false,
                'code' => 'forbidden',
                'message' => 'Ingresso não pertence à sua associação',
            ], 403);
        }

        if ($ticket->order->status !== 'paid') {
            return response()->json([
                'success' => false,
                'code' => 'not_paid',
                'message' => 'Ingresso não está pago',
            ], 409);
        }

        if ($ticket->status === 'used') {
            return response()->json([
                'success' => false,
                'code' => 'already_used',
                'message' => 'Ingresso já foi utilizado',
                'data' => [
                    'event' => $ticket->ticketType?->event?->title,
                    'type' => $ticket->ticketType?->name,
                    'owner' => $ticket->owner?->name,
                ],
            ], 409);
        }

        $ticket->status = 'used';
        $ticket->save();

        return response()->json([
            'success' => true,
            'message' => 'Ingresso validado com sucesso',
            'data' => [
                'event' => $ticket->ticketType?->event?->title,
                'type' => $ticket->ticketType?->name,
                'owner' => $ticket->owner?->name,
                'ticket_id' => $ticket->id,
            ],
        ]);
    }
}

