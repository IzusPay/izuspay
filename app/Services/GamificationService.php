<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;

class GamificationService
{
    public static function getGamificationData($userId = null)
    {
        $user = Auth::user();

        if (! $user || ! $user->association_id) {
            return null;
        }

        $associationId = $user->association_id;

        // Calcular métricas
        $totalRevenue = Sale::where('association_id', $associationId)->where('status', 'paid')->sum('total_price');
        $totalSales = Sale::where('association_id', $associationId)->where('status', 'paid')->count();
        $totalMembers = User::comPerfil('Membro')->where('association_id', $associationId)->count();

        // Detectar gênero
        $userGender = self::detectGender($user->name);

        // Definir níveis baseado no gênero
        $levels = self::getLevelsByGender($userGender);

        // Calcular nível atual
        $currentLevelInfo = null;
        $currentLevel = 1;
        foreach ($levels as $level => $info) {
            if ($totalRevenue >= $info['min']) {
                $currentLevelInfo = $info;
                $currentLevelInfo['level'] = $level;
                $currentLevel = $level;
            } else {
                break;
            }
        }

        // Calcular progresso para próximo nível
        $nextLevel = $currentLevel + 1;
        $nextLevelMin = $levels[$nextLevel]['min'] ?? $currentLevelInfo['max'];
        $currentLevelMin = $currentLevelInfo['min'];

        $range = $nextLevelMin - $currentLevelMin;
        $progress = $totalRevenue - $currentLevelMin;
        $progressPercentage = ($range > 0) ? min(100, ($progress / $range) * 100) : 100;

        return [
            'levelName' => $currentLevelInfo['name'],
            'levelBadge' => $currentLevelInfo['badge'],
            'levelColor' => $currentLevelInfo['color'],
            'levelDescription' => $currentLevelInfo['description'],
            'currentLevel' => $currentLevel,
            'currentRevenue' => $totalRevenue,
            'nextLevelTarget' => $nextLevelMin,
            'progressPercentage' => $progressPercentage,
            'remainingToNext' => max(0, $nextLevelMin - $totalRevenue),
            'rewards' => $currentLevelInfo['rewards'],
            'nextLevelRewards' => $levels[$nextLevel]['rewards'] ?? [],
            'nextLevelName' => $levels[$nextLevel]['name'] ?? 'Máximo',
            'allLevels' => $levels,
            'achievements' => self::calculateAchievements($totalRevenue, $totalSales, $totalMembers),
            'milestones' => self::calculateMilestones($totalRevenue, $totalSales, $totalMembers),
        ];
    }

    private static function getLevelsByGender($userGender)
    {
        if ($userGender === 'male') {
            return [
                1 => ['min' => 0, 'max' => 10000, 'name' => 'Aprendiz do Jogo', 'badge' => 'target', 'color' => 'green', 'rewards' => ['Dashboard básico'], 'description' => 'Primeiros passos na conquista.'],
                2 => ['min' => 10000, 'max' => 100000, 'name' => 'Encantador', 'badge' => 'rose', 'color' => 'amber', 'rewards' => ['Relatórios mensais'], 'description' => 'Começando a ser notado.'],
                3 => ['min' => 100000, 'max' => 500000, 'name' => 'Conquistador', 'badge' => 'heart', 'color' => 'gray', 'rewards' => ['Analytics avançado'], 'description' => 'Dominando a arte da atração.'],
                4 => ['min' => 500000, 'max' => 1000000, 'name' => 'Sedutor Implacável', 'badge' => 'fire', 'color' => 'yellow', 'rewards' => ['API personalizada'], 'description' => 'Irresistível e influente.'],
                5 => ['min' => 1000000, 'max' => 5000000, 'name' => 'Mestre da Paixão', 'badge' => 'crown', 'color' => 'blue', 'rewards' => ['Consultoria exclusiva'], 'description' => 'Elite da sedução.'],
                6 => ['min' => 5000000, 'max' => PHP_INT_MAX, 'name' => 'Lenda da Sedução', 'badge' => 'diamond', 'color' => 'purple', 'rewards' => ['Parceria estratégica'], 'description' => 'Top do topo.'],
            ];
        } elseif ($userGender === 'female') {
            return [
                1 => ['min' => 0, 'max' => 10000, 'name' => 'Aprendiz do Charme', 'badge' => 'sparkles', 'color' => 'green', 'rewards' => ['Dashboard básico'], 'description' => 'Primeiros passos no charme.'],
                2 => ['min' => 10000, 'max' => 100000, 'name' => 'Encantadora', 'badge' => 'flower', 'color' => 'amber', 'rewards' => ['Relatórios mensais'], 'description' => 'Começando a brilhar.'],
                3 => ['min' => 100000, 'max' => 500000, 'name' => 'Diva Irresistível', 'badge' => 'dancer', 'color' => 'gray', 'rewards' => ['Analytics avançado'], 'description' => 'Atração máxima.'],
                4 => ['min' => 500000, 'max' => 1000000, 'name' => 'Deusa da Sedução', 'badge' => 'fire', 'color' => 'yellow', 'rewards' => ['API personalizada'], 'description' => 'Intensidade e poder.'],
                5 => ['min' => 1000000, 'max' => 5000000, 'name' => 'Rainha da Paixão', 'badge' => 'crown', 'color' => 'blue', 'rewards' => ['Consultoria exclusiva'], 'description' => 'Status lendário.'],
                6 => ['min' => 5000000, 'max' => PHP_INT_MAX, 'name' => 'Lenda do Desejo', 'badge' => 'diamond', 'color' => 'purple', 'rewards' => ['Parceria estratégica'], 'description' => 'Top do topo.'],
            ];
        } else {
            return [
                1 => ['min' => 0, 'max' => 10000, 'name' => 'Iniciante', 'badge' => 'seedling', 'color' => 'green', 'rewards' => ['Dashboard básico'], 'description' => 'Começando a jornada.'],
                2 => ['min' => 10000, 'max' => 100000, 'name' => 'Intermediário', 'badge' => 'medal', 'color' => 'amber', 'rewards' => ['Relatórios mensais'], 'description' => 'Subindo de nível.'],
                3 => ['min' => 100000, 'max' => 500000, 'name' => 'Avançado', 'badge' => 'star', 'color' => 'gray', 'rewards' => ['Analytics avançado'], 'description' => 'Crescimento consistente.'],
                4 => ['min' => 500000, 'max' => 1000000, 'name' => 'Expert', 'badge' => 'trophy', 'color' => 'yellow', 'rewards' => ['API personalizada'], 'description' => 'Excelência reconhecida.'],
                5 => ['min' => 1000000, 'max' => 5000000, 'name' => 'Master', 'badge' => 'platinum-trophy', 'color' => 'blue', 'rewards' => ['Consultoria exclusiva'], 'description' => 'Elite do mercado.'],
                6 => ['min' => 5000000, 'max' => PHP_INT_MAX, 'name' => 'Lendário', 'badge' => 'diamond', 'color' => 'purple', 'rewards' => ['Parceria estratégica'], 'description' => 'Top do topo.'],
            ];
        }
    }

