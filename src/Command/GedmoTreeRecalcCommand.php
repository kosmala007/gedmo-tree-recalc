<?php

declare(strict_types=1);

namespace DevPack\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class GedmoTreeRecalcCommand extends Command
{
    private $em;

    protected static $defaultName = 'gedmo:tree:recalc';

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Recalc Gedmo Tree (Doctrine Behavioral Extensions)')
            ->addArgument('className', InputArgument::OPTIONAL,
            'Class name of the entity to recalc (e.g. DeliciousPizza)')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $output->writeln('Success!!!');

        return 0;
    }
}
