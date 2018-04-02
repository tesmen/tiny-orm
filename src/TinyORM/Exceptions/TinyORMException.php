<?php

namespace TinyORM\Exceptions;

class TinyORMException extends \Exception
{
    public function getName()
    {
        return 'TinyORMException';
    }
}
