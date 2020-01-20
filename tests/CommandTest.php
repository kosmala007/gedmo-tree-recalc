<?php

declare(strict_types=1);

namespace DevPack\Tests;

use DevPack\Command\GedmoTreeRecalcCommand;
use Symfony\Component\Console\Application;
use PHPUnit\Framework\TestCase;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Console\Tester\CommandTester;
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

class CommandTest extends TestCase
{
    private $em;

    protected function setUp(): void
    {
        $isDevMode = true;
        $config = Setup::createAnnotationMetadataConfiguration([__DIR__.'/src'], true);
        $conn = [
            'driver' => 'pdo_sqlite',
            'path' => __DIR__ . '/db.sqlite',
        ];
        $this->em = EntityManager::create($conn, $config);
    }

    public function testExecute()
    {
        $command = new GedmoTreeRecalcCommand($this->em);

        $commandTester = new CommandTester($command);
        $commandTester->execute([]);
        $output = $commandTester->getDisplay();

        $this->assertStringContainsString('Success', $output);
    }

    protected function tearDown(): void
    {
    }
}
