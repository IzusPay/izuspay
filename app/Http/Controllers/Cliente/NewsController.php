<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\CreatorProfile;
use App\Models\News;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Buscar criadores que o usuário segue
        $followingCreators = CreatorProfile::whereHas('followers', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
            ->active()
            ->limit(10)
            ->get();

        // Buscar criadores com assinaturas ativas através das vendas
        $subscribedCreatorIds = Subscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->where('renews_at', '>', Carbon::now())
            ->whereHas('sale', function ($query) {
                $query->where('status', 'paid')
                    ->whereNotNull('plan_id');
            })
            ->with('sale.plan.association.creatorProfile')
            ->get()
            ->pluck('sale.plan.association.creatorProfile.id')
            ->filter()
            ->unique();

        $subscribedCreators = CreatorProfile::whereIn('id', $subscribedCreatorIds)
            ->active()
            ->get();

        $accessibleCreatorIds = $followingCreators->pluck('id')
            ->merge($subscribedCreators->pluck('id'))
            ->unique();

        // Buscar notícias dos criadores acessíveis
        if ($accessibleCreatorIds->count() > 0) {
            $news = News::with(['author', 'creatorProfile'])
                ->where('status', 'published')
                ->where(function ($query) use ($accessibleCreatorIds) {
                    $query->whereIn('creator_profile_id', $accessibleCreatorIds)
                        ->orWhereNull('creator_profile_id')
                        ->orWhere('creator_profile_id', 0);
                })
                ->where(function ($q) {
                    $q->where('is_private', 0)
                        ->orWhereNull('is_private');
                })
                ->latest()
                ->take(20)
                ->get();

        } else {
            // Se não segue ninguém e não tem assinaturas, mostrar apenas conteúdo público
            $news = News::with(['author', 'creatorProfile'])
                ->where('status', 'published')
                ->where(function ($query) {
                    $query->where('is_private', false)
                        ->orWhereNull('is_private');
                })
                ->latest()
                ->take(6)
                ->get();
        }

        // Buscar criadores sugeridos para seguir
        $suggestedCreators = CreatorProfile::whereDoesntHave('followers', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
            ->active()
            ->orderBy('followers_count', 'desc')
            ->limit(8)
            ->get();

        return view('cliente.dashboard-mobile', compact('news', 'followingCreators', 'suggestedCreators'));
    }

    private function userHasAccessToCreator($userId, $creatorId)
    {
        // Verifica se segue o criador
        $isFollowing = CreatorProfile::where('id', $creatorId)
            ->whereHas('followers', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->exists();

        $hasActiveSubscription = Subscription::where('user_id', $userId)
            ->where('status', 'active')
            ->where('renews_at', '>', Carbon::now())
            ->whereHas('sale', function ($query) use ($creatorId) {
                $query->where('status', 'paid')
                    ->whereNotNull('plan_id')
                    ->whereHas('plan.association.creatorProfile', function ($q) use ($creatorId) {
                        $q->where('id', $creatorId);
                    });
            })
            ->exists();

        return $isFollowing || $hasActiveSubscription;
    }

    public function profile($username)
    {
        $user = auth()->user();

        $creator = CreatorProfile::withCount(['posts', 'followers', 'following'])
            ->where('username', $username)
            ->firstOrFail();

        // Verifica se o usuário logado segue esse criador
        $isFollowing = $creator->followers()->where('user_id', $user->id)->exists();

        $hasActiveSubscription = Subscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->where('renews_at', '>', Carbon::now())
            ->whereHas('sale', function ($query) use ($creator) {
                $query->where('status', 'paid')
                    ->whereNotNull('plan_id')
                    ->whereHas('plan.association.creatorProfile', function ($q) use ($creator) {
                        $q->where('id', $creator->id);
                    });
            })
            ->exists();

        $creator->load(['news' => function ($q) use ($hasActiveSubscription) {
            $q->where('status', 'published');

            if (! $hasActiveSubscription) {
                // Se não tem assinatura, mostrar apenas conteúdo público
                $q->where(function ($query) {
                    $query->where('is_private', false)
                        ->orWhereNull('is_private');
                });
            }

            $q->latest();
        }]);

        return view('cliente.profile.mobile', compact('creator', 'isFollowing', 'hasActiveSubscription'));
    }

    /**
     * Exibe todas as notícias
     */
    public function all(Request $request)
    {
        $search = $request->get('search');
        $category = $request->get('category');

        $query = News::with(['author', 'creatorProfile'])
            ->where('status', 'published');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                    ->orWhere('content', 'LIKE', "%{$search}%");
            });
        }

        if ($category) {
            $query->where('category', $category);
        }

        $news = $query->latest()->paginate(12);

        // Buscar categorias disponíveis
        $categories = News::where('status', 'published')
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category');

        return view('cliente.news.index', compact('news', 'categories', 'search', 'category'));
    }

    /**
     * Exibe uma notícia específica
     */
    public function show($id)
    {
        $user = auth()->user();

        $news = News::with(['author', 'creatorProfile'])
            ->where('status', 'published')
            ->findOrFail($id);

        if ($news->is_private && $news->creatorProfile) {
            $hasAccess = $this->userHasAccessToCreator($user->id, $news->creator_profile_id);

            if (! $hasAccess) {
                abort(403, 'Você precisa de uma assinatura ativa para acessar este conteúdo.');
            }
        }

        // Incrementar visualizações
        $news->increment('views_count');

        // Buscar notícias relacionadas
        $relatedNews = News::with(['author', 'creatorProfile'])
            ->where('status', 'published')
            ->where('id', '!=', $news->id)
            ->where(function ($query) use ($news) {
                if ($news->category) {
                    $query->where('category', $news->category);
                }
                if ($news->creatorProfile) {
                    $query->orWhere('creator_profile_id', $news->creator_profile_id);
                }
            })
            ->where(function ($query) use ($user, $news) {
                $query->where('is_private', false)
                    ->orWhereNull('is_private');

                if ($news->creatorProfile) {
                    $hasAccess = $this->userHasAccessToCreator($user->id, $news->creator_profile_id);
                    if ($hasAccess) {
                        $query->orWhere('creator_profile_id', $news->creator_profile_id);
                    }
                }
            })
            ->latest()
            ->limit(4)
            ->get();

        return view('cliente.news.show', compact('news', 'relatedNews'));
    }
}
