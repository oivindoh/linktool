<?php
class StringEscape
{
    function __get($value)
    {
        return mysql_real_escape_string($value);
    }
}
$str = new StringEscape;
?>