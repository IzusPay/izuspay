<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class News extends Model
{
    use HasFactory;

    protected $fillable = [
        'association_id',
        'user_id',
        'creator_profile_id', // NOVO CAMPO ADICIONADO
        'title',
        'slug',
        'excerpt',
        'content',
        'featured_image',
        'status',
        'is_featured',
        'tags',
        'views_count',
        'likes_count', // Added likes_count to fillable
        'published_at',
        'is_private', // Added is_private field
        'category', // NOVO CAMPO ADICIONADO
        'type', // Added type field for content type (text, video, image, etc.)
    ];

    protected $casts = [
        'tags' => 'array',
        'is_featured' => 'boolean',
        'is_private' => 'boolean', // Added is_private cast
        'published_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'likes_count' => 'integer', // Added likes_count cast
        'type' => 'string', // Added type cast
    ];

    // ==================== SCOPES ====================

    /**
     * Notícias publicadas
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->where('published_at', '<=', now());
    }

    /**
     * Notícias públicas (não privadas)
     */
    public function scopePublic($query)
    {
        return $query->where(function ($q) {
            $q->where('is_private', false)
                ->orWhereNull('is_private');
        });
    }

    /**
     * Notícias privadas
     */
    public function scopePrivate($query)
    {
        return $query->where('is_private', true);
    }

    /**
     * Filtrar por tipo de conteúdo (NOVO SCOPE)
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    // ==================== MÉTODOS ====================

    /**
     * Verificar se é conteúdo privado
     */
    public function isPrivate(): bool
    {
        return $this->is_private === true;
    }

    /**
     * Verificar se é conteúdo público
     */
    public function isPublic(): bool
    {
        return ! $this->isPrivate();
    }

    /**
     * Incrementar likes
     */
    public function incrementLikes(): void
    {
        $this->increment('likes_count');
    }

    /**
     * Decrementar likes
     */
    public function decrementLikes(): void
    {
        $this->decrement('likes_count');
    }

    // ==================== RELACIONAMENTOS ====================

    /**
     * Associação da notícia
     */
    public function association(): BelongsTo
    {
        return $this->belongsTo(Association::class);
    }

    /**
     * Autor da notícia
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Perfil do criador da notícia (NOVO RELACIONAMENTO)
     */
    public function creatorProfile(): BelongsTo
    {
        return $this->belongsTo(CreatorProfile::class, 'creator_profile_id');
    }

    // ==================== ACCESSORS ====================

    /**
     * URL da imagem destacada
     */
    public function getFeaturedImageUrlAttribute(): string
    {
        if ($this->featured_image) {
            return asset('storage/'.$this->featured_image);
        }

        return asset('images/default-news.jpg');
    }

    /**
     * Resumo automático se não tiver excerpt
     */
    public function getExcerptAttribute($value): string
    {
        if ($value) {
            return $value;
        }

        return Str::limit(strip_tags($this->content), 150);
    }

    /**
     * Tempo de leitura estimado
     */
    public function getReadingTimeAttribute(): int
    {
        $wordCount = str_word_count(strip_tags($this->content));

        return max(1, ceil($wordCount / 200)); // 200 palavras por minuto
    }

    /**
     * Data formatada para exibição
     */
    public function getFormattedDateAttribute(): string
    {
        return $this->published_at ?
            $this->published_at->format('d/m/Y \à\s H:i') :
            $this->created_at->format('d/m/Y \à\s H:i');
    }

    /**
     * Data relativa (há 2 dias, etc.)
     */
    public function getRelativeDateAttribute(): string
    {
        $date = $this->published_at ?: $this->created_at;

        return $date->diffForHumans();
    }

    /**
     * Nome do autor (prioriza criador, depois autor, depois admin) - NOVO ACCESSOR
     */
    public function getAuthorNameAttribute(): string
    {
        if ($this->creatorProfile) {
            return $this->creatorProfile->display_name;
        }

        if ($this->author) {
            return $this->author->name;
        }

        return 'Admin';
    }

    /**
     * Avatar do autor (prioriza criador, depois autor) - NOVO ACCESSOR
     */
    public function getAuthorAvatarAttribute(): string
    {
        if ($this->creatorProfile) {
            return $this->creatorProfile->profile_image_url;
        }

        if ($this->author) {
            return 'https://ui-avatars.com/api/?name='.urlencode($this->author->name).'&background=22c55e&color=fff&size=200';
        }

        return 'https://ui-avatars.com/api/?name=Admin&background=22c55e&color=fff&size=200';
    }

    // ==================== SCOPES ====================

    /**
     * Notícias em rascunho
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Notícias arquivadas
     */
    public function scopeArchived($query)
    {
        return $query->where('status', 'archived');
    }

    /**
     * Notícias em destaque
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Filtrar por associação
     */
    public function scopeByAssociation($query, $associationId)
    {
        return $query->where('association_id', $associationId);
    }

    /**
     * Filtrar por criador (NOVO SCOPE)
     */
    public function scopeByCreator($query, $creatorProfileId)
    {
        return $query->where('creator_profile_id', $creatorProfileId);
    }

    /**
     * Filtrar por categoria (NOVO SCOPE)
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Buscar por termo
     */
    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('title', 'like', "%{$term}%")
                ->orWhere('content', 'like', "%{$term}%")
                ->orWhere('excerpt', 'like', "%{$term}%");
        });
    }

    /**
     * Ordenar por data de publicação
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('published_at', 'desc')
            ->orderBy('created_at', 'desc');
    }

    /**
     * Notícias de criadores seguidos por um usuário (NOVO SCOPE)
     */
    public function scopeFromFollowedCreators($query, $userId)
    {
        return $query->whereHas('creatorProfile.followers', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        });
    }

    // ==================== MÉTODOS ====================

    /**
     * Gerar slug automaticamente
     */
    public static function boot()
    {
        parent::boot();

        static::creating(function ($news) {
            if (! $news->slug) {
                $news->slug = Str::slug($news->title).'-'.$news->id;

                // Garantir que o slug seja único
                $originalSlug = $news->slug;
                $counter = 1;

                while (static::where('slug', $news->slug)->exists()) {
                    $news->slug = $originalSlug.'-'.$counter;
                    $counter++;
                }
            }
        });

        static::updating(function ($news) {
            if ($news->isDirty('title') && ! $news->isDirty('slug')) {
                $news->slug = Str::slug($news->title);

                // Garantir que o slug seja único
                $originalSlug = $news->slug;
                $counter = 1;

                while (static::where('slug', $news->slug)->where('id', '!=', $news->id)->exists()) {
                    $news->slug = $originalSlug.'-'.$counter;
                    $counter++;
                }
            }
        });

        // Atualizar contador de posts do criador (NOVO)
        static::created(function ($news) {
            if ($news->creator_profile_id && $news->status === 'published') {
                $news->creatorProfile?->increment('posts_count');
            }
        });

        static::updated(function ($news) {
            if ($news->creator_profile_id) {
                $creatorProfile = $news->creatorProfile;
                if ($creatorProfile) {
                    // Recalcular contador de posts publicados
                    $publishedCount = static::where('creator_profile_id', $creatorProfile->id)
                        ->where('status', 'published')
                        ->count();
                    $creatorProfile->update(['posts_count' => $publishedCount]);
                }
            }
        });

        static::deleted(function ($news) {
            if ($news->creator_profile_id) {
                $news->creatorProfile?->decrement('posts_count');
            }
        });
    }

    /**
     * Publicar notícia
     */
    public function publish(): bool
    {
        $result = $this->update([
            'status' => 'published',
            'published_at' => now(),
        ]);

        // Incrementar contador do criador se necessário
        if ($result && $this->creator_profile_id) {
            $this->creatorProfile?->increment('posts_count');
        }

        return $result;
    }

    /**
     * Despublicar notícia
     */
    public function unpublish(): bool
    {
        $result = $this->update([
            'status' => 'draft',
            'published_at' => null,
        ]);

        // Decrementar contador do criador se necessário
        if ($result && $this->creator_profile_id) {
            $this->creatorProfile?->decrement('posts_count');
        }

        return $result;
    }

    /**
     * Arquivar notícia
     */
    public function archive(): bool
    {
        $wasPublished = $this->isPublished();
        $result = $this->update(['status' => 'archived']);

        // Decrementar contador do criador se estava publicada
        if ($result && $wasPublished && $this->creator_profile_id) {
            $this->creatorProfile?->decrement('posts_count');
        }

        return $result;
    }

    /**
     * Incrementar visualizações
     */
    public function incrementViews(): void
    {
        $this->increment('views_count');
    }

    /**
     * Verificar se está publicada
     */
    public function isPublished(): bool
    {
        return $this->status === 'published' &&
               $this->published_at &&
               $this->published_at <= now();
    }

    /**
     * Verificar se é rascunho
     */
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Verificar se está arquivada
     */
    public function isArchived(): bool
    {
        return $this->status === 'archived';
    }

    /**
     * Verificar se pertence a um criador (NOVO MÉTODO)
     */
    public function hasCreator(): bool
    {
        return ! is_null($this->creator_profile_id);
    }

    /**
     * Obter badge do status
     */
    public function getStatusBadge(): string
    {
        $statusBadge = match ($this->status) {
            'published' => '<span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">Publicada</span>',
            'draft' => '<span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">Rascunho</span>',
            'archived' => '<span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200">Arquivada</span>',
            default => '<span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200">Indefinido</span>'
        };

        if ($this->isPrivate()) {
            $statusBadge .= ' <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200 ml-1">Privado</span>';
        }

        return $statusBadge;
    }

    /**
     * Obter cor do status
     */
    public function getStatusColor(): string
    {
        return match ($this->status) {
            'published' => 'green',
            'draft' => 'yellow',
            'archived' => 'gray',
            default => 'gray'
        };
    }
}
