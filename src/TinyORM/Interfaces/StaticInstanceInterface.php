<?php

namespace TinyORM\Interfaces;

interface StaticInstanceInterface
{
    public static function instance($refresh = false);
}
