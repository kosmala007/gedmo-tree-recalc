<?php

declare(strict_types=1);

namespace DevPack\Tests;

use DevPack\Exception;
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
        $config = Setup::createAnnotationMetadataConfiguration([__DIR__.'/src'], true);
        $conn = [
            'driver' => 'pdo_sqlite',
            'path' => __DIR__ . '/db.sqlite',
        ];
        $this->em = EntityManager::create($conn, $config);
    }

    public function testIfEntityNotExist()
    {
        $command = new GedmoTreeRecalcCommand($this->em);
        $commandTester = new CommandTester($command);

        $this->expectException(Exception\ClassNotExistException::class);

        $commandTester->execute(['className' => 'Tag']);
    }

    protected function tearDown(): void
    {
    }
}
