<?php

namespace App\Http\Controllers;

use App\Models\CreatorProfile;
use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CreatorProfileController extends Controller
{
    /**
     * Exibe a página de explorar criadores
     */
    public function explore(Request $request)
    {
        $search = $request->get('search');
        $category = $request->get('category');

        $query = CreatorProfile::with('user')
            ->active()
            ->orderBy('followers_count', 'desc');

        if ($search) {
            $query->search($search);
        }

        if ($category) {
            $query->where('category', $category);
        }

        $creators = $query->paginate(12);

        // Buscar categorias disponíveis
        $categories = CreatorProfile::active()
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category');

        return view('cliente.creators.explore', compact('creators', 'categories', 'search', 'category'));
    }

    /**
     * Exibe o perfil de um criador específico
     */
    public function show($username)
    {
        $creator = CreatorProfile::with(['user', 'news' => function ($query) {
            $query->where('status', 'published')
                ->latest()
                ->take(9);
        }])
            ->where('username', $username)
            ->active()
            ->firstOrFail();

        $isFollowing = false;
        if (Auth::check()) {
            $isFollowing = $creator->isFollowedBy(Auth::id());
        }

        return view('cliente.creators.profile', compact('creator', 'isFollowing'));
    }

    /**
     * Seguir ou deixar de seguir um criador
     */
    public function toggleFollow(Request $request, $username)
    {
        if (! Auth::check()) {
            return response()->json(['error' => 'Você precisa estar logado'], 401);
        }

        $creator = CreatorProfile::where('username', $username)->active()->firstOrFail();
        $user = Auth::user();

        $isFollowing = $creator->isFollowedBy($user->id);

        if ($isFollowing) {
            // Deixar de seguir
            $creator->followers()->detach($user->id);
            $creator->decrement('followers_count');
            $action = 'unfollowed';
        } else {
            // Seguir
            $creator->followers()->attach($user->id);
            $creator->increment('followers_count');
            $action = 'followed';
        }

        return response()->json([
            'action' => $action,
            'followers_count' => $creator->fresh()->followers_count,
            'is_following' => ! $isFollowing,
        ]);
    }

    /**
     * Buscar criadores via AJAX
     */
    public function search(Request $request)
    {
        $term = $request->get('q');

        if (strlen($term) < 2) {
            return response()->json([]);
        }

        $creators = CreatorProfile::with('user')
            ->active()
            ->search($term)
            ->limit(10)
            ->get()
            ->map(function ($creator) {
                return [
                    'id' => $creator->id,
                    'username' => $creator->username,
                    'display_name' => $creator->display_name,
                    'profile_image_url' => $creator->profile_image_url,
                    'followers_count' => $creator->followers_count,
                    'is_verified' => $creator->is_verified,
                    'bio' => $creator->bio ? Str::limit($creator->bio, 50) : null,
                ];
            });

        return response()->json($creators);
    }

    /**
     * Exibe os criadores que o usuário segue
     */
    public function following()
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        $followingCreators = CreatorProfile::whereHas('followers', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
            ->with('user')
            ->active()
            ->orderBy('display_name')
            ->paginate(12);

        return view('cliente.creators.following', compact('followingCreators'));
    }

    /**
     * Feed personalizado baseado nos criadores seguidos
     */
    public function feed()
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Buscar notícias dos criadores que o usuário segue
        $news = News::whereHas('creatorProfile.followers', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
            ->with(['author', 'creatorProfile'])
            ->where('status', 'published')
            ->latest()
            ->paginate(10);

        // Buscar criadores sugeridos (não seguidos ainda)
        $suggestedCreators = CreatorProfile::whereDoesntHave('followers', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
            ->active()
            ->orderBy('followers_count', 'desc')
            ->limit(5)
            ->get();

        return view('cliente.feed', compact('news', 'suggestedCreators'));
    }
}
