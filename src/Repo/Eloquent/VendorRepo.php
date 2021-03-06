<?php
/**
 * Class GroupRepo
 * Aug 2021
 * 2:45 PM
 * @author Yoel Limbu <yoyal.limbu@gmail.com>
 */

namespace InventoryManagement\Repo\Eloquent;


use \InventoryManagement\Repo\Eloquent\BaseRepo;
use \InventoryManagement\Models\Vendor;
use \InventoryManagement\Repo\RepoInterface\VendorInterface;
use Illuminate\Support\Facades\Schema;

class VendorRepo extends BaseRepo implements VendorInterface
{
    protected $vendor;


    public function __construct(Vendor $vendor)
    {
        parent::__construct($vendor);
        $this->vendor = $vendor;
    }





    public function firstOrCreate(array $data)
    {
        return $this->vendor->firstOrCreate($data);

    }
}
