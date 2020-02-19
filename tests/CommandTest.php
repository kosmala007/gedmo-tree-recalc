<?php

declare(strict_types=1);

namespace DevPack\GedmoTreeRecalc\Tests;

use DevPack\GedmoTreeRecalc\Command\GedmoTreeRecalcCommand;
use DevPack\GedmoTreeRecalc\Exception;
use DevPack\GedmoTreeRecalc\Tests\Factory\EntityManagerFactory;
use DevPack\GedmoTreeRecalc\Tests\Fixtures\AppFixture;
use DevPack\GedmoTreeRecalc\Tests\Service\DatabaseService;
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

    public function testExecute()
    {
        $this->commandTester->execute(['className' => 'Category']);
        $output = $this->commandTester->getDisplay();

        $this->assertStringContainsString('Tree is verified', $output);
    }

    protected function tearDown(): void
    {
        $this->em->getConnection()->close();
    }
}
