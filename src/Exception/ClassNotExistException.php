<?php

declare(strict_types=1);

namespace DevPack\Exception;

class ClassNotExistException extends \Exception
{
    public function __construct(string $className)
    {
        parent::__construct('Class: "'.$className.'" not exist!');
    }
}
