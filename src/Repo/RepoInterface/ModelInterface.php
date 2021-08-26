<?php
/**
 * Created by PhpStorm.
 * @author Kabina Suwal <kabina.suwal92@gmail.com>
 * Date: 21-Jun-18
 * Time: 01:15 PM
 */
namespace GeniussystemsNp\InventoryManagement\Repo\RepoInterface;
use \GeniussystemsNp\InventoryManagement\Repo\RepoInterface\BaseInterface;


interface ModelInterface extends BaseInterface
{
    public function getAllWithParam(array $parameter, $path);

    public function firstOrCreate(array $data);
}