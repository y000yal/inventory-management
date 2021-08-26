<?php
/**
 * Created by PhpStorm.
 * @author Kabina Suwal <kabina.suwal92@gmail.com>
 * Date: 21-Jun-18
 * Time: 02:51 PM
 */

namespace GeniussystemsNp\InventoryManagement\Repo\RepoInterface;
use \GeniussystemsNp\InventoryManagement\Repo\RepoInterface\BaseInterface;

interface InventoryInterface extends BaseInterface
{
    public function getAllWithParamByReseller($reseller_id,array $parameter, $path);

    public function getSpecificBySerialAndOwner($mac,$reseller_id);

    public function getUnusedInventories($reseller_id);

    public function getUnassignedInventoriesToReseller();

    public function getSpecificBySerial($serial);

    public function getLatestBatchNo();

    public function checkUnassignedInventoriesOfReseller($inventories,$reseller_id);

    public function changeStatusOfMultipleInventory(array $ids,$status);

    public function getInventoryReportData($reseller_id = null);


    public function getPublicSpecificBySerial($serial);
   
}