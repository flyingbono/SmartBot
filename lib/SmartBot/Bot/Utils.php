<?php
namespace SmartBot\Bot;

class Utils {
    public static function camelize($value, $lcfirst = true)
    {
        return strtr(ucwords(strtr($value, array('_' => ' ','-' => ' ', '.' => '_ ', '\\' => '_ '))), array(' ' => ''));
    }
}