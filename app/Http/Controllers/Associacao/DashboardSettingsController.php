<?php

namespace App\Http\Controllers\Associacao;

use App\Http\Controllers\Controller;
use App\Models\DashboardSetting;
use Illuminate\Http\Request;

class DashboardSettingsController extends Controller
{
    public function saveLayout(Request $request)
    {
        $request->validate([
            'layout' => 'required|array',
        ]);

        DashboardSetting::updateOrCreate(
            ['user_id' => auth()->id()],
            ['layout' => $request->layout]
        );

        return response()->json(['message' => 'Layout salvo com sucesso!']);
    }
}
