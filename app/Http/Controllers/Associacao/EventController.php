<?php

namespace App\Http\Controllers\Associacao;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Sale;
use App\Models\Ticket;
use App\Models\TicketOrder;
use App\Models\TicketType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EventController extends Controller
{
    public function index()
    {
        $associationId = Auth::user()->association_id;
        $events = Event::where('association_id', $associationId)->orderBy('starts_at', 'desc')->get();

        return view('associacao.eventos.index', compact('events'));
    }

    public function create()
    {
        return view('associacao.eventos.create_edit');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'location' => ['nullable', 'string', 'max:255'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'capacity' => ['required', 'integer', 'min:0'],
            'status' => ['required', 'string'],
            'brand_color' => ['nullable', 'string', 'max:20'],
        ]);
        $data['association_id'] = Auth::user()->association_id;
        if ($request->hasFile('brand_logo')) {
            $data['brand_logo'] = $request->file('brand_logo')->store('events', 'public');
        }
        $event = Event::create($data);

        return redirect()->route('associacao.eventos.edit', $event)->with('success', 'Evento criado.');
    }

    public function edit(Event $evento)
    {
        abort_if($evento->association_id !== Auth::user()->association_id, 403);
        $ticketTypes = $evento->ticketTypes()->orderBy('price')->get();
        $orders = TicketOrder::with(['user', 'sale', 'tickets', 'ticketType'])
            ->where('event_id', $evento->id)
            ->orderBy('id', 'desc')
            ->get();
        $totalOrders = $orders->count();
        $paidOrders = $orders->where('status', 'paid');
        $awaitingOrders = $orders->where('status', 'awaiting_payment');
        $totalGenerated = $orders->sum(fn ($o) => (float) $o->unit_price * (int) $o->quantity);
        $totalPaid = $paidOrders->sum(fn ($o) => (float) $o->unit_price * (int) $o->quantity);
        $conversionRate = $totalOrders > 0 ? round(($paidOrders->count() / $totalOrders) * 100, 2) : 0.0;
        $metrics = [
            'total_generated' => $totalGenerated,
            'total_paid' => $totalPaid,
            'total_awaiting' => $awaitingOrders->sum(fn ($o) => (float) $o->unit_price * (int) $o->quantity),
            'orders_count' => $totalOrders,
            'paid_count' => $paidOrders->count(),
            'awaiting_count' => $awaitingOrders->count(),
            'conversion_rate' => $conversionRate,
        ];

        return view('associacao.eventos.create_edit', compact('evento', 'ticketTypes', 'orders', 'metrics'));
    }

    public function show(Event $evento)
    {
        abort_if($evento->association_id !== Auth::user()->association_id, 403);
        $orders = TicketOrder::with(['user', 'sale', 'tickets', 'ticketType'])
            ->where('event_id', $evento->id)
            ->orderBy('id', 'desc')
            ->get();
        $totalOrders = $orders->count();
        $paidOrders = $orders->where('status', 'paid');
        $awaitingOrders = $orders->where('status', 'awaiting_payment');
        $totalGenerated = $orders->sum(fn ($o) => (float) $o->unit_price * (int) $o->quantity);
        $totalPaid = $paidOrders->sum(fn ($o) => (float) $o->unit_price * (int) $o->quantity);
        $conversionRate = $totalOrders > 0 ? round(($paidOrders->count() / $totalOrders) * 100, 2) : 0.0;
        $metrics = [
            'total_generated' => $totalGenerated,
            'total_paid' => $totalPaid,
            'total_awaiting' => $awaitingOrders->sum(fn ($o) => (float) $o->unit_price * (int) $o->quantity),
            'orders_count' => $totalOrders,
            'paid_count' => $paidOrders->count(),
            'awaiting_count' => $awaitingOrders->count(),
            'conversion_rate' => $conversionRate,
        ];
        $dashboardOnly = true;
        return view('associacao.eventos.create_edit', compact('evento', 'orders', 'metrics', 'dashboardOnly'));
    }

    public function update(Request $request, Event $evento)
    {
        abort_if($evento->association_id !== Auth::user()->association_id, 403);
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'location' => ['nullable', 'string', 'max:255'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'capacity' => ['required', 'integer', 'min:0'],
            'status' => ['required', 'string'],
            'brand_color' => ['nullable', 'string', 'max:20'],
        ]);
        if ($request->hasFile('brand_logo')) {
            if ($evento->brand_logo) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($evento->brand_logo);
            }
            $data['brand_logo'] = $request->file('brand_logo')->store('events', 'public');
        }
        $evento->update($data);

        return redirect()->route('associacao.eventos.edit', $evento)->with('success', 'Evento atualizado.');
    }

    public function destroy(Event $evento)
    {
        abort_if($evento->association_id !== Auth::user()->association_id, 403);
        $evento->delete();

        return redirect()->route('associacao.eventos.index')->with('success', 'Evento excluído.');
    }

    public function addTicketType(Request $request, Event $evento)
    {
        abort_if($evento->association_id !== Auth::user()->association_id, 403);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'capacity' => ['required', 'integer', 'min:0'],
            'per_order_limit' => ['required', 'integer', 'min:1'],
            'is_active' => ['boolean'],
        ]);
        $data['event_id'] = $evento->id;
        TicketType::create($data);

        return redirect()->route('associacao.eventos.edit', $evento)->with('success', 'Tipo de ingresso adicionado.');
    }

    public function markOrderPaid(Request $request, Event $evento, TicketOrder $order)
    {
        abort_if($evento->association_id !== Auth::user()->association_id, 403);
        abort_if($order->event_id !== $evento->id, 403);

        if ($order->status === 'paid') {
            return redirect()->route('associacao.eventos.edit', $evento)->with('info', 'Pedido já está marcado como pago.');
        }

        DB::transaction(function () use ($order) {
            $sale = $order->sale;
            if ($sale && $sale->status !== 'paid') {
                $sale->update(['status' => 'paid']);
            }
            $order->update(['status' => 'paid']);

            $existingTickets = Ticket::where('ticket_order_id', $order->id)->count();
            if ($existingTickets < (int) $order->quantity) {
                $toCreate = (int) $order->quantity - $existingTickets;
                for ($i = 0; $i < $toCreate; $i++) {
                    Ticket::create([
                        'ticket_order_id' => $order->id,
                        'ticket_type_id' => $order->ticket_type_id,
                        'owner_user_id' => $order->user_id,
                        'qr_token' => (string) \Illuminate\Support\Str::uuid(),
                        'status' => 'issued',
                    ]);
                }
            }
        });

        return redirect()->route('associacao.eventos.edit', $evento)->with('success', 'Pedido marcado como pago e ingressos emitidos.');
    }
}
