<?php namespace Waka\Agg\Models;

use Model;

class Settings extends Model
{
    public $implement = ['System.Behaviors.SettingsModel'];

    // A unique code
    public $settingsCode = 'waka_agg_settings';

    // Reference to field configuration
    public $settingsFields = 'fields.yaml';

    // public function listStartImports()
    // {
    //     $lists = \Config::get('waka.wconfig::start_data');
    //     $resultArray = [];
    //     foreach ($lists as $key => $list) {
    //         $truncate = $list['truncate'] ?? 'pas de truncate';
    //         $resultArray[$key] = $key . ' : ' . $truncate;
    //     }
    //     return $resultArray;
    // }

}
