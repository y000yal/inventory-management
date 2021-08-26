<?php
/**
 * Class InventoryVendor
 * Aug 2021
 * 2:16 PM
 * @author Yoel Limbu <yoyal.limbu@gmail.com>
 */
namespace GeniussystemsNp\InventoryManagement\Models;



use GeniussystemsNp\InventoryManagement\Database\Factories\VendorFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Vendor extends Model
{
//    use HasFactory;
    /**
     * The name of table to which this model is associated with.
     *
     * @var string
     */
    protected $table = "vendors";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name','slug'
    ];


    protected $appends = ['active_inventory_count','inventory_count'];
    protected static function newFactory()
    {
        return VendorFactory::new();
    }
    public function inventoryModels()
    {
        return $this->hasMany('GeniussystemsNp\InventoryManagement\Models\Model', 'vendor_id', 'id');
    }

    public function inventories()
    {
        return $this->hasMany('GeniussystemsNp\InventoryManagement\Models\Inventory', 'vendor');

    }

    public function getInventoryCountAttribute()
    {
        return 0;
    }

    public function getActiveInventoryCountAttribute()
    {
        return 0;
    }


}