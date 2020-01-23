<?php

declare(strict_types=1);

namespace DevPack\Command;

use DevPack\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
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
            ->addArgument('className', InputArgument::REQUIRED,
            'Class name of the entity to recalc (e.g. DeliciousPizza)')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $className = $input->getArgument('className');
        $className = 'App\\Entity\\'.$className;
        if (!class_exists($className)) {
            $io->error('Class "'.$className.'" doesn\'t exist!');

            throw new Exception\ClassNotExistException($className);
        }

        $this->meta = $this->em->getClassMetaData($className);
        $this->nestedTreeRepo = new NestedTreeRepository($this->em, $this->meta);
        $this->repo = $this->em->getRepository($className);

        $parentPropertyName = $this->getParentPropertyName();
        $parentGetterName = 'get'.ucfirst($parentPropertyName);
        if (!$this->meta->reflClass->hasMethod($parentGetterName)) {
            $io->error('You don\'t have "'.$parentGetterName.'()" method in your class');

            throw new Exception\MissingParentGetterException($parentGetterName);
        }
        $parentSetterName = 'set'.ucfirst($parentPropertyName);
        if (!$this->meta->reflClass->hasMethod($parentSetterName)) {
            $io->error('You don\'t have "'.$parentSetterName.'()" method in your class');

            throw new Exception\MissingParentSetterException($parentGetterName);
        }
        if (!$this->clearTreeProperties()) {
            $io->error('Your tree is incomplete');

            return 0;
        }

        return 0;
    }

    public function getParentPropertyName(): ?string
    {
        $result = null;
        foreach ($this->meta->reflClass->getProperties() as $propertyRef) {
            if ($this->isTreeProperty($propertyRef, 'TreeParent')) {
                $result = $propertyRef->getName();
            }
        }

        return $result;
    }

    public function isTreeProperty(
        \ReflectionProperty $propertyRef,
        string $elem
    ): bool {
        $result = false;
        if (false !== strpos($propertyRef->getDocComment(), '@Gedmo\\'.$elem)) {
            $result = true;
        }

        return $result;
    }

    public function clearTreeProperties(): bool
    {
        $sqlParts = [];
        foreach ($this->meta->reflClass->getProperties() as $propertyRef) {
            if ($this->isTreeProperty($propertyRef, 'TreeLevel')
                || $this->isTreeProperty($propertyRef, 'TreeLeft')
                || $this->isTreeProperty($propertyRef, 'TreeRight')
                || $this->isTreeProperty($propertyRef, 'TreeRoot')
            ) {
                $sqlParts[] = $this->meta->getColumnName($propertyRef->getName()).' = 0';
            }
        }
        if (!count($sqlParts)) {
            return false;
        }

        $conn = $this->em->getConnection();
        $stmt = $conn->prepare('
            UPDATE '.$this->meta->getTableName().'
            SET '.implode(', ', $sqlParts)
        );
        $stmt->execute();

        return true;
    }
}
