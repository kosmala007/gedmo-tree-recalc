<?php

declare(strict_types=1);

namespace DevPack\GedmoTreeRecalc\Tests\Fixtures;

use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;

class AppFixture
{
    public static function load(EntityManagerInterface $em)
    {
        $categories = [];
        for ($i = 0; $i < 3; ++$i) {
            $categories[$i] = new Category('Category '.($i + 1));
            $em->persist($categories[$i]);
        }

        for ($i = 0; $i < 2; ++$i) {
            $category = new Category('Category 1.'.($i + 1));
            $category->setParent($categories[0]);
            $em->persist($category);
        }

        $em->flush();
    }
}
