<?php

declare(strict_types=1);

namespace DevPack\Tests;

use DevPack\Command\GedmoTreeRecalcCommand;
use DevPack\Exception;
use DevPack\Tests\Factory\EntityManagerFactory;
use DevPack\Tests\Fixtures\AppFixture;
use DevPack\Tests\Service\DatabaseService;
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
        $this->em = EntityManagerFactory::create();
        DatabaseService::databaseUp($this->em);
        AppFixture::load($this->em);

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

    public function testInvalidCategory()
    {
        $this->expectException(Exception\MissingParentGetterException::class);

        $this->commandTester->execute(['className' => 'InvalidCategory']);
    }

    protected function tearDown(): void
    {
        $this->em->getConnection()->close();
    }
}
