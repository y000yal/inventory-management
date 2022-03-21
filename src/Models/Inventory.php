<?php

/**
 * Created by PhpStorm.
 * @author Kabina Suwal <kabina.suwal92@gmail.com>
 * Date: 21-Jun-18
 * Time: 11:47 AM
 */

namespace GeniussystemsNp\InventoryManagement\Models;

use GeniussystemsNp\InventoryManagement\Database\Factories\InventoryFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Inventory extends Model {

    use SoftDeletes;

    /**
     * The name of table to which this model is associated with.
     * @var string
     */
    protected $table = "inventory";

    protected $hidden = [
            "pivot"
    ];

    /**
     * The attributes that are mass assignable.
     * @var array
     */

    protected $fillable = [
            'serial', 'vendor', 'model', 'os_version', 'group_id', 'batch_no', 'status', 'web_user', 'is_faulty'
    ];

    protected static function newFactory() {
        return InventoryFactory::new();
    }

    public function vendor() {
        return $this->belongsTo('GeniussystemsNp\InventoryManagement\Models\Vendor', 'vendor');
    }

    public function model() {
        return $this->belongsTo('GeniussystemsNp\InventoryManagement\Models\Model', 'model');

    }

    public function group() {
        return $this->belongsTo('GeniussystemsNp\InventoryManagement\Models\Group', 'group_id', 'id');

    }

    public function subscribers() {
        return $this->belongsToMany("App\Models\Subscriber", "subscriber_ipcams", "inventory_id", "subscriber_id")->wherePivot('deleted_at', null);
    }

    public function macs() {
        return $this->hasMany(Mac::class, 'inventory_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */

}
