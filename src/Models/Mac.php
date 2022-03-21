<?php
/**
 * Class StbMac
 * Aug 2021
 * 12:20 PM
 * @author Yoel Limbu <yoyal.limbu@gmail.com>
 */
namespace GeniussystemsNp\InventoryManagement\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Mac extends Model
{
    use SoftDeletes;
    /**
     * The name of table to which this model is associated with.
     *
     * @var string
     */
    protected $table = "macs";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'inventory_id','mac'
    ];

    public function stb()
    {
        return $this->belongsTo(Inventory::class,'inventory_id');
    }
}
