<?php
/**
 * Class Reseller
 * Aug 2021
 * 12:54 PM
 * @author Yoel Limbu <yoyal.limbu@gmail.com>
 */

namespace GeniussystemsNp\InventoryManagement\Models;

use Illuminate\Database\Eloquent\Model;



class Group extends Model  {

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
            'name','slug', 'profile', 'logo', 'details','status'
    ];


}