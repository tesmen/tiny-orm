<?php

namespace TinyORM;

class Tiny
{
    public static function getRepository($class)
    {
        return new Repository();
    }
}
