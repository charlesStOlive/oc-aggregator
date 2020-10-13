<?php namespace Waka\Agg\Models;

use Carbon\Carbon;
use Model;
use Waka\Utils\Models\DataSource;

/**
 * week Model
 */
class AgWeek extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'waka_agg_weeks';

    /**
     * @var array Guarded fields
     */
    protected $guarded = [];

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
        'start_at'
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
            $ds = new DataSource($this->data_source_id, 'id');
            $ds_name = $ds->name;
            $this->name = $ds_name . ' ' . $this->ag_year . ' Semaine : ' . $this->ag_week;
        }
        Carbon::setWeekStartsAt(Carbon::MONDAY);
        Carbon::setWeekEndsAt(Carbon::SUNDAY);
        $dt = \Carbon\Carbon::createFromDate($this->ag_year, 1, 1);
        $dte = \Carbon\Carbon::createFromDate($this->ag_year, 1, 1);
        $dt = $dt->startOfWeek();
        $this->start_at = $dt->addWeeks($this->ag_week - 1);
        $dte = $dte->endOfWeek();
        $this->end_at = $dte->addWeeks($this->ag_week - 1);
    }

    public function afterSave()
    {
        //trace(_log("after save");
        \Event::fire('agg.update', [$this]);
    }
}
