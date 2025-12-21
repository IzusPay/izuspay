<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'association_id',
        'name',
        'email',
        'password',
        'tipo',
        'telefone',
        'documento',
        'status',
        'api_token',
        'avatar',
        'data_nascimento',
        'endereco',
        'cidade',
        'estado',
        'cep',
        'observacoes',
        'ultimo_acesso',
        'email_verified_at'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'data_nascimento' => 'date',
        'ultimo_acesso' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Relacionamento com associação
     */
    public function association()
    {
        return $this->belongsTo(Association::class);
    }

    public function documentations(): HasMany
    {
        return $this->hasMany(Documentation::class);
    }

    /**
     * Relacionamento com assinaturas
     */
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Relacionamento com vendas
     */
    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    /**
     * Relacionamento com perfis do usuário
     */
    public function userPerfis()
    {
        return $this->hasMany(UserPerfilModel::class, 'user_id');
    }

    /**
     * Relacionamento com perfis através da tabela pivot
     */
    public function perfis()
    {
        return $this->belongsToMany(PerfilModel::class, 'user_perfis', 'user_id', 'perfil_id')
            ->withPivot('is_atual', 'status')
            ->withTimestamps();
    }

    /**
     * Obtém o perfil atual do usuário
     */
    public function perfilAtual()
    {
        $userPerfil = $this->userPerfis()->where('is_atual', 1)->where('status', 1)->with('perfil')->first();
        return $userPerfil ? $userPerfil->perfil : null;
    }

    /**
     * Obtém todos os perfis ativos do usuário
     */
    public function perfisAtivos()
    {
        return $this->userPerfis()->where('status', 1)->with('perfil')->get()->pluck('perfil');
    }

    /**
     * Define um perfil como atual
     */
    public function setPerfilAtual($perfilId)
    {
        // Remove o perfil atual
        $this->userPerfis()->update(['is_atual' => 0]);

        // Define o novo perfil como atual
        $userPerfil = $this->userPerfis()->where('perfil_id', $perfilId)->where('status', 1)->first();
        if ($userPerfil) {
            $userPerfil->update(['is_atual' => 1]);
            return true;
        }

        return false;
    }

    /**
     * Adiciona um perfil ao usuário
     */
    public function adicionarPerfil($perfilId, $isAtual = false, $status = 1)
    {
        // Verifica se já existe
        $exists = $this->userPerfis()->where('perfil_id', $perfilId)->exists();

        if (!$exists) {
            // Se for definir como atual, remove outros atuais
            if ($isAtual) {
                $this->userPerfis()->update(['is_atual' => 0]);
            }

            return $this->userPerfis()->create([
                'perfil_id' => $perfilId,
                'is_atual' => $isAtual ? 1 : 0,
                'status' => $status
            ]);
        }

        return false;
    }

    public function creatorProfile()
    {
        return $this->hasOne(CreatorProfile::class);
    }

    /**
     * Remove um perfil do usuário
     */
    public function removerPerfil($perfilId)
    {
        return $this->userPerfis()->where('perfil_id', $perfilId)->delete();
    }

    /**
     * Verifica se o usuário tem um perfil específico
     */
    public function temPerfil($perfilName)
    {
        return $this->perfisAtivos()->contains('name', $perfilName);
    }

    /**
     * Verifica se é administrador
     */
    public function isAdmin()
    {
        return $this->temPerfil('Administrador');
    }

    /**
     * Verifica se é cliente
     */
    public function isCliente()
    {
        return $this->temPerfil('Associacao');
    }

    /**
     * Verifica se é dono de associação
     */
    public function isDonoAssociacao()
    {
        return $this->temPerfil('Associacao');
    }

    // app/Models/User.php
    public function dashboardSetting()
    {
        return $this->hasOne(DashboardSetting::class);
    }


    /**
     * Verifica se é membro
     */
    public function isMembro()
    {
        return $this->temPerfil('Membro');
    }

    /**
     * Verifica se é moderador
     */
    public function isModerador()
    {
        return $this->temPerfil('Moderador');
    }

    /**
     * Registra o último acesso
     */
    public function registrarAcesso()
    {
        $this->update(['ultimo_acesso' => now()]);
    }

    /**
     * Scopes
     */
    public function scopeAtivos($query)
    {
        return $query->where('status', 'ativo');
    }

    public function scopeInativos($query)
    {
        return $query->where('status', 'inativo');
    }

    public function scopePendentes($query)
    {
        return $query->where('status', 'pendente');
    }

    public function scopePorAssociacao($query, $associationId)
    {
        return $query->where('association_id', $associationId);
    }

    public function scopeComPerfil($query, $perfilName)
    {
        // A lógica corrigida para evitar o erro 'pivot'
        return $query->whereHas('perfis', function($q) use ($perfilName) {
            $q->where('name', $perfilName);
        })->whereHas('userPerfis', function($q) {
            $q->where('status', 1);
        });
    }

    public function scopeBuscar($query, $termo)
    {
        return $query->where(function($q) use ($termo) {
            $q->where('name', 'like', "%{$termo}%")
              ->orWhere('email', 'like', "%{$termo}%")
              ->orWhere('documento', 'like', "%{$termo}%");
        });
    }

    /**
     * Accessors
     */
    public function getDocumentoFormatadoAttribute()
    {
        if (!$this->documento) return '';

        $documento = preg_replace('/\D/', '', $this->documento);

        if (strlen($documento) == 11) {
            return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $documento);
        } elseif (strlen($documento) == 14) {
            return preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $documento);
        }

        return $this->documento;
    }

    public function getTelefoneFormatadoAttribute()
    {
        if (!$this->telefone) return '';

        $telefone = preg_replace('/\D/', '', $this->telefone);

        if (strlen($telefone) == 11) {
            return preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $telefone);
        } elseif (strlen($telefone) == 10) {
            return preg_replace('/(\d{2})(\d{4})(\d{4})/', '($1) $2-$3', $telefone);
        }

        return $this->telefone;
    }

    public function getCepFormatadoAttribute()
    {
        if (!$this->cep) return '';

        $cep = preg_replace('/\D/', '', $this->cep);

        if (strlen($cep) == 8) {
            return preg_replace('/(\d{5})(\d{3})/', '$1-$2', $cep);
        }

        return $this->cep;
    }

    public function getNomeAbreviadoAttribute()
    {
        $nomes = explode(' ', $this->name);
        if (count($nomes) >= 2) {
            return $nomes[0] . ' ' . end($nomes);
        }
        return $this->name;
    }

    public function getIniciaisAttribute()
    {
        $nomes = explode(' ', $this->name);
        $iniciais = '';
        foreach (array_slice($nomes, 0, 2) as $nome) {
            $iniciais .= strtoupper(substr($nome, 0, 1));
        }
        return $iniciais;
    }

    public function getAvatarUrlAttribute()
    {
        if ($this->avatar) {
            return asset('storage/avatars/' . $this->avatar);
        }
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=6366f1&color=ffffff&size=200';
    }

    public function getPerfilAtualNomeAttribute()
    {
        $perfil = $this->perfilAtual();
        return $perfil ? $perfil->name : 'Sem perfil';
    }

    public function getBadgePerfilAttribute()
    {
        $perfil = $this->perfilAtual();
        if (!$perfil) return '<span class="badge bg-secondary">Sem perfil</span>';

        $colors = [
            'Administrador' => 'danger',
            'Cliente' => 'primary',
            'Dono de Associação' => 'success',
            'Membro' => 'info',
            'Moderador' => 'warning'
        ];

        $color = $colors[$perfil->name] ?? 'secondary';
        return '<span class="badge bg-' . $color . '">' . $perfil->name . '</span>';
    }

    public function getBadgeStatusAttribute()
    {
        $colors = [
            'ativo' => 'success',
            'inativo' => 'danger',
            'pendente' => 'warning'
        ];

        $color = $colors[$this->status] ?? 'secondary';
        return '<span class="badge bg-' . $color . '">' . ucfirst($this->status) . '</span>';
    }

    public function apiTokens()
{
    return $this->hasMany(ApiToken::class);
}
}
