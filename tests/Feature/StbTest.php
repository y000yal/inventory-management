<?php
/**
 * Class StbTest
 *
 * @category
 * @package InventoryManagement\Tests\Feature
 * @author Yoel Limbu <yoyal.limbu@gmail.com>
 */

namespace InventoryManagement\Tests\Feature;


use InventoryManagement\Models\Inventory;

class StbTest extends \InventoryManagement\Tests\TestCase {
    //    public function test_creating_new_stb() {
    //        $create = [
    //                "serial"  => 456842654,
    //                "vendor"  => 68,
    //                "model"   => 24,
    //                "macs[0]" => "00:22:6D:9E:58:7A",
    //                "status"  => "inactive"
    //        ];
    //        $response= $this->stb->create($create);
    //        $this->assertEquals($response->serial,456842654);
    //    }
    //    public function test_get_all_stbs() {
    //        $params = [
    //                'sort_by'    => 'desc',
    //                'sort_field' => 'id',
    //                'limit'      => 10
    //        ];
    //        $path = 'http://localhost/resources/public/stbs/';
    //        $response = $this->stb->getAllWithParam($params, $path);
    //        $this->assertIsObject($response);
    //        $this->assertNotEmpty($response); //check if null
    //    }
    //        public function test_get_single_stb_with_serial() {
    //            $serial = 456842654; //change as per your data in db
    //            $response = $this->stb->getSpecificBySerial($serial); //if not found returns ModelNotFoundExecution
    //            $this->assertEquals(456842654, $response->serial, 'check if edited serial matches');
    //        }
    //    public function test_update_stb_with_serial() {
    //        $serial = 456842654;
    //        $stb = $this->stb->getSpecificBySerial($serial);
    //        $update = [
    //                'vendor' => '68',
    //        ];
    //        $this->stb->update($stb->id, $update);
    //    }
//    public function test_delete_stb() {
    //        $id = 7102737;
    //        $this->stb->delete($id);
    //    }
}