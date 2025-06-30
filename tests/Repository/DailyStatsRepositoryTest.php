<?php

namespace YouzanApiDataBundle\Tests\Repository;

use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;
use YouzanApiDataBundle\Repository\DailyStatsRepository;

class DailyStatsRepositoryTest extends TestCase
{
    public function testRepositoryIsCorrectType(): void
    {
        $registry = $this->createMock(ManagerRegistry::class);
        $repository = new DailyStatsRepository($registry);
        
        $this->assertInstanceOf(DailyStatsRepository::class, $repository);
    }
}