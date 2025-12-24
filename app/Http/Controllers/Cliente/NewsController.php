<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\CreatorProfile;
use App\Models\Event;
use App\Models\News;
use App\Models\Sale;
use App\Models\Subscription;
use App\Models\TicketOrder;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NewsController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $now = Carbon::now();
        $thirtyDaysAgo = $now->copy()->subDays(30);

        $totalSpent = Sale::where('user_id', $user->id)
            ->where('status', 'paid')
            ->sum('total_price');

        $subscriptionsCount = Subscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->where('renews_at', '>', $now)
            ->count();

        $eventsPurchased = TicketOrder::where('user_id', $user->id)
            ->where('status', 'paid')
            ->count();

        $pixHoje = Sale::where('user_id', $user->id)
            ->where('status', 'paid')
            ->where('payment_method', 'pix')
            ->whereDate('updated_at', $now->toDateString())
            ->sum('total_price');
        $cartaoHoje = Sale::where('user_id', $user->id)
            ->where('status', 'paid')
            ->where('payment_method', 'credit_card')
            ->whereDate('updated_at', $now->toDateString())
            ->sum('total_price');
        $boletoHoje = Sale::where('user_id', $user->id)
            ->where('status', 'paid')
            ->where('payment_method', 'boleto')
            ->whereDate('updated_at', $now->toDateString())
            ->sum('total_price');

        $labels = [];
        $data = [];
        for ($i = 29; $i >= 0; $i--) {
            $day = $now->copy()->subDays($i)->toDateString();
            $labels[] = Carbon::parse($day)->format('d/m');
            $daySum = Sale::where('user_id', $user->id)
                ->where('status', 'paid')
                ->whereDate('updated_at', $day)
                ->sum('total_price');
            $data[] = (float) $daySum;
        }
        $revenueChartData = ['labels' => $labels, 'data' => $data];
        $averageTicket = Sale::where('user_id', $user->id)
            ->where('status', 'paid')
            ->avg('total_price') ?? 0;
        $transacoes = Sale::where('user_id', $user->id)->count();

        $banners = [];
        $afiliados = $subscriptionsCount;
        $saldo = 0.0;

        $latestEvents = Event::where('status', 'published')
            ->orderBy('starts_at', 'desc')
            ->limit(8)
            ->get();

        return view('cliente.dashboard', compact(
            'banners',
            'totalSpent',
            'saldo',
            'pixHoje',
            'cartaoHoje',
            'boletoHoje',
            'revenueChartData',
            'averageTicket',
            'afiliados',
            'transacoes',
            'eventsPurchased',
            'latestEvents'
        ));
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
        $user = Auth::user();

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
        $user = Auth::user();

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
