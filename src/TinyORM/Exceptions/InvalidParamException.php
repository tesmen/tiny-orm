<?php

namespace TinyORM\Exceptions;

class InvalidParamException extends \BadMethodCallException
{
    public function getName()
    {
        return 'Invalid Parameter';
    }
}
