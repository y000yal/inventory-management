<?php
/**
 * Created by PhpStorm.
 * @author Kabina Suwal <kabina.suwal92@gmail.com>
 * Date: 21-Jun-18
 * Time: 12:42 PM
 */

namespace GeniussystemsNp\InventoryManagement\Repo\Eloquent;


use \GeniussystemsNp\InventoryManagement\Repo\Eloquent\BaseRepo;
use \GeniussystemsNp\InventoryManagement\Models\Vendor;
use \GeniussystemsNp\InventoryManagement\Repo\RepoInterface\VendorInterface;
use Illuminate\Support\Facades\Schema;

class VendorRepo extends BaseRepo implements VendorInterface
{
    protected $vendor;


    public function __construct(Vendor $vendor)
    {
        parent::__construct($vendor);
        $this->vendor = $vendor;
    }

    public function getAllWithParam(array $parameter, $path)
    {
        $columnsList = Schema::getColumnListing($this->vendor->getTable());

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
                $data = $this->vendor->where($parameter["filter_field"], $parameter["filter_value"]);
            } else {
                $data = $this->vendor;
            }


        } else {
            $data = $this->vendor;
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
                    $data = $data->where($key, "like", "$val%");

                } else {

                    $relationKey = camel_case(implode(".", array_except($checkKey, [$count - 1])));

                    $data = $data->whereHas($relationKey, function ($query) use ($checkKey, $val) {
                        $query->where(last($checkKey), 'like', "$val%");
                    });

                }
            }
        }
//        if (isset($parameter["q"])) {
//            $searchValue = "%" . $parameter["q"] . "%";
//
//            $data = $data->where(function ($query) use ($searchValue, $columnsList) {
//                foreach ($columnsList as $key => $columnName) {
//                    $query->orWhere($columnName, "like", $searchValue);
//                }
//            });
//
//        }




        return $data
            ->with(['inventoryModels:vendor_id,id,name,slug'])
            ->orderBy($orderByColumn, $parameter["sort_by"])
            ->paginate($parameter["limit"])
            ->withPath($path)
            ->appends($parameter);
    }



    public function firstOrCreate(array $data)
    {
        return $this->vendor->firstOrCreate($data);

    }
}