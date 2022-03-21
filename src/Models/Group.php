<?php
/**
 * Class Reseller
 * Aug 2021
 * 12:54 PM
 * @author Yoel Limbu <yoyal.limbu@gmail.com>
 */

namespace InventoryManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Group extends Model {
    use SoftDeletes;

    /**
     * The name of table to which this model is associated with.
     *
     * @var string
     */
    protected $table = "groups";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = [
        'name', 'slug', 'profile', 'logo', 'details', 'status'
    ];
    protected $hidden   = ['pivot'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function inventories() {
        return $this->hasMany('InventoryManagement\Models\Inventory', 'group_id');

    }

    public function getLogoAttribute($value) {
        if (empty($value)) {
            return $value;
        }
        return config('Config.channel_base_url') . "/images/" . $value;

    }

}
