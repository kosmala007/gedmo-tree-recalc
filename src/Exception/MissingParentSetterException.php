<?php

declare(strict_types=1);

namespace DevPack\GedmoTreeRecalc\Exception;

class MissingParentSetterException extends \Exception
{
    public function __construct(string $parentSetterName)
    {
        parent::__construct('You don\'t have "'.$parentSetterName.'()" method in your class');
    }
}
