<?php
/**
 * Class BaseRepo
 * Aug 2021
 * 2:49 PM
 * @author Yoel Limbu <yoyal.limbu@gmail.com>
 */


namespace InventoryManagement\Repo\Eloquent;

use \InventoryManagement\Repo\RepoInterface\BaseInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;


class BaseRepo implements BaseInterface {

    protected $model;

    public function __construct(Model $model) {
        $this->model = $model;
    }

    /**
     * Get paginated data according to the given conditions passed.
     * @param $status
     * @param $sortBy
     * @param $limit
     * @return mixed
     */
    public function getAll($sortBy, $limit) {
        return $this->model->orderBy('id', $sortBy)->paginate($limit);
    }

    /**
     * Insert new row in related table.
     * @param array $data
     */
    public function create(array $data) {
        return $this->model->create($data);
    }

    /**
     * Insert multiple row in related table.
     * @param array $data
     */
    public function insert(array $data) {
        return $this->model->insert($data);
    }


    /**
     * Update row of given id in related table.
     * @param array $data
     * @param $id
     */
    public function update($id, array $data) {

        $this->model->findOrFail($id)->update($data);
        $data = $this->model->findOrFail($id);
        return $data;
    }

    /**
     * Delete row of given id in related table.
     * @param $id
     */
    public function delete($id) {

        return $this->model->findOrFail($id)->delete();
    }

    /**
     * Get data related to given id in related table.
     * @param $id
     */
    public function getSpecificById($id) {

        $data = $this->model->findOrFail($id);
        return $data;

    }

    public function getAllWithParam(array $parameter, $path) {
        $columnsList = Schema::getColumnListing($this->model->getTable());

        //$is_columnExistInUserTable = false;

        $orderByColumn = "id";

        foreach ($columnsList as $columnName) {

            if (isset($parameter["sort_field"]) && $columnName == $parameter["sort_field"]) {
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
                $data = $this->model;
            }


        }
        else {
            $data = $this->model;
        }
        /**
         * Multiple filter Implementation
         */

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

                }
                else {

                    $relationKey = camel_case(implode(".", array_except($checkKey, [$count - 1])));

                    $data = $data->whereHas($relationKey, function ($query) use ($checkKey, $val) {
                        $query->where(last($checkKey), 'like', "$val%");
                    });

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

        if (isset($parameter["start_date"])) {
            $data = $data->where('created_at', '>=', $parameter['start_date'] . ' 00:00:00');
        }

        if (isset($parameter["end_date"])) {
            $data = $data->where('created_at', '<=', $parameter['end_date'] . ' 23:59:59');

        }
        if (isset($parameter['with_relationship'])) {
            $data = $data->with($parameter['with_relationship']);
        }
        if (isset($parameter['sort_by'])) {
            $data = $data->orderBy($orderByColumn, $parameter["sort_by"]);
        }

        return $data->paginate($parameter["limit"])->withPath($path)->appends($parameter);
    }

    public function getSpecificByColumnValue($column, $value) {
        return $this->model->where($column, $value)->first();
    }

    public function deleteMutipleByColumnValue($column, array $values) {
        return $this->model->whereIn($column, $values)->delete();
    }

    public function findByField($field, $value) {
        return $this->model->where($field, $value)->firstOrFail();
    }

    public function getSpecificByIdOrSlug($id) {
        $field = is_numeric($id) ? "id" : "slug";
        return $this->model->where($field, $id)->firstOrFail();
    }

    public function getAllByColumnValue($column, $value) {
        return $this->model->where($column, $value)->get();
    }

    public function createNewSlug($name) {
        $slug = str_slug($name);
        $data = $this->model->where('slug', 'like', "$slug%")->selectRaw(DB::raw('slug,max(cast(replace(slug,"' . $slug . '-","") as unsigned)) as slug_no'))->first();

        if ($data['slug_no'] > 0) {
            return $slug . '-' . ($data['slug_no'] + 1);
        }
        else {
            if ($data['slug'] == $slug) {
                return $slug . '-1';
            }
            else {
                return $slug;
            }
        }


    }

    public function getAllIn($column, $arrayValue) {
        return $this->model->whereIn($column, $arrayValue)->get();
    }

    public function createOrUpdate($matchThese, $data) {
        return $this->model->updateOrCreate($matchThese, $data);
    }


    public function removeLinks($data) {
        $data = $data->toArray();
        unset($data['links']);
        return $data;
    }

}
