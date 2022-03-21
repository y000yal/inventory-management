<?php
/**
 * Class GroupRepo
 * Aug 2021
 * 2:45 PM
 * @author Yoel Limbu <yoyal.limbu@gmail.com>
 */

namespace GeniussystemsNp\InventoryManagement\Repo\Eloquent;


use GeniussystemsNp\InventoryManagement\Models\Group;
use \GeniussystemsNp\InventoryManagement\Repo\RepoInterface\GroupInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;


class GroupRepo extends BaseRepo implements GroupInterface {

    protected $group;

    /**
     * ResellerRepo constructor.
     */
    public function __construct(Group $group) {
        parent::__construct($group);
        $this->group = $group;
    }


}
