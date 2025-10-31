<?php

declare(strict_types=1);

namespace YouzanApiDataBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use Symfony\Component\DependencyInjection\Attribute\When;

#[When(env: 'test')]
#[When(env: 'dev')]
abstract class AppFixtures extends Fixture
{
    protected Generator $faker;

    public function __construct()
    {
        $this->faker = Factory::create('zh_CN');
    }

    abstract public function load(ObjectManager $manager): void;

    protected function generateChineseMobileNumber(): string
    {
        $prefixes = ['130', '131', '132', '133', '134', '135', '136', '137', '138', '139',
            '150', '151', '152', '153', '155', '156', '157', '158', '159',
            '180', '181', '182', '183', '184', '185', '186', '187', '188', '189'];

        $prefix = $this->faker->randomElement($prefixes);
        assert(is_string($prefix));
        $suffix = (string) $this->faker->numerify('########');

        return $prefix . $suffix;
    }

    protected function generateYouzanOpenId(): string
    {
        return $this->faker->regexify('[a-zA-Z0-9_-]{20,32}');
    }

    protected function generateEncryptedData(string $original): string
    {
        return base64_encode($original);
    }

    protected function generateUnionId(): string
    {
        return $this->faker->regexify('[a-zA-Z0-9_-]{29}');
    }
}
