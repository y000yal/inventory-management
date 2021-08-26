<?php
/**
 * Created by PhpStorm.
 * @author Kabina Suwal <kabina.suwal92@gmail.com>
 * Date: 21-Jun-18
 * Time: 11:42 AM
 */

namespace GeniussystemsNp\InventoryManagement\Models;

use Illuminate\Database\Eloquent\Model as baseModel;

class Model extends baseModel {

    /**
     * The name of table to which this model is associated with.
     *
     * @var string
     */
    protected $table = "models";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
            'name', 'vendor_id', 'slug'
    ];

    protected $appends = ['active_inventory_count', 'inventory_count'];

    public function vendor() {
        return $this->belongsTo('GeniussystemsNp\InventoryManagement\Models\Vendor', 'vendor_id', 'id');
    }

    public function inventories() {
        return $this->hasMany('GeniussystemsNp\InventoryManagement\Models\Inventory', 'model', 'id');

    }


    public function getInventoryCountAttribute() {
        return 0;
    }

    public function getActiveInventoryCountAttribute() {
        return 0;
    }


}