<?php
/**
 * Class GroupRepo
 * Aug 2021
 * 2:45 PM
 * @author Yoel Limbu <yoyal.limbu@gmail.com>
 */
namespace InventoryManagement\Repo\Eloquent;


use \InventoryManagement\Models\InventoryModel;
use InventoryManagement\Models\Model;
use \InventoryManagement\Repo\Eloquent\BaseRepo;
use InventoryManagement\Repo\RepoInterface\ModelInterface;
use Illuminate\Support\Facades\Schema;

class ModelRepo extends BaseRepo implements ModelInterface
{
    protected $model;


    public function __construct(Model $model)
    {
        parent::__construct($model);
        $this->model = $model;
    }


    public function firstOrCreate(array $data)
    {
        return $this->model->firstOrCreate($data);

    }
}
