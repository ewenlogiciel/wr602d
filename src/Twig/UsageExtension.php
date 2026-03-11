<?php

namespace App\Twig;

use App\Entity\User;
use App\Repository\GenerationRepository;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class UsageExtension extends AbstractExtension
{
    public function __construct(private GenerationRepository $generationRepository) {}

    public function getFunctions(): array
    {
        return [
            new TwigFunction('daily_usage', [$this, 'getDailyUsage']),
        ];
    }

    public function getDailyUsage(User $user): array
    {
        $limit = $user->getPlan()?->getUsageLimit();

        if ($limit === null) {
            return ['unlimited' => true, 'used' => 0, 'limit' => null, 'percentage' => 0];
        }

        $today    = new \DateTimeImmutable('today');
        $tomorrow = $today->modify('+1 day');
        $used     = $this->generationRepository->countByUserOnDate($user, $today, $tomorrow);

        $percentage = $limit > 0 ? (int) min(100, round($used / $limit * 100)) : 100;

        return [
            'unlimited'  => false,
            'used'       => $used,
            'limit'      => $limit,
            'percentage' => $percentage,
        ];
    }
}
