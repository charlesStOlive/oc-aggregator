<?php namespace Waka\Agg\Classes;

class AggQueryBase
{
    public static function CreateAutoDbRaw($column)
    {
        $dbr = [];
        $r = 'COUNT(*) as count';
        array_push($dbr, $r);
        $r = 'SUM(' . $column['column'] . ') as ' . $column['column'];
        array_push($dbr, $r);
        return implode(",", $dbr);

    }
}
