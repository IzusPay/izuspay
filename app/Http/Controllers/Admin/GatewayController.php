<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Gateway;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class GatewayController extends Controller
{
    /**
     * Exibe a lista de gateways.
     */
    public function index()
    {
        $gateways = Gateway::latest()->paginate(10);
        return view('admin.gateways.index', compact('gateways'));
    }

    /**
     * Mostra o formulário para criar um novo gateway.
     */
    public function create()
    {
        return view('admin.gateways.create');
    }

    /**
     * Salva um novo gateway no banco de dados.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:gateways,name',
            'logo_url' => 'nullable|url',
            'is_active' => 'required|boolean',
            'credentials_schema' => 'required|json', // Mantém a validação como JSON
            'card_fee_percentage' => 'nullable|numeric|min:0',
            'pix_fee_percentage' => 'nullable|numeric|min:0',
            'fixed_fee' => 'nullable|numeric|min:0',
            'order' => 'required|integer|min:1',

        ]);

        // Tente decodificar o JSON.
        try {
            $schema = json_decode($validated['credentials_schema'], true, 512, JSON_THROW_ON_ERROR);
            if (!isset($schema['fields']) || !is_array($schema['fields'])) {
                throw new \InvalidArgumentException('O JSON do schema deve conter uma chave "fields" que seja um array.');
            }
        } catch (\Exception $e) {
            // Se o JSON for inválido, voltamos com o erro.
            return back()->withInput()->withErrors(['credentials_schema' => 'O formato do Schema de Credenciais é inválido: ' . $e->getMessage()]);
        }

        $validated['slug'] = Str::slug($validated['name']);
        $validated['credentials_schema'] = $schema; // Salva o array decodificado

        Gateway::create($validated);

        return redirect()->route('admin.gateways.index')->with('success', 'Gateway criado com sucesso!');
    }

    /**
     * Mostra o formulário para editar um gateway existente.
     */
    public function edit(Gateway $gateway)
    {
        return view('admin.gateways.edit', compact('gateway'));
    }

    /**
     * Atualiza um gateway no banco de dados.
     */
    public function update(Request $request, Gateway $gateway)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('gateways')->ignore($gateway->id)],
            'logo_url' => 'nullable|url',
            'is_active' => 'required|boolean',
            'credentials_schema' => 'required|json',
            'card_fee_percentage' => 'nullable|numeric|min:0',
            'pix_fee_percentage' => 'nullable|numeric|min:0',
            'fixed_fee' => 'nullable|numeric|min:0',
            'order' => 'required|integer|min:1',

        ]);

        // Mesma lógica de decodificação e validação do store()
        try {
            $schema = json_decode($validated['credentials_schema'], true, 512, JSON_THROW_ON_ERROR);
            if (!isset($schema['fields']) || !is_array($schema['fields'])) {
                throw new \InvalidArgumentException('O JSON do schema deve conter uma chave "fields" que seja um array.');
            }
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['credentials_schema' => 'O formato do Schema de Credenciais é inválido: ' . $e->getMessage()]);
        }

        $validated['slug'] = Str::slug($validated['name']);
        $validated['credentials_schema'] = $schema; // Salva o array decodificado

        $gateway->update($validated);

        return redirect()->route('admin.gateways.index')->with('success', 'Gateway atualizado com sucesso!');
    }

    /**
     * Remove um gateway do banco de dados.
     */
    public function destroy(Gateway $gateway)
    {
        $gateway->delete();

        return redirect()->route('admin.gateways.index')->with('success', 'Gateway excluído com sucesso!');
    }
}
