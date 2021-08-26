<?php


namespace GeniussystemsNp\InventoryManagement\Http\Controllers;


use GeniussystemsNp\InventoryManagement\Repo\RepoInterface\VendorInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VendorController extends Controller
{

    protected $vendor;

    public function __construct(VendorInterface $vendor)
    {
        $this->vendor = $vendor;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function index(Request $request)
    {
        $this->context = "Inventory Vendor Display List";


        try {
            $this->validate($request, [
                "filter_field" => "sometimes|string",
                "filter_value" => "required_with:filter_field|string",
                "q" => "sometimes",
            ]);
        } catch (\Exception $ex) {
            return $this->message($ex->response->original, 422, $this->context);
        }

        try {
            $parameter = $request->all();
            $parameter["sort_by"] = $request->get("sort_by", "desc");
            $parameter["sort_field"] = $request->get("sort_field");
            $parameter["limit"] = $this->limit($request);
            $path = $request->url();
            $data = $this->vendor->getAllWithParam($parameter, $path);

            if (count($data) == 0) {

                return $this->message("No record found", 204, $this->context);
            }

            return $this->response($data, 200, $this->context);

        } catch (\Exception $ex) {
            return $this->message($ex->getMessage(), 500, $this->context);
        }
    }

    /**
     *
     * Store Inventory Vendor
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function store(Request $request)
    {

        $this->context = "Create Vendor";

        try {
            $this->validate($request, [
                "name" => "required|string|unique:vendors|max:16"
            ]);

        } catch (\Exception $ex) {

            return $this->message($ex->response->original, 422, $this->context);

        }

        try {
            $create = $request->all();

            $create['slug'] = str_slug($create['name']);

            $vendor = $this->vendor->create($create);

            return $this->message("inventory vendor created successfully", 200, $this->context);

        } catch (\Exception $ex) {
            return $this->message($ex->getMessage(), 500, $this->context);

        }


    }

    /**
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function adminShow($id)
    {
        $this->context = "Inventory Vendor Display";

        try {

            $data = $this->vendor->getSpecificByIdOrSlug($id);

            return $this->response($data, 200, $this->context);

        } catch (ModelNotFoundException $ex) {
            return $this->message("No record found", 204, $this->context);


        } catch (\Exception $ex) {
            return $this->message($ex->getMessage(), 500, $this->context);

        }


    }

    /**
     * @param $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function update($id, Request $request)
    {

        $this->context = "Inventory Vendor Update";

        /**
         * Start DB transaction to ensure the operation is reversed in case not successfully committed.
         *
         */
        try {
            $vendor = $this->vendor->getSpecificByIdOrSlug($id);

            try {
                $this->validate($request, [
                    "name" => "required|string|unique:vendors,name,$vendor->id,id|max:16",
                ]);

            } catch (\Exception $ex) {
                return $this->message($ex->response->original, 422, $this->context);

            }
            $vendor = $this->vendor->update($vendor->id, $request->only('name'));
            return $this->message("Inventory vendor updated successfully", 200, $this->context);


        } catch (ModelNotFoundException $ex) {
            return $this->message("No record found", 204, $this->context);

        } catch (\Exception $ex) {
            return $this->message($ex->getMessage(), 500, $this->context);

        }
    }

    /**
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */

    public function delete($id)
    {
        $this->context = "Inventory Vendor Delete";

        try {

            $vendor = $this->vendor->getSpecificByIdOrSlug($id);
            if(isset($vendor->inventory) && count($vendor->inventory) >0)
            {
                throw new \Exception("This vendor still has inventories. Please delete related inventories first.")  ;
            }else{
                $this->vendor->delete($vendor->id);
            }
            return $this->message("Inventory vendor deleted successfully", 200, $this->context);


        } catch (ModelNotFoundException $ex) {
            return $this->message("No record found", 204, $this->context);

        } catch (\Exception $ex) {
            return $this->message($ex->getMessage(), 500, $this->context);

        }

    }






}
