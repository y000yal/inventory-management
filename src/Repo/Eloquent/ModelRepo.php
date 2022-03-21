<?php
/**
 * Class GroupRepo
 * Aug 2021
 * 2:45 PM
 * @author Yoel Limbu <yoyal.limbu@gmail.com>
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


    public function firstOrCreate(array $data)
    {
        return $this->model->firstOrCreate($data);

    }
}
