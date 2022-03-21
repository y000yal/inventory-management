<?php


namespace InventoryManagement\Http\Controllers;


use InventoryManagement\Repo\RepoInterface\VendorInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VendorController extends Controller {

    protected $vendor;

    public function __construct(VendorInterface $vendor) {
        $this->vendor = $vendor;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function index(Request $request) {
        $this->context = "vendor Display List";


        try {
            $this->validate($request, [
                "filter_field" => "sometimes|string",
                "filter_value" => "required_with:filter_field|string",
                "q"            => "sometimes",
            ]);
        } catch (\Exception $ex) {
            return $this->message($ex->response->original, 422, $this->context);
        }

        try {
            $parameter = $request->all();
            $parameter["limit"] = $this->limit($request);
            $parameter["wih_relationship"] = ['inventoryModels:vendor_id,id,name,slug'];
            $path = $request->url();

            $data = $this->vendor->getAllWithParam($parameter, $path);
            $data = $this->vendor->removeLinks($data);
            if (count($data) == 0) {

                return $this->message("No record found", 204, $this->context);
            }

            return $this->response($data, 200, $this->context);

        } catch (\Exception $ex) {
            return $this->message($ex->getTraceAsString(), 500, $this->context, 'Something went wrong.');

        }
    }

    /**
     *
     * Store vendor
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function store(Request $request) {

        $this->context = "Create Vendor";
        $regex = config("config.password_regex", "/^.*(?=.{3,})(?=.*[a-zA-Z])(?=.*[0-9])(?=.*[\d\X])(?=.*[!$#%@]).*$/");

        try {
            $this->validate($request, [
                "name" => "required|string|unique:vendors,name|max:16"
            ]);

        } catch (\Exception $ex) {

            return $this->message($ex->response->original, 422, $this->context);

        }

        try {
            $create = $request->all();

            $create['slug'] = str_slug($create['name']);

            $vendor = $this->vendor->create($create);

            return $this->message("vendor created successfully", 200, $this->context);

        } catch (\Exception $ex) {
            return $this->message($ex->getTraceAsString(), 500, $this->context, 'Something went wrong.');

        }


    }

    /**
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function adminShow($id) {
        $this->context = "vendor Display";

        try {

            $data = $this->vendor->getSpecificByIdOrSlug($id);

            return $this->response($data, 200, $this->context);

        } catch (ModelNotFoundException $ex) {
            return $this->message($ex->getTraceAsString(), 204, $this->context, 'No record found');

        } catch (\Exception $ex) {
            return $this->message($ex->getTraceAsString(), 500, $this->context, 'Something went wrong.');

        }


    }

    /**
     * @param $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function update($id, Request $request) {

        $this->context = "vendor Update";

        /**
         * Start DB transaction to ensure the operation is reversed in case not successfully committed.
         *
         */
        try {
            $vendor = $this->vendor->getSpecificByIdOrSlug($id);

            try {
                $this->validate($request, [
                    "name"        => "required|string|unique:vendors,name,$vendor->id,id|max:16",
                    "description" => "required",
                ]);

            } catch (\Exception $ex) {
                return $this->message($ex->response->original, 422, $this->context);

            }
            $vendor = $this->vendor->update($vendor->id, $request->all());
            return $this->message("vendor updated successfully", 200, $this->context);


        } catch (ModelNotFoundException $ex) {
            return $this->message($ex->getTraceAsString(), 204, $this->context, 'No record found');

        } catch (\Exception $ex) {
            return $this->message($ex->getTraceAsString(), 500, $this->context, 'Something went wrong.');

        }
    }

    /**
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */

    public function delete($id) {
        $this->context = "vendor Delete";

        try {

            $vendor = $this->vendor->getSpecificById($id);

            if (count($vendor->inventories) > 0) {
                return $this->response([
                                           "message" => "This vendor is still being used by an inventory item. Please delete related inventories first."
                                       ], 400, $this->context);
            }
            $this->vendor->delete($vendor->id);

            return $this->message("vendor deleted successfully", 200, $this->context);

        } catch (ModelNotFoundException $ex) {
            return $this->message($ex->getTraceAsString(), 204, $this->context, 'No record found');

        } catch (\Exception $ex) {
            return $this->message($ex->getTraceAsString(), 500, $this->context, 'Something went wrong.');

        }

    }


}
