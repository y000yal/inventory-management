<?php

/**
 * Class Media
 * Aug 2021
 * 4:12 PM
 * @author Yoel Limbu <yoyal.limbu@gmail.com>
 */

namespace GeniussystemsNp\InventoryManagement\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Media extends Model
{
    use SoftDeletes;
    /**
     * The name of table to which this model is associated with.
     *
     * @var string
     */
    protected $table = "media";

    protected $hidden = [
        "pivot"
    ];
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['type', 'data', 'mime_type', 'extension', 'size', 'path'];
}
