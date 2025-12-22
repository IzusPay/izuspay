<?php

namespace App\Http\Controllers;

use App\Models\Controller;
use Illuminate\Http\Request;

class ControllerController extends Controller
{
    public function index(Request $request)
    {
        $controllers = Controller::query();

        if ($request->filled('name')) {
            $controllers->where('name', 'like', '%'.$request->name.'%');
        }

        if ($request->filled('ip')) {
            $controllers->where('ip', 'like', '%'.$request->ip.'%');
        }

        if ($request->filled('device_id')) {
            $controllers->where('device_id', 'like', '%'.$request->device_id.'%');
        }

        $controllers = $controllers->paginate(5); // Altere o valor para o número de itens por página

        return view('controllers.index', [
            'controllers' => $controllers,
        ]);
    }

    public function create()
    {
        return view('controllers.create');
    }

    public function store(Request $request)
    {
        Controller::create($request->all());

        return redirect()->route('controllers.index')->with('success', 'Controlador Criado com sucesso!');
    }

    public function show($id)
    {
        return response()->json(Controller::findOrFail($id));
    }

    public function edit(Controller $controller)
    {
        return view('controllers.edit', compact('controller'));
    }

    public function updateController(Request $request, Controller $controller)
    {
        $request->validate([
            'ip' => 'sometimes|ip',
            'port' => 'sometimes|integer',
            'type' => 'sometimes|string',
            'id_device' => 'sometimes|string',
        ]);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        $controller->update($request->all());

        return redirect()->route('controllers.index')->with('success', 'Controlador editado com sucesso!');
    }

    public function destroyController(Controller $controllers)
    {
        $controllers->delete();

        return redirect()->route('controllers.index')->with('success', 'Controlador removido com sucesso!');
    }
}
