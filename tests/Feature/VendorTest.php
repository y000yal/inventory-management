<?php

namespace GeniussystemsNp\InventoryManagement\Tests\Feature;


use GeniussystemsNp\InventoryManagement\Models\Vendor;
use GeniussystemsNp\InventoryManagement\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class VendorTest extends TestCase {
//    use  RefreshDatabase;

    /**
     * @test
     */
    public function test_create_stb_vendor() {
//        StbVendor::factory()->create(); //this uses factory
//        $vendor = $this->stbVendor->create([
//                                                   'name' => 'check',
//                                           ]);
//        $this->assertEquals('check', $vendor->name, 'check if saved name is check or not');
  }
//
//    /**
//     * @test
//     */
//    public function test_get_all_vendors_with_params() {
//        $params = [
//                'sort_by'    => 'desc',
//                'sort_field' => 'id',
//                'limit'      => 10
//        ];
//        $path = 'http://localhost/resources/public/stb/vendors';
//        $vendors = $this->stbVendor->getAllWithParam($params, $path);
//        $this->assertIsObject($vendors);
//        $this->assertNotEmpty($vendors); //check if null
//    }
//
//    public function test_get_single_vendor() {
//        $id = 56; //change as per your data in db
//        $vendor = $this->stbVendor->getSpecificById($id); //if not found returns ModelNotFoundExecution
//        $this->assertEquals('check edited', $vendor->name, 'check if edited name is check edited');
//    }
//
//    public function test_update_vendor() {
//        $id = 56;
//        $vendor = $this->stbVendor->getSpecificById($id);
//        $vendor->name = 'check edited';
//        $this->stbVendor->update($vendor->id, $vendor->toArray());
//    }
//
//    public function test_delete_vendor() {
//        $id = 56;
//        $this->stbVendor->delete($id);
//    }
}
