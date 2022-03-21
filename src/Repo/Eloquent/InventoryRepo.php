<?php
/**
 * Class GroupRepo
 * Aug 2021
 * 2:45 PM
 * @author Yoel Limbu <yoyal.limbu@gmail.com>
 */

namespace GeniussystemsNp\InventoryManagement\Repo\Eloquent;


use \GeniussystemsNp\InventoryManagement\Models\Inventory;
use GeniussystemsNp\InventoryManagement\Repo\Eloquent\BaseRepo;
use \GeniussystemsNp\InventoryManagement\Repo\RepoInterface\InventoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class InventoryRepo extends BaseRepo implements InventoryInterface {
    protected $inventory;


    public function __construct(Inventory $inventory) {
        parent::__construct($inventory);
        $this->inventory = $inventory;

    }

    public function getAllWithParamByReseller($reseller_id, array $parameter, $path) {
        $columnsList = Schema::getColumnListing($this->model->getTable());

        //$is_columnExistInUserTable = false;

        $orderByColumn = "id";
        foreach ($columnsList as $columnName) {
            if ($columnName == $parameter["sort_field"]) {
                $orderByColumn = $columnName;
                break;
            }
        }
        $parameter["sort_field"] = $orderByColumn;
        if (isset($parameter["filter_field"])) {
            if (in_array($parameter["filter_field"], $columnsList)) {
                $data = $this->model->where($parameter["filter_field"], $parameter["filter_value"]);
            }
            else {
                $data = $this->inventory;
            }


        }
        else {
            $data = $this->inventory;
        }

        if (isset($parameter["filter"])) {
            $filterParams = $parameter["filter"];
            foreach ($filterParams as $key => $val) {
                /**
                 * Check if filter is needed from relationship or column of a table.
                 * If item count of $checkKey is 1 after exploding $key, filter from table column. Else use relation existence method for filter.
                 */
                $checkKey = explode(".", $key);
                $count = count($checkKey);
                if ($count == 1) {
                    if ($key == "status") {
                        $data = $data->where($key, "like", "$val");

                    }
                    else {
                        $data = $data->where($key, "like", "$val%");

                    }


                }
                else {

                    $relationKey = camel_case(implode(".", array_except($checkKey, [$count - 1])));
                    //                    dd($relationKey);

                    //                    $data = $data->whereHas($relationKey, function ($query) use ($checkKey, $val) {
                    //                        $query->where(last($checkKey), 'like', "$val%");
                    //                    });


                    if ($relationKey == 'macs') {
                        $data = $data->join('inventory_macs', 'inventory_macs.inventory_id', 'inventory.id')->where('inventory_macs.mac', $val);
                    }

                }
            }
        }


        if (!empty($reseller_id)) {
            $data = $data->where('owner', $reseller_id);
            //return $data->with(['vendor:id,name','model:id,name','owner:id,name','macs:inventory_id,mac','inventoryUser.user:id,username'])->orderBy($orderByColumn, $parameter["sort_by"])->where("web_user","0")->paginate($parameter["limit"])->withPath($path)->appends($parameter);
        }
        return $data->with(['vendor:id,name', 'model:id,name', 'group:id,name'])
                    ->orderBy('inventory.' . $orderByColumn, $parameter["sort_by"])
                    ->paginate($parameter["limit"])
                    ->withPath($path)->appends($parameter);
    }


    public function getSpecificBySerialAndOwner($serial, $reseller_id) {
        $data = $this->inventory->where([
                                                ['owner', $reseller_id],
                                                ['serial', $serial]
                                        ])->first();

        return $data;
    }

    public function getInventoryWithParamAdmin(array $parameter, $path) {
        $columnsList = Schema::getColumnListing($this->model->getTable());

        //$is_columnExistInUserTable = false;

        $orderByColumn = "id";
        foreach ($columnsList as $columnName) {
            if ($columnName == $parameter["sort_field"]) {
                $orderByColumn = $columnName;
                break;
            }
        }
        $parameter["sort_field"] = $orderByColumn;
        if (isset($parameter["filter_field"])) {
            if (in_array($parameter["filter_field"], $columnsList)) {
                $data = $this->model->where($parameter["filter_field"], $parameter["filter_value"]);
            }
            else {
                $data = $this->inventory;
            }

        }
        else {
            $data = $this->inventory;
        }
        $data = $data->with('owner:id,name')->with(['users']);
        if (isset($parameter["q"])) {
            $searchValue = "%" . $parameter["q"] . "%";

            $data = $data->where(function ($query) use ($searchValue, $columnsList) {
                foreach ($columnsList as $key => $columnName) {
                    $query->orWhere($columnName, "like", $searchValue);
                }
            });

        }
        return $data->orderBy($orderByColumn, $parameter["sort_by"])->paginate($parameter["limit"])->withPath($path)->appends($parameter);
    }

    public function getFreshInventoryWithParam(array $parameter, $path) {
        $columnsList = Schema::getColumnListing($this->model->getTable());

        //$is_columnExistInUserTable = false;

        $orderByColumn = "id";
        foreach ($columnsList as $columnName) {
            if ($columnName == $parameter["sort_field"]) {
                $orderByColumn = $columnName;
                break;
            }
        }
        $parameter["sort_field"] = $orderByColumn;
        if (isset($parameter["filter_field"])) {
            if (in_array($parameter["filter_field"], $columnsList)) {
                $data = $this->model->where($parameter["filter_field"], $parameter["filter_value"]);
            }
            else {
                $data = $this->inventory;
            }


        }
        else {
            $data = $this->inventory;
        }
        $data = $data->with('owner:id,name');
        if (isset($parameter["q"])) {
            $searchValue = "%" . $parameter["q"] . "%";

            $data = $data->where(function ($query) use ($searchValue, $columnsList) {
                foreach ($columnsList as $key => $columnName) {
                    $query->orWhere($columnName, "like", $searchValue);
                }
            });

        }
        return $data->where('owner', null)->orderBy($orderByColumn, $parameter["sort_by"])->paginate($parameter["limit"])->withPath($path)->appends($parameter);
    }

    public function getSpecificByIdAdmin($id) {
        $data = $this->inventory->with('owner:id,name')->findOrFail($id);
        return $data;

    }

    public function checkInventoryAssignedToGroup($inventory) {

        $data = $this->inventory->where("serial", '=', $inventory)->has("group")->get();

        return $data;
    }

    public function attachInventoryGroup($request) {
        $data = $this->inventory->where("serial", $request['inventory_serial'])->update(['group_id' => $request['group_id']]);
        return $data;
    }

    public function getUnusedInventories($group_id) {
        return $this->inventory->where([
                                               ['group_id', $group_id],
                                               ['status', "0"],
                                               ['is_faulty', "0"]
                                       ])->doesntHave('subscribers')->get();
    }

    public function getUnassignedInventoriesToReseller() {
        return $this->inventory->with(['macs:inventory_id,mac'])->whereNull('group_id')->paginate();
    }

    public function getSpecificById($mac) {
        $field = is_numeric($mac) ? 'id' : 'mac';
        return $this->inventory->with(['vendor:id,name', 'model:id,name', 'owner:id,name', 'hotspot'])->where($field, $mac)->firstOrFail();
    }

    public function getSpecificBySerial($serial) {
        return $this->inventory->with(['vendor:id,name', 'model:id,name', 'group:id,name,slug', 'macs', 'subscribers:id,username'])->where('serial', $serial)->firstOrFail();

    }

    public function getLatestBatchNo() {
        return $this->inventory->max('batch_no');
    }

    /**
     * Check the list of Inventories fetched from request belongs to given reseller and does not have subscribers.
     * @param $inventories
     * @param $reseller_id
     * @return mixed
     */

    public function checkUnassignedInventoriesOfGroup($inventories, $group_id) {
        return $this->inventory->where('group_id', $group_id)->where('serial', $inventories)->doesntHave('subscribers')->get();
    }

    public function changeStatusOfMultipleInventory(array $ids, $status) {
        return $this->inventory->whereIn('id', $ids)->update(["status" => $status]);

    }

    public function getInventoryReportData($reseller_id = null) {

        // dd($reseller_id);

        if (is_null($reseller_id)) {
            return $this->inventory->select(DB::raw("COUNT(*) as count"), "status")->where("web_user", "0")->groupBy('status')->get();
        }
        else {
            return $this->inventory->select(DB::raw("COUNT(*) as count"), "status")->where([
                                                                                                   ["web_user", "0"],
                                                                                                   ["owner", $reseller_id]
                                                                                           ])->groupBy('status')->get();

        }
    }


    public function getInventorySubscriber($serial) {
        return $this->inventory->with(['inventoryUser'])->where('serial', $serial)->firstOrFail();
    }

    public function getAllInventoryList($reseller_id, array $parameter, $path) {
        $columnsList = Schema::getColumnListing($this->model->getTable());

        //$is_columnExistInUserTable = false;

        $orderByColumn = "id";
        foreach ($columnsList as $columnName) {
            if ($columnName == $parameter["sort_field"]) {
                $orderByColumn = $columnName;
                break;
            }
        }
        $parameter["sort_field"] = $orderByColumn;
        if (isset($parameter["filter_field"])) {
            if (in_array($parameter["filter_field"], $columnsList)) {
                $data = $this->model->where($parameter["filter_field"], $parameter["filter_value"]);
            }
            else {
                $data = $this->inventory;
            }


        }
        else {
            $data = $this->inventory;
        }

        if (isset($parameter["filter"])) {
            $filterParams = $parameter["filter"];
            foreach ($filterParams as $key => $val) {
                /**
                 * Check if filter is needed from relationship or column of a table.
                 * If item count of $checkKey is 1 after exploding $key, filter from table column. Else use relation existence method for filter.
                 */
                $checkKey = explode(".", $key);
                $count = count($checkKey);
                if ($count == 1) {
                    if ($key == "status") {
                        $data = $data->where($key, "like", "$val");

                    }
                    else {
                        $data = $data->where($key, "like", "$val%");

                    }


                }
                else {


                }
            }
        }
        if (isset($parameter["q"])) {
            $searchValue = "%" . $parameter["q"] . "%";

            $data = $data->where(function ($query) use ($searchValue, $columnsList) {
                foreach ($columnsList as $key => $columnName) {
                    $query->orWhere($columnName, "like", $searchValue);
                }
            });

        }

        if (!empty($reseller_id)) {
            $data = $data->where('owner', $reseller_id);
        }
        return $data->with(['vendor:id,name', 'model:id,name', 'owner:id,name', 'macs:inventory_id,mac', 'inventoryUser.user:id,username'])->orderBy($orderByColumn, $parameter["sort_by"])->paginate($parameter["limit"])->withPath($path)->appends($parameter);
    }

    public function getPublicSpecificBySerial($serial) {
        return $this->inventory
                ->select('id', 'mac', 'serial', 'os_version', 'batch_no', 'web_user', 'status')
                /*->selectRaw('IF((status == active), "Yes", "No") as 1,
                                IF((status == inactive), "Yes", "No") as 0')*/
                ->with(['vendor:id,name', 'model:id,name', 'owner:id,name,username', 'macs', 'hotspot', 'inventoryUser.user', 'activePackage', 'subscribedPackages'])
                ->where('serial', $serial)
                ->firstOrFail();
    }

}
