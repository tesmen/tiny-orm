<?php

namespace TinyORM\Exceptions;

class InvalidRouteException extends TinyORMException
{
    public function getName()
    {
        return 'Invalid Route';
    }
}
