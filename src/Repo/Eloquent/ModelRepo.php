<?php
/**
 * Created by PhpStorm.
 * @author Kabina Suwal <kabina.suwal92@gmail.com>
 * Date: 21-Jun-18
 * Time: 01:16 PM
 */

namespace GeniussystemsNp\InventoryManagement\Repo\Eloquent;


use \GeniussystemsNp\InventoryManagement\Models\InventoryModel;
use GeniussystemsNp\InventoryManagement\Models\Model;
use \GeniussystemsNp\InventoryManagement\Repo\Eloquent\BaseRepo;
use GeniussystemsNp\InventoryManagement\Repo\RepoInterface\ModelInterface;
use Illuminate\Support\Facades\Schema;

class ModelRepo extends BaseRepo implements ModelInterface
{
    protected $model;


    public function __construct(Model $model)
    {
        parent::__construct($model);
        $this->model = $model;
    }

    public function getAllWithParam(array $parameter, $path)
    {
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
            } else {
                $data = $this->model;
            }


        } else {
            $data = $this->model;
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
        return $data->with('vendor:id,name')
            ->orderBy($orderByColumn, $parameter["sort_by"])
            ->paginate($parameter["limit"])
            ->withPath($path)
            ->appends($parameter);
    }

    public function firstOrCreate(array $data)
    {
        return $this->model->firstOrCreate($data);

    }
}