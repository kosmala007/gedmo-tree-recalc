<?php

declare(strict_types=1);

namespace DevPack\Tests;

use DevPack\Command\GedmoTreeRecalcCommand;
use DevPack\Exception;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Tester\CommandTester;

class CommandTest extends TestCase
{
    private $em;
    private $command;
    private $commandTester;

    protected function setUp(): void
    {
        $config = Setup::createAnnotationMetadataConfiguration([__DIR__.'/src'], true);
        $conn = [
            'driver' => 'pdo_sqlite',
            'path' => __DIR__.'/db.sqlite',
        ];

        $this->em = EntityManager::create($conn, $config);
        $this->command = new GedmoTreeRecalcCommand($this->em);
        $this->commandTester = new CommandTester($this->command);
    }

    public function testIsArgumentNotPassed()
    {
        $this->expectException(RuntimeException::class);

        $this->commandTester->execute([]);
    }

    public function testIfEntityNotExist()
    {
        $this->expectException(Exception\ClassNotExistException::class);

        $this->commandTester->execute(['className' => 'Tag']);
    }

    protected function tearDown(): void
    {
        $this->em->getConnection()->close();
    }
}
