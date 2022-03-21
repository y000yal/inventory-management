<?php
/**
 * Created by PhpStorm.
 * @author Kabina Suwal <kabina.suwal92@gmail.com>
 * Date: 21-Jun-18
 * Time: 01:15 PM
 */
namespace InventoryManagement\Repo\RepoInterface;
use \InventoryManagement\Repo\RepoInterface\BaseInterface;


interface ModelInterface extends BaseInterface
{
    public function getAllWithParam(array $parameter, $path);

    public function firstOrCreate(array $data);
}