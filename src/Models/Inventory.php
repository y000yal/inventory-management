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

class Inventory extends Model
{

    /**
     * The name of table to which this model is associated with.
     *
     * @var string
     */
    protected $table = "inventory";

    protected $hidden = [
        "pivot"
    ];
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'serial', 'vendor', 'model', 'os_version', 'group', 'batch_no', 'status', 'web_user'
    ];
    protected static function newFactory()
    {
        return InventoryFactory::new();
    }
    public function vendor()
    {
        return $this->belongsTo('GeniussystemsNp\InventoryManagement\Models\Vendor', 'vendor');
    }

    public function model()
    {
        return $this->belongsTo('GeniussystemsNp\InventoryManagement\Models\Model', 'model');

    }

    public function group()
    {
        return $this->belongsTo('GeniussystemsNp\InventoryManagement\Models\Group', 'group', 'id');

    }

    public function subscribers()
    {
        return $this->belongsToMany("GeniussystemsNp\InventoryManagement\Models\Subscribers", "subscriber_inventories", "inventory_id", "user_id")->withPivot('status', 'package_id', 'balance', 'expiry_date', 'extended_date', 'parse_session');
    }


    public function user()
    {
        return $this->belongsToMany("GeniussystemsNp\InventoryManagement\Models\User", "user_inventories", "inventory_id", "user_id")->withPivot('status', 'package_id', 'balance', 'expiry_date', 'extended_date');
    }

    public function macs()
    {
        return $this->hasMany(Mac::class, 'inventory_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */

}
