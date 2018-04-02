<?php

namespace TinyORM\Exceptions;

class InvalidConfigException extends TinyORMException
{
    public function getName()
    {
        return 'Invalid Configuration';
    }
}
