<?php

namespace TinyORM\Exceptions;

class InvalidCallException extends \BadMethodCallException
{
    public function getName()
    {
        return 'Invalid Call';
    }
}
