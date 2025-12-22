<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CreatorProfile;

class CreatorProfileController extends Controller
{
    public function index()
    {
        $creatorProfiles = CreatorProfile::with(['user', 'association'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.creator_profiles.index', compact('creatorProfiles'));
    }
}
