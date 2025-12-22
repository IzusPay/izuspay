<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreatorProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'username',
        'display_name',
        'bio',
        'profile_image',
        'cover_image',
        'is_verified',
        'followers_count',
        'following_count',
        'posts_count',
        'category',
        'website',
        'location',
        'is_active',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
        'is_active' => 'boolean',
        'followers_count' => 'integer',
        'following_count' => 'integer',
        'posts_count' => 'integer',
    ];

    /**
     * Relacionamento com o usuário
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relacionamento com as notícias/posts do criador
     */
    public function news()
    {
        return $this->hasMany(News::class, 'creator_profile_id');
    }

    public function association()
    {
        return $this->hasOneThrough(Association::class, User::class, 'id', 'id', 'user_id', 'association_id');
    }

    /**
     * Relacionamento com os seguidores
     */
    public function followers()
    {
        return $this->belongsToMany(User::class, 'creator_followers', 'creator_profile_id', 'user_id')
            ->using(CreatorFollower::class) // usa a model pivot customizada
            ->withTimestamps();
    }

    /**
     * Relacionamento com quem o criador segue
     */
    public function following()
    {
        return $this->belongsToMany(CreatorProfile::class, 'creator_follows', 'follower_id', 'following_id')
            ->withTimestamps();
    }

    /**
     * Verifica se um usuário segue este criador
     */
    public function isFollowedBy($userId)
    {
        return $this->followers()->where('user_id', $userId)->exists();
    }

    /**
     * Verifica se um usuário tem assinatura ativa deste criador
     */
    public function hasActiveSubscriber($userId)
    {
        return \App\Models\Subscription::query()
            ->whereHas('sale.plan.association.creatorProfile', function ($query) {
                $query->where('creator_profiles.id', $this->id);
            })
            ->where('user_id', $userId)
            ->where('status', 'active')
            ->where('renews_at', '>', now())
            ->exists();
    }

    /**
     * Relacionamento com assinaturas ativas através das vendas da associação
     */
    public function activeSubscriptions()
    {
        return \App\Models\Subscription::query()
            ->whereHas('sale.plan.association.creatorProfile', function ($query) {
                $query->where('creator_profiles.id', $this->id);
            })
            ->where('status', 'active')
            ->where('renews_at', '>', now());
    }

    /**
     * Relacionamento com todas as vendas do criador através da associação
     */
    public function sales()
    {
        return \App\Models\Sale::query()
            ->whereHas('plan.association.creatorProfile', function ($query) {
                $query->where('creator_profiles.id', $this->id);
            })
            ->orWhereHas('product.association.creatorProfile', function ($query) {
                $query->where('creator_profiles.id', $this->id);
            });
    }

    /**
     * Verifica se usuário pode ver conteúdo privado (seguidor OU assinante ativo)
     */
    public function canUserViewPrivateContent($userId)
    {
        return $this->isFollowedBy($userId) || $this->hasActiveSubscriber($userId);
    }

    /**
     * Verifica se usuário pode ver conteúdo premium (apenas assinantes ativos)
     */
    public function canUserViewPremiumContent($userId)
    {
        return $this->hasActiveSubscriber($userId);
    }

    /**
     * Retorna todos os assinantes ativos do criador
     */
    public function getActiveSubscribersAttribute()
    {
        return $this->activeSubscriptions()
            ->with('user')
            ->get()
            ->pluck('user')
            ->unique('id');
    }

    /**
     * Accessor para URL da imagem de perfil
     */
    public function getProfileImageUrlAttribute()
    {
        if ($this->profile_image) {
            return asset('storage/'.$this->profile_image);
        }

        // Retorna uma imagem padrão baseada nas iniciais
        return 'https://ui-avatars.com/api/?name='.urlencode($this->display_name).'&background=22c55e&color=fff&size=200';
    }

    /**
     * Accessor para URL da imagem de capa
     */
    public function getCoverImageUrlAttribute()
    {
        if ($this->cover_image) {
            return asset('storage/'.$this->cover_image);
        }

        return null;
    }

    /**
     * Scope para buscar criadores ativos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para buscar por nome ou username
     */
    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('display_name', 'LIKE', "%{$term}%")
                ->orWhere('username', 'LIKE', "%{$term}%")
                ->orWhere('bio', 'LIKE', "%{$term}%");
        });
    }

    /**
     * Relacionamento com os planos através da associação
     */
    public function plans()
    {
        return $this->hasManyThrough(
            \App\Models\Plan::class,
            \App\Models\Association::class,
            'id', // Foreign key on associations table
            'association_id', // Foreign key on plans table
            'user_id', // Local key on creator_profiles table
            'association_id' // Local key on users table
        )->through('user');
    }

    /**
     * Busca os planos ativos da associação do criador
     */
    public function getActivePlans()
    {
        return $this->user->association->plans()
            ->where('is_active', true)
            ->orderBy('price')
            ->get();
    }

    /**
     * Formata os planos para exibição na view pública
     */
    public function getFormattedPlansAttribute()
    {
        $plans = $this->getActivePlans();
        $formattedPlans = [];

        foreach ($plans as $index => $plan) {
            $priceInReais = $plan->price_in_reais; // Using Plan's accessor

            // Determinar o período baseado na duração
            $period = $this->getPeriodText($plan->duration_in_months);

            // Calcular desconto se houver
            $discount = null;
            $discountPercent = 0; // Initialize discount percentage as number
            $originalPrice = null;
            if ($plan->duration_in_months > 1) {
                $monthlyPrice = $plans->where('duration_in_months', 1)->first();
                if ($monthlyPrice) {
                    $monthlyPriceInReais = $monthlyPrice->price_in_reais; // Using Plan's accessor
                    $totalMonthlyPrice = $monthlyPriceInReais * $plan->duration_in_months;
                    if ($priceInReais < $totalMonthlyPrice) {
                        $discountPercent = round((($totalMonthlyPrice - $priceInReais) / $totalMonthlyPrice) * 100);
                        $discount = $discountPercent.'%';
                        $originalPrice = $totalMonthlyPrice;
                    }
                }
            }

            $formattedPlans[] = (object) [
                'id' => $plan->id,
                'name' => $plan->name,
                'price' => $priceInReais,
                'formatted_price' => $plan->formatted_price, // Using Plan's formatted_price accessor
                'period' => $period,
                'description' => $plan->description,
                'discount' => $discount,
                'discount_percentage' => $discountPercent, // Added discount_percentage as number
                'original_price' => $originalPrice,
                'popular' => $plan->duration_in_months == 12, // Marcar anual como popular
                'features' => $this->getPlanFeatures($plan->duration_in_months),
                'duration_in_months' => $plan->duration_in_months,
            ];
        }

        return collect($formattedPlans);
    }

    /**
     * Retorna o texto do período baseado na duração
     */
    private function getPeriodText($durationInMonths)
    {
        switch ($durationInMonths) {
            case 1:
                return 'mês';
            case 3:
                return '3 meses';
            case 6:
                return '6 meses';
            case 12:
                return '12 meses';
            default:
                return $durationInMonths.' meses';
        }
    }

    /**
     * Retorna as features baseadas na duração do plano
     */
    private function getPlanFeatures($durationInMonths)
    {
        $baseFeatures = [
            'Conteúdo exclusivo diário',
            'Chat direto com o criador',
            'Lives privadas semanais',
            'Fotos e vídeos em alta qualidade',
        ];

        switch ($durationInMonths) {
            case 1:
                return $baseFeatures;
            case 3:
                return array_merge($baseFeatures, [
                    'Conteúdo bônus exclusivo',
                    'Acesso antecipado a novos conteúdos',
                    'Desconto em produtos personalizados',
                ]);
            case 6:
                return array_merge($baseFeatures, [
                    'Conteúdo bônus exclusivo',
                    'Acesso antecipado a novos conteúdos',
                    'Videochamada mensal exclusiva',
                    'Conteúdo personalizado',
                    'Acesso vitalício a conteúdos especiais',
                ]);
            case 12:
                return array_merge($baseFeatures, [
                    'Conteúdo bônus exclusivo',
                    'Acesso antecipado a novos conteúdos',
                    'Videochamada mensal exclusiva',
                    'Encontro presencial anual (se possível)',
                    'Kit de produtos físicos exclusivos',
                    'Acesso vitalício a todo conteúdo',
                    'Participação em decisões de conteúdo',
                ]);
            default:
                return $baseFeatures;
        }
    }

    /**
     * Retorna o total de posts do criador
     */
    public function totalPosts()
    {
        return $this->news()->count();
    }

    /**
     * Retorna o total de likes em todos os posts
     */
    public function totalLikes()
    {
        return $this->news()->sum('likes_count');
    }

    /**
     * Retorna o total de assinantes ativos
     */
    public function totalActiveSubscribers()
    {
        return $this->activeSubscriptions()->count();
    }

    /**
     * Retorna o total de posts privados do criador
     */
    public function totalPrivatePosts()
    {
        return $this->news()->where('is_private', true)->count();
    }

    /**
     * Retorna o total de posts públicos do criador
     */
    public function totalPublicPosts()
    {
        return $this->news()->where(function ($query) {
            $query->where('is_private', false)
                ->orWhereNull('is_private');
        })->count();
    }

    /**
     * Retorna o total de vídeos do criador
     */
    public function totalVideos()
    {
        return $this->news()->where('type', 'video')->count();
    }

    /**
     * Retorna o total de imagens do criador
     */
    public function totalImages()
    {
        return $this->news()->where('type', 'image')->count();
    }

    /**
     * Retorna estatísticas completas do criador
     */
    public function getStatsAttribute()
    {
        return [
            'posts' => $this->totalPosts(),
            'public_posts' => $this->totalPublicPosts(),
            'private_posts' => $this->totalPrivatePosts(),
            'videos' => $this->totalVideos(),
            'images' => $this->totalImages(),
            'likes' => $this->totalLikes(),
            'followers' => $this->followers_count,
            'subscribers' => $this->totalActiveSubscribers(),
        ];
    }
}
