<?php

declare(strict_types=1);

namespace DevPack\GedmoTreeRecalc\Exception;

class MissingParentGetterException extends \Exception
{
    public function __construct(string $parentGetterName)
    {
        parent::__construct('You don\'t have "'.$parentGetterName.'()" method in your class');
    }
}
