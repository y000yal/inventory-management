<?php
/**
 * Created by PhpStorm.
 * @author Kabina Suwal <kabina.suwal92@gmail.com>
 * Date: 21-Jun-18
 * Time: 12:39 PM
 */

namespace InventoryManagement\Repo\RepoInterface;



use \InventoryManagement\Repo\RepoInterface\BaseInterface;

interface VendorInterface extends BaseInterface
{
    public function getAllWithParam(array $parameter, $path);
    public function firstOrCreate(array $data);


}