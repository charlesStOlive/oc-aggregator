<?php namespace Waka\Agg\Models;

use Model;

/**
 * Aggregable Model
 */
class Aggregable extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'waka_agg_aggregables';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = [];

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
    protected $jsonable = ['datas'];

    /**
     * @var array Attributes to be appended to the API representation of the model (ex. toArray())
     */
    protected $appends = ['shortName', 'firstName'];

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
        'start_at',
        'end_at',
    ];

    /**
     * @var array Relations
     */
    public $hasOne = [];
    public $hasMany = [];
    public $belongsTo = [];
    public $belongsToMany = [];
    public $morphTo = [
        'aggregable' => [],
        'periodeable' => [],
    ];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [];
    public $attachMany = [];

    /**
     *
     */
    public function getNameFromAttribute()
    {
        return $this->periodeable->name;
    }

    public function getShortNameAttribute()
    {
        if ($this->periodeable_type == 'Waka\Agg\Models\AgMonth') {
            return $this->periodeable->ag_year . '/' . $this->periodeable->ag_month;
        }
        if ($this->periodeable_type == 'Waka\Agg\Models\AgYear') {
            return $this->periodeable->ag_year;
        }
        if ($this->periodeable_type == 'Waka\Agg\Models\AgWeek') {
            return $this->periodeable->ag_year . '/' . $this->periodeable->ag_week;
        }
    }
    public function getFirstNameAttribute()
    {
        if ($this->periodeable_type == 'Waka\Agg\Models\AgMonth') {
            return $this->periodeable->ag_month;
        }
        if ($this->periodeable_type == 'Waka\Agg\Models\AgYear') {
            return $this->periodeable->ag_year;
        }
        if ($this->periodeable_type == 'Waka\Agg\Models\AgWeek') {
            return $this->periodeable->ag_week;
        }
    }

    public function scopeMonths($query)
    {
        return $query->where('periodeable_type', 'Waka\Agg\Models\AgMonth');
    }
    public function scopeYears($query)
    {
        return $query->where('periodeable_type', 'Waka\Agg\Models\AgYear');
    }
    public function scopeWeeks($query)
    {
        return $query->where('periodeable_type', 'Waka\Agg\Models\AgWeek');
    }
    public function scopeYear($query, $value)
    {
        return $query->whereHas('periodeable', function ($q) use ($value) {
            $q->where('ag_year', $value);
        });
    }
}
