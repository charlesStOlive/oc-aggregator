<?php namespace Waka\Agg\Models;

use Model;
use Waka\Utils\Models\DataSource;
use \Carbon\Carbon;

/**
 * month Model
 */
class AgMonth extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'waka_agg_months';

    /**
     * @var array Guarded fields
     */
    protected $guarded = [''];

    /**
     * @var array Fillable fields
     */
    protected $fillable = ['*'];

    /**
     * @var array Validation rules for attributes
     */
    public $rules = [];

    /**
     * @var array Attributes to be cast to native types
     */
    protected $casts = [];

    /**
     * @var array Attributes to be cast to JSON
     */
    protected $jsonable = [];

    /**
     * @var array Attributes to be appended to the API representation of the model (ex. toArray())
     */
    protected $appends = [];

    /**
     * @var array Attributes to be removed from the API representation of the model (ex. toArray())
     */
    protected $hidden = [];

    /**
     * @var array Attributes to be cast to Argon (Carbon) instances
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'date_at',
        'end_at',
    ];

    /**
     * @var array Relations
     */
    public $hasOne = [];
    public $hasMany = [];
    public $belongsTo = [
        'data_source' => 'Waka\Utils\Models\DataSource',
    ];
    public $belongsToMany = [];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [
        'periodeables' => ['Waka\Agg\Models\Aggregable', 'name' => 'periodeable'],
    ];
    public $attachOne = [];
    public $attachMany = [];

    public function beforeSave()
    {
        if (!$this->name) {
            $ds_name = DataSource::find($this->data_source_id)->name;
            $this->name = $ds_name . ' ' . $this->ag_year . ' mois : ' . $this->ag_month;
        }

        $dt = \Carbon\Carbon::createFromDate($this->ag_year, $this->ag_month, 1);
        $dte = \Carbon\Carbon::createFromDate($this->ag_year, $this->ag_month, 1);
        $this->start_at = $dt;
        $this->end_at = $dte->endOfMonth();

    }

    public function afterSave()
    {
        \Event::fire('agg.update', [$this]);
    }
}
