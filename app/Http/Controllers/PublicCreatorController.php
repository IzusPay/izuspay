<?php

namespace App\Http\Controllers;

use App\Models\CreatorProfile;
use App\Models\News;
use Illuminate\Http\Request;

class PublicCreatorController extends Controller
{
    public function show($username)
    {
        $creator = CreatorProfile::where('username', $username)
            ->with(['user.association.plans' => function ($query) {
                $query->where('is_active', true)->orderBy('price');
            }])
            ->firstOrFail();

        $plans = $creator->user->association->plans->map(function ($plan) {

            // Buscar produtos associados ao plano
            $products = \DB::table('plan_product')
                ->join('products', 'plan_product.product_id', '=', 'products.id')
                ->where('plan_product.plan_id', $plan->id)
                ->select('products.name', 'products.price')
                ->get();

            $plan->productsT = $products;
            $plan->total_priceT = $products->sum('price');
            $plan->formatted_priceT = number_format($plan->total_priceT, 2, ',', '.');

            return $plan;
        });

        // Se não houver planos cadastrados, usar um plano padrão
        if ($plans->isEmpty()) {
            $plans = collect([
                (object) [
                    'id' => 0,
                    'name' => 'Mensal',
                    'price' => 29.90,
                    'formatted_price' => '29,90',
                    'period' => 'mês',
                    'description' => 'Acesso completo ao conteúdo exclusivo',
                    'discount_percentage' => 0,
                    'products' => collect([]),
                    'features' => [
                        'Conteúdo exclusivo diário',
                        'Chat direto com o criador',
                        'Lives privadas semanais',
                        'Fotos e vídeos em alta qualidade',
                    ],
                ],
            ]);
        }

        $isSubscriber = auth()->check() ? $creator->hasActiveSubscriber(auth()->id()) : false;

        $postsQuery = News::where('creator_profile_id', $creator->id)->orderBy('created_at', 'desc');
        if (! $isSubscriber) {
            $postsQuery->where('is_private', false);
        }

        $posts = $postsQuery->paginate(12);

        $stats = [
            'followers' => $creator->followers_count ?? 0,
            'posts' => $creator->totalPosts(),
            'likes' => $creator->totalLikes(),
            'subscribers' => $creator->activeSubscriptions()->count(),
        ];

        return view('public.creator_profile', compact('creator', 'plans', 'stats', 'posts', 'isSubscriber'));
    }

    public function subscribe(Request $request, $username, $planId)
    {
        $creator = CreatorProfile::where('username', $username)->firstOrFail();

        // Verificar se o plano existe e pertence à associação do criador
        $plan = $creator->user->association->plans()
            ->where('id', $planId)
            ->where('is_active', true)
            ->firstOrFail();

        // Validar dados do assinante
        $request->validate([
            'email' => 'required|email',
            'name' => 'required|string|max:255',
        ]);

        // Aqui você poderia calcular novamente o valor baseado nos produtos
        $totalPrice = $plan->products->sum(fn ($product) => $product->price);

        // Exemplo: usar $plan->offer_hash, $plan->product_hash para integração com gateway de pagamentos

        return redirect()->back()->with('success', 'Assinatura iniciada! Valor total: R$ '.number_format($totalPrice, 2, ',', '.'));
    }
}
