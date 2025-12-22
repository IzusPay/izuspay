<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\PlanModel;
use App\Models\Product;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function index()
    {
        $plans = Plan::paginate(20);

        return view('admin.plans.index', compact('plans'));
    }

    public function create()
    {
        $products = Product::all();

        return view('associacao.plans.create_edit', compact('products'));
    }

    public function store(Request $request)
    {

        $data = $request->validate([
            'name' => 'nullable|string',
            'description' => 'nullable',
            'link_image' => 'nullable',
            'id_plan_external' => 'nullable|string',
            'id_offer_external' => 'nullable|string',
            'link_checkout_external' => 'nullable|string',
            'value' => 'nullable|numeric',
            'page_quantity' => 'nullable|integer',
            'billing_cycle' => 'nullable|in:monthly,quarterly,semiannual,annual,biennial,quadrennial',
            'status' => 'boolean',
        ]);

        PlanModel::create($data);

        return redirect()->route('admin.plans.index');
    }

    public function edit(PlanModel $plan)
    {
        return view('admin.plans.edit', compact('plan'));
    }

    public function update(Request $request, PlanModel $plan)
    {
        $data = $request->validate([
            'name' => 'nullable|string',
            'description' => 'nullable',
            'link_image' => 'nullable',
            'id_plan_external' => 'nullable|string',
            'id_offer_external' => 'nullable|string',
            'link_checkout_external' => 'nullable|string',
            'value' => 'nullable|numeric',
            'page_quantity' => 'nullable|integer',
            'billing_cycle' => 'nullable|in:monthly,quarterly,semiannual,annual,biennial,quadrennial',
            'status' => 'boolean',
        ]);

        $plan->update($data);

        return redirect()->route('admin.plans.index');
    }

    public function destroy(PlanModel $plan)
    {
        $plan->delete();

        return redirect()->route('admin.plans.index');
    }
}
