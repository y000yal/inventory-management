<?php
/**
 * Class StbModelTest
 *
 * @category
 * @package InventoryManagement\Tests\Feature
 * @author Yoel Limbu <yoyal.limbu@gmail.com>
 */

namespace InventoryManagement\Tests\Feature;


use Faker\Factory;

class ModelTest extends \InventoryManagement\Tests\TestCase {

    /**
     * @test
     */
    public function test_create_stb_model() { //testing of stb model store

//        $model = $this->stbModel->create([
//                                                 'name' => 'model1',
//                                         ]);
//        $this->assertEquals('model1', $model->name, 'check if saved name is model1 or not');
    }

    /**
     * @test
     */
//    public function test_get_all_models_with_params() { //testing of retrieving all stb models
//        $params = [
//                'sort_by'    => 'desc',
//                'sort_field' => 'id',
//                'limit'      => 10
//        ];
//        $path = 'http://localhost/resources/public/stb/vendors';
//        $models = $this->stbModel->getAllWithParam($params, $path);
//        $this->assertIsObject($models);
//        $this->assertNotEmpty($models); //check if null
//    }
//
//    public function test_get_single_vendor() { //testing of retrieving a single model
//        $id = 56; //change as per your data in db
//        $model = $this->stbModel->getSpecificById($id); //if not found returns ModelNotFoundExecution
//        $this->assertEquals($id, $model->id, 'check if retrieved model is correct');
//    }
//
//    public function test_update_vendor() { //testing of updating a specific stb model by id
//        $id = 56; //change as per your data in db
//        $model = $this->stbModel->getSpecificById($id);
//        $model->name = 'check edited model';
//        $this->stbModel->update($model->id, $model->toArray());
//    }
//
//        public function test_delete_vendor() {
//            $id = 56;
//            $this->stbModel->delete($id);
//        }
}
