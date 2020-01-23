<?php

declare(strict_types=1);

namespace DevPack\Tests;

use App\Entity\Category;
use DevPack\Command\GedmoTreeRecalcCommand;
use DevPack\Exception;
use DevPack\Tests\Factory\EntityManagerFactory;
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

        $this->command = new GedmoTreeRecalcCommand($this->em);
        $this->commandTester = new CommandTester($this->command);

        $cat = new Category();
        $cat->setName('Category 1');
        $this->em->persist($cat);
        $this->em->flush();
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

    public function test()
    {
        $this->commandTester->execute(['className' => 'Category']);
    }

    protected function tearDown(): void
    {
        $this->em->getConnection()->close();
    }
}
