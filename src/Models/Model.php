<?php
/**
 * Created by PhpStorm.
 * @author Kabina Suwal <kabina.suwal92@gmail.com>
 * Date: 21-Jun-18
 * Time: 11:42 AM
 */

namespace InventoryManagement\Models;

use Illuminate\Database\Eloquent\Model as baseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class Model extends baseModel {
use SoftDeletes;
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
            'name','description', 'vendor_id', 'slug'
    ];


    public function vendor() {
        return $this->belongsTo('InventoryManagement\Models\Vendor', 'vendor_id', 'id');
    }

    public function inventories() {
        return $this->hasMany('InventoryManagement\Models\Inventory', 'model', 'id');

    }




}
