<?php namespace Waka\Agg\Classes;

use Waka\Utils\Classes\DataSource;

class AggQueryBase
{
    public static function CreateAutoDbRaw($calculs) {
        $dbr = [];
        foreach($calculs as $calcul) {
            if($calcul['type'] == 'count') {
                $r = 'COUNT(*) as count';
                array_push($dbr, $r);
            }
            if($calcul['type'] == 'sum') {
                $r = 'SUM('.$calcul['column'].') as '.$calcul['column'];
                array_push($dbr, $r);
            }
            if($calcul['type'] == 'avg') {
                $r = 'AVG('.$calcul['column'].') as '.$calcul['column'];
                array_push($dbr, $r);
            }
        }
        return implode(",", $dbr);

    }
}
