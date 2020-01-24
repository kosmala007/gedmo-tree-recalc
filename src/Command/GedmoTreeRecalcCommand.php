<?php

declare(strict_types=1);

namespace DevPack\Command;

use DevPack\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Exception\InvalidMappingException;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use Gedmo\Tree\TreeListener;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
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
        $this->treeListener = $this->getTreeListener();

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

        $this->clearTreeProperties();

        $entities = $this->repo->findAll();
        $progressBar = new ProgressBar($output, count($entities));
        $progressBar->setFormat('debug');
        foreach ($entities as $entity) {
            $parent = $entity->{$parentGetterName}();

            $entity->{$parentSetterName}(null);
            $this->setNewParent($entity, null);
            $this->em->flush();

            $entity->{$parentSetterName}($parent);
            $this->setNewParent($entity, $parent);
            $this->em->flush();

            $progressBar->advance();
        }
        $progressBar->finish();
        $io->text('');
        $io->success('Modified entities: '.count($entities));

        $this->nestedTreeRepo->recover();
        $result = $this->nestedTreeRepo->verify();
        $this->em->flush();
        if (is_array($result)) {
            $io->error('Errors in tree');
            foreach ($result as $row) {
                $io->text((string) $row);
            }
        } else {
            $io->success('Tree is verified');
        }

        return 1;
    }

    public function setNewParent($node, $newParent)
    {
        $this->treeListener
            ->getStrategy($this->em, $this->meta->name)
            ->updateNode($this->em, $node, $newParent)
        ;

        return $node;
    }

    public function getTreeListener(): TreeListener
    {
        $treeListener = null;
        foreach ($this->em->getEventManager()->getListeners() as $listeners) {
            foreach ($listeners as $listener) {
                if ($listener instanceof TreeListener) {
                    $treeListener = $listener;
                    break;
                }
            }
            if ($treeListener) {
                break;
            }
        }
        if (null === $treeListener) {
            throw new InvalidMappingException(
                'Tree listener was not found on your entity manager,
                it must be hooked into the event manager'
            );
        }

        return $treeListener;
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

    public function clearTreeProperties()
    {
        $qb = $this->em->createQueryBuilder();
        $qb->update($this->meta->name, 'u');

        foreach ($this->meta->reflClass->getProperties() as $propertyRef) {
            if ($this->isTreeProperty($propertyRef, 'TreeLevel')
                || $this->isTreeProperty($propertyRef, 'TreeLeft')
                || $this->isTreeProperty($propertyRef, 'TreeRight')
                || $this->isTreeProperty($propertyRef, 'TreeRoot')
            ) {
                $prop = $this->meta->getColumnName($propertyRef->getName());
                $qb->set('u.'.$prop, 0);
            }
        }

        $qb->getQuery()->execute();
        $this->em->clear();
    }
}
