<?php

namespace App\Http\Controllers\Associacao;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\TicketType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        $event = Event::create($data);

        return redirect()->route('associacao.eventos.edit', $event)->with('success', 'Evento criado.');
    }

    public function edit(Event $evento)
    {
        abort_if($evento->association_id !== Auth::user()->association_id, 403);
        $ticketTypes = $evento->ticketTypes()->orderBy('price')->get();

        return view('associacao.eventos.create_edit', compact('evento', 'ticketTypes'));
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
}
