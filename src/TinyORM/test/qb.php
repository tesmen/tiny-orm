<?php
require '../../../src/TinyORM/Base/BaseQueryBuilder.php';
require '../../../src/TinyORM/Base/QueryBuilder.php';

$qb = new \TinyORM\Base\QueryBuilder('users');

$qb
    ->bindParam('userId',1)
    ->addConditionNew('(t.id = :userId AND u.type = :type', [11722183, "VIP"])
    ->join('user_stat us ON us.user_id = t.id')
    ->leftJoin('orders o ON o.user_id = t.id');

echo $qb->getQuery() . PHP_EOL;

