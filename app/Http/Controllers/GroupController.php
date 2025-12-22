<?php

namespace App\Http\Controllers;

use App\Models\Controller;
use App\Models\Group;
use App\Models\GroupAcess;
use App\Models\GroupAcessGroupControllator;
use App\Models\GroupControlator;
use App\Services\PacFace\ControlId\GroupService;
use App\Services\PacFace\ControlId\Login;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    // protected $groupService;
    // protected $loginService;

    // public function __construct(GroupService $groupService, Login $loginService)
    // {
    //     $this->groupService = $groupService;
    //     $this->loginService = $loginService;
    // }

    public function index()
    {
        $groupAcesses = GroupAcess::with('groupAcessGroupControllators', 'groupControlators')->orderBy('id', 'desc')->paginate(10);

        return view('groups.index', compact('groupAcesses'));
    }

    public function create()
    {
        $controllers = Controller::all();

        return view('groups.create', compact('controllers'));
    }

    public function store(Request $request)
    {
        try {
            // Criação do grupo de acesso
            $groupAcess = GroupAcess::create([
                'name' => $request->name,
                'status' => 1,
                'type' => 1,
            ]);

            // Criação dos grupos em cada controlador
            foreach ($request['controllers'] as $controllerId) {
                $controller = Controller::where('id', '=', $controllerId)->first();
                // $token = $this->loginService->login($controller->ip);

                // $group = $this->groupService->store($controller->ip, $token, $request->name);

                $groupLocal = Group::create([
                    'name' => $request->name,
                    // 'external_id' => $group['ids'][0],
                    'external_id' => 1,
                    'status' => 1,
                    'type' => 1,
                ]);

                $groupController = GroupControlator::create([
                    'group_id' => $groupLocal->id,
                    'controller_id' => $controllerId,
                ]);

                GroupAcessGroupControllator::create([
                    'id_group_acess' => $groupAcess->id,
                    'id_group_controllators' => $groupController->id,
                ]);
            }

            // Exibe uma mensagem de sucesso na view
            return redirect()->route('groups.index')->with('success', 'Group created successfully!');
        } catch (\Exception $e) {
            // Exibe uma mensagem de erro na view
            return redirect()->route('groups.index')->with('error', 'Group Error created! - '.$e->getMessage());
        }
    }

    public function show($id)
    {
        // Obtém o grupo e passa para a view
        $group = Group::with('controllers')->findOrFail($id);

        return view('groups.show', compact('group'));
    }

    public function edit(GroupAcess $group)
    {
        $controllers = Controller::all();

        return view('groups.edit', compact('group', 'controllers'));
    }

    public function updates(GroupAcess $group, Request $request)
    {
        // Atualiza os dados do grupo
        $group->update($request->all());

        // Retorna para a view do grupo com uma mensagem de sucesso
        return redirect()->route('groups.edit', $group)
            ->with('success', 'Grupo atualizado com sucesso!');
    }

    public function destroys(GroupAcess $group)
    {
        $group->delete();

        return redirect()->route('groups.index')->with('success', 'Grupo excluido com sucesso!');
    }

    public function viculeGroupUser(Request $request)
    {
        try {
            $group = Group::create([
                'name' => $request['name'],
                'external_id' => $request['external_id'],
                'status' => $request['status'],
                'type' => $request['type'],
            ]);

            // Criação dos registros em GroupControlator
            foreach ($request['controllers'] as $controllerId) {
                GroupControlator::create([
                    'group_id' => $group->id,
                    'controller_id' => $controllerId,
                ]);
            }

            // Exibe uma mensagem de sucesso na view
            return redirect()->route('groups.index')->with('success', 'Group created successfully!');
        } catch (\Exception $e) {
            // Exibe uma mensagem de erro na view
            return redirect()->route('groups.index')->with('error', 'Group Error created! - '.$e->getMessage());
        }
    }
}
