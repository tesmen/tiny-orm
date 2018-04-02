<?php

namespace TinyORM\Exceptions;

class InvalidValueException extends \UnexpectedValueException
{
    public function getName()
    {
        return 'Invalid Return Value';
    }
}
