<?php
/**
 * Created by PhpStorm.
 * @author Kabina Suwal <kabina.suwal92@gmail.com>
 * Date: 21-Jun-18
 * Time: 01:14 PM
 */


namespace InventoryManagement\Http\Controllers;


use InventoryManagement\Repo\RepoInterface\ModelInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ModelController extends Controller {

    protected $model;

    public function __construct(ModelInterface $model) {
        $this->model = $model;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function index(Request $request) {
        $this->context = "Inventory Model Display List";

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
            $parameter["with_relationship"] = ['vendor:id,name'];
            $path = $request->url();

            $data = $this->model->getAllWithParam($parameter, $path);
            $data = $this->model->removeLinks($data);
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
     * Store Inventory Model
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function store(Request $request) {
        $this->context = "Inventory Model Create";

        try {
            $this->validate($request, [
                    "name"      => "required|string|unique:models|max:16",
                    "vendor_id" => "required|exists:vendors,id"
            ]);

        } catch (\Exception $ex) {

            return $this->message($ex->response->original, 422, $this->context);

        }

        try {
            $create = $request->all();
            $create['slug'] = str_slug($create['name']);
            $model = $this->model->create($create);
            $this->unsetTransaction();
            DB::commit();

            return $this->message("inventory model created successfully", 200, $this->context);


        } catch (\Exception $ex) {
            return $this->message($ex->getTraceAsString(), 500, $this->context, 'Something went wrong.');

        }


    }

    /**
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function adminShow($id) {

        $this->context = "inventory Model Display";

        try {

            $data = $this->model->getSpecificByIdOrSlug($id);

            Log::info("inventory Model Display", [
                    "status" => "200",
                    //"data" => serialize($data),
            ]);
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
        $this->context = "inventory Model Update";


        /**
         * Start DB transaction to ensure the operation is reversed in case not successfully committed.
         *
         */
        try {
            $model = $this->model->getSpecificByIdOrSlug($id);

            try {
                $this->validate($request, [
                        "name"      => "required|string|unique:models,name,$model->id,id|max:16",
                        "vendor_id" => "required|exists:vendors,id"
                ]);

            } catch (\Exception $ex) {
                return $this->message($ex->response->original, 422, $this->context);

            }


            $model = $this->model->update($model->id, $request->only('name', 'vendor_id'));

            return $this->message("inventory model updated successfully", 200, $this->context);


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
        $this->context = "inventory Model Delete";

        try {
            $model = $this->model->getSpecificById($id);

            if (count($model->inventories) > 0) {
                return $this->response([
                                               "message" => "This model is still being used by an inventory item. Please delete related inventories first."
                                       ], 400, $this->context);
            }
            $this->model->delete($model->id);
            return $this->message("Inventory model deleted successfully", 200, $this->context);

        } catch (ModelNotFoundException $ex) {
            return $this->message($ex->getTraceAsString(), 204, $this->context, 'No record found');

        } catch (\Exception $ex) {
            return $this->message($ex->getTraceAsString(), 500, $this->context, 'Something went wrong.');
        }

    }

}
