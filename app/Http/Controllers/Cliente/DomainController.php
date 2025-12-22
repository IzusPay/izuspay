<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\DomainModel;
use App\Models\PageModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DomainController extends Controller
{
    public function index()
    {
        $domains = DomainModel::where('user_id', Auth::user()->id)->get();

        return view('cliente.domains.index', compact('domains'));
    }

    public function create()
    {
        return view('cliente.domains.create');
    }

    public function store(Request $request)
    {
        DomainModel::create([
            'user_id' => Auth::user()->id,
            'domain' => $request->domain,
        ]);

        return redirect()->route('cliente.domains.index')->with('success', 'Domain added successfully.');
    }

    public function edit(DomainModel $domain)
    {
        if (Auth::user()->id !== $domain->user_id) {
            abort(403, 'Unauthorized action.');
        }

        return view('cliente.domains.edit', compact('domain'));
    }

    public function update(Request $request, DomainModel $domain)
    {
        if (Auth::user()->id !== $domain->user_id) {
            abort(403, 'Unauthorized action.');
        }

        $domain->update([
            'domain' => $request->domain,
        ]);

        return redirect()->route('cliente.domains.index')->with('success', 'Domain updated successfully.');
    }

    public function destroy(DomainModel $domain)
    {
        if (Auth::user()->id !== $domain->user_id) {
            abort(403, 'Unauthorized action.');
        }

        $domain->delete();

        return redirect()->route('cliente.domains.index')->with('success', 'Domain deleted successfully.');
    }

    public function attachPage(Request $request, DomainModel $domain)
    {
        if (Auth::user()->id !== $domain->user_id) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'page_id' => 'required|exists:pages,id',
        ]);

        $page = PageModel::find($request->page_id);
        $page->domain_id = $domain->id;
        $page->save();

        return redirect()->route('cliente.domains.index')->with('success', 'Page attached to domain successfully.');
    }
}