    private static function calculateAchievements($revenue, $sales, $members)
    {
        $achievements = [];

        if ($revenue >= 10000) {
            $achievements[] = ['name' => 'Primeira Receita', 'icon' => 'dollar-sign', 'unlocked' => true];
        }
        if ($revenue >= 100000) {
            $achievements[] = ['name' => 'Seis Dígitos', 'icon' => 'trending-up', 'unlocked' => true];
        }
        if ($revenue >= 1000000) {
            $achievements[] = ['name' => 'Milionário', 'icon' => 'crown', 'unlocked' => true];
        }

        if ($sales >= 10) {
            $achievements[] = ['name' => 'Vendedor Iniciante', 'icon' => 'shopping-cart', 'unlocked' => true];
        }
        if ($sales >= 100) {
            $achievements[] = ['name' => 'Vendedor Expert', 'icon' => 'award', 'unlocked' => true];
        }
        if ($sales >= 1000) {
            $achievements[] = ['name' => 'Máquina de Vendas', 'icon' => 'zap', 'unlocked' => true];
        }

        if ($members >= 50) {
            $achievements[] = ['name' => 'Comunidade Ativa', 'icon' => 'users', 'unlocked' => true];
        }
        if ($members >= 500) {
            $achievements[] = ['name' => 'Grande Comunidade', 'icon' => 'globe', 'unlocked' => true];
        }

        return $achievements;
    }

    private static function calculateMilestones($revenue, $sales, $members)
    {
        $milestones = [];

        $revenueMilestones = [25000, 50000, 250000, 750000, 2500000, 10000000];
        foreach ($revenueMilestones as $milestone) {
            if ($revenue < $milestone) {
                $milestones[] = [
                    'type' => 'revenue',
                    'target' => $milestone,
                    'current' => $revenue,
                    'progress' => ($revenue / $milestone) * 100,
                    'description' => 'Receita de R$ '.number_format($milestone, 0, ',', '.'),
                ];
                break;
            }
        }

        return $milestones;
    }

    private static function detectGender($fullName)
    {
        $firstName = explode(' ', trim($fullName))[0];

        $cacheKey = 'genderize:' . strtolower($firstName);
        return Cache::remember($cacheKey, 60 * 60 * 24 * 7, function () use ($firstName) {
            try {
                $response = Http::timeout(5)->retry(2, 500)->get('https://api.genderize.io', [
                    'name' => $firstName,
                ]);
                if ($response->ok()) {
                    $g = $response->json()['gender'] ?? null;
                    return $g ?: 'unknown';
                }
            } catch (\Throwable $e) {
            }
            return 'unknown';
        });
    }
}
