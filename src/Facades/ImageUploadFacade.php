<?php
/**
 * Created by PhpStorm.
 * User: ashish
 * Date: 6/29/2017
 * Time: 3:47 PM
 */

namespace  GeniussystemsNp\InventoryManagement\Facades;


use Illuminate\Support\Facades\Facade;

class ImageUploadFacade extends Facade
{

    protected static function getFacadeAccessor()
    {
        return "imageUploader";
    }
}