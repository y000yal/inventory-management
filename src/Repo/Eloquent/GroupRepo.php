<?php
/**
 * Created by PhpStorm.
 * User: Prashant
 * Date: 5/8/2018
 * Time: 2:39 PM
 */

namespace GeniussystemsNp\InventoryManagement\Repo\Eloquent;


use GeniussystemsNp\InventoryManagement\Models\Group;
use \GeniussystemsNp\InventoryManagement\Repo\RepoInterface\GroupInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class GroupRepo extends BaseRepo implements GroupInterface
{

    protected $group;
    protected $user;

    /**
     * ResellerRepo constructor.
     */
    public function __construct(Group $group)
    {
        parent::__construct($group);
        $this->group = $group;
    }

    public function getPublicResellerList($platform)
    {
        return $this->group->where([
            ['reseller_id', null],
            ['status', '1'],
        ])->whereRaw("(platforms & $platform) = $platform")->get();
    }


    public function getPublicResellerListV2()
    {
        return $this->group
            ->where([
            ['reseller_id', null],
            ['status', '1']])
            ->select("id", "name","profile", "logo","details as description","status","reseller_id","platforms as platform_mask","role","email","username",
                "first_name","middle_name","last_name","address", "city","district","province","country","phone_no","mobile_no", "website","longitude","latitude",
                "expiry_date_format","expiry_date","content_expiry_flag")
            ->get();
    }


}
