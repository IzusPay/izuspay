<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Association;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BannerController extends Controller
{
    public function index()
    {
        $banners = Banner::with('association')->latest()->paginate(12);

        return view('admin.marketing.banners.index', compact('banners'));
    }

    public function create()
    {
        return view('admin.marketing.banners.create_edit');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'image_url' => ['required', 'url'],
            'link' => ['nullable', 'url'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        $associationIds = Association::pluck('id');
        foreach ($associationIds as $associationId) {
            Banner::create($validated + ['association_id' => $associationId]);
        }

        return redirect()->route('admin.marketing.banners.index')
            ->with('success', 'Banner criado com sucesso!');
    }

    public function edit(Banner $banner)
    {
        return view('admin.marketing.banners.create_edit', compact('banner'));
    }

    public function update(Request $request, Banner $banner)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'image_url' => ['required', 'url'],
            'link' => ['nullable', 'url'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        $banner->update($validated);

        return redirect()->route('admin.marketing.banners.index')
            ->with('success', 'Banner atualizado com sucesso!');
    }

    public function destroy(Banner $banner)
    {
        $banner->delete();

        return redirect()->route('admin.marketing.banners.index')
            ->with('success', 'Banner excluído com sucesso!');
    }
}
