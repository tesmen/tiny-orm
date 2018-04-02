<?php
/**
 * Created by PhpStorm.
 * User: misantron
 * Date: 4/15/14
 * Time: 3:34 PM
 */

namespace TinyORM\Helpers;


class SimpleArrayHelper
{

    /**
     * @param array $array
     * @param string|int $field
     * @return array
     */
    public static function extractColumn(&$array, $field)
    {
        $values = [];
        foreach ($array as $val) {
            if (isset($val[$field])) {
                $values[] = $val[$field];
            }
        }

        return $values;
    }

    /**
     * Extract fields by names
     *
     * @param array $source one-dimensional associative array
     * @param $fields
     * @return array
     */
    public static function extractFields(array &$source, array $fields)
    {
        $result = [];
        foreach ($fields as $value) {
            if (isset($source[$value])) {
                $result[$value] = $source[$value];
            } else {
                $result[$value] = null;
            }
        }

        return $result;
    }

    /**
     * @param array $array
     * @param string $prefix
     * @param string $postfix
     * @param bool $preserveKeys
     * @return array
     */
    public static function construct(&$array, $prefix = '', $postfix = '', $preserveKeys = true)
    {
        $values = [];
        foreach ($array as $key => $val) {
            $values[$key] = $prefix . $val . $postfix;
        }

        return $preserveKeys
            ? $values
            : array_values($values);
    }

    /**
     * @param array $array
     * @param string $prefix
     * @return array
     */
    public static function extend(&$array, $prefix = '')
    {
        $values = [];
        foreach ($array as $val) {
            $values[$val] = $prefix . $val;
        }

        return $values;
    }

    /**
     * @param array $array
     * @param array $fields
     * @param int $sort
     */
    public static function sort(&$array, $fields = [], $sort = SORT_ASC)
    {
        usort(
            $array, function ($a, $b) use ($fields) {
            $val = 0;
            foreach ($fields as $field) {
                if ($val == 0) $val = strnatcmp($a[$field], $b[$field]);
            }

            return $val;
        }
        );
        if ($sort === SORT_DESC) $array = array_reverse($array, true);
    }

    /**
     * @param array $array
     * @param bool $preserveKeys
     * @return array
     */
    public static function filter(&$array, $preserveKeys = false)
    {
        $values = [];
        foreach ($array as $key => $val) {
            $val = trim($val);
            if (!empty($val)) {
                $values[$key] = $val;
            }
        }

        return $preserveKeys
            ? $values
            : array_values($values);
    }

    public static function replaceKeyColumn($array, $columnName)
    {
        $result = [];
        if (empty($array)) return $result;
        foreach ($array as $val) {
            if (array_key_exists($columnName, $val)) {
                $result[$val[$columnName]] = $val;
            }
        }

        return $result;
    }

    public static function closestHigher($array, $nr)
    {
        $higher = 0;
        sort($array);
        foreach ($array as $num) {
            if ($nr > $num) $re_arr['lower'] = $num;
            else if ($nr <= $num) {
                $higher = $num;
                break;
            }
        }
        if ($higher == 0) {
            $higher = $array[count($array) - 1];
        }

        return $higher;
    }

    public static function closestLower($array, $nr)
    {
        $higher = 0;
        $lower = 0;
        $higher = 0;
        sort($array);
        foreach ($array as $num) {
            if ($nr > $num) $lower = $num;
            else if ($nr <= $num) {
                $higher = $num;
                break;
            }
        }

        return $lower;
    }

    /**
     * Get value from array
     *
     * @param array|\ArrayAccess $array
     * @param string $index
     * @param mixed $default
     *
     * @return mixed
     */
    public static function get($array, $index, $default = null)
    {
        if (array_key_exists($index, $array)) {
            return $array[$index];
        }

        return $default;
    }

    /**
     * in: [['name' => 'Sasha', 'age' => 10], ['name' => 'Serega', 'age' => 12]]
     * getList(in, 'name', 'age')
     * out:
     * ['Sasha' => 10, 'Serega' => 12]
     *
     * @param array $source
     * @param string $key1
     * @param string $key2
     * @return array|bool
     */
    public static function getList(array $source, $key1, $key2)
    {
        $list = [];

        foreach ($source as $value) {
            if (!array_key_exists($key1, $value) || !array_key_exists($key2, $value)) {
                return false;
            }

            $list[$value[$key1]] = $value[$key2];
        }

        return $list;
    }

    /**
     * @param array $source
     * @param array $keys
     * @return array
     */
    public static function only(array $source, array $keys)
    {
        return array_intersect_key($source, array_flip($keys));
    }

    /**
     * @param array $source
     * @param array $keys
     * @return array
     */
    public static function exclude(array $source, array $keys)
    {
        return array_diff_key($source, array_flip($keys));
    }

    /**
     * Usage:
     *
     * $in [
     *      [ 'id' => 1, 'role' > 'admin'],
     *      [ 'id' => 2, 'role' => 'admin'],
     *      [ 'id' => 3, 'role' => 'user'],
     *      [ 'id' => 4, 'role' => 'user'],
     *  ]
     *
     * Arr::group($in, 'role')
     * $out
     *      [
     *          'admin' => [
     *                          ['id' => 1, 'role' => 'admin],
     *                          ['id' => 2, 'role' => 'admin],
     *                      ],
     *          'user' => [
     *                          ['id' => 3, 'role' => 'user'],
     *                          ['id' => 4, 'role' => 'user'],
     *                     ],
     *          ]
     *
     *
     * @param array $source
     * @param $key
     * @return array
     */
    public static function group(array $source, $key)
    {
        $grouped = [];

        foreach ($source as $elem) {
            $value = $elem[$key];

            if (!array_key_exists($value, $grouped)) {
                $grouped[$value] = [];
            }

            $grouped[$value][] = $elem;
        }

        return $grouped;
    }

    /**
     * @param array $source assoc
     * @param array $insert assoc
     * @return array
     */
    public static function prepend(array $source, array $insert)
    {
        $new = $insert;

        foreach ($source as $key => $value) {
            $new[$key] = $value;
        }

        return $new;
    }

    /**
     * @param array $hayStack
     * @param mixed $value
     * @param string $field
     * @return mixed
     */
    public static function pick($hayStack, $value, $field = 'id')
    {
        if (!is_array($hayStack)) {
            return false;
        }

        foreach ($hayStack as $item) {
            if (isset($item[$field]) && $item[$field] === $value) {
                return $item;
            }
        }

        return false;
    }
}
