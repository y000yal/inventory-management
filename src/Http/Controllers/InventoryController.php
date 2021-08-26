<?php
/**
 * Created by PhpStorm.
 * @author Kabina Suwal <kabina.suwal92@gmail.com>
 * Date: 21-Jun-18
 * Time: 02:48 PM
 */

namespace GeniussystemsNp\InventoryManagement\Http\Controllers;


use GeniussystemsNp\InventoryManagement\Repo\RepoInterface\InventoryInterface;
use GeniussystemsNp\InventoryManagement\Repo\RepoInterface\ModelInterface;
use GeniussystemsNp\InventoryManagement\Repo\RepoInterface\VendorInterface;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use LocalParse;

use Maatwebsite\Excel\Facades\Excel;

class InventoryController extends Controller {
    protected $inventory;
    protected $model;
    protected $vendor;

    public function __construct(InventoryInterface $inventory, ModelInterface $model, VendorInterface $vendor) {
        $this->inventory = $inventory;
        $this->vendor = $vendor;
        $this->model = $model;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function index(Request $request) {

        $this->context = 'Display Inventory List';
        try {
            $token_params = $request->get('params');

            if ($request->get('guard') == "reseller") {
                $reseller_id = is_null($token_params->reseller_id) ? $token_params->id : $token_params->reseller_id;

            }
            else {
                $reseller_id = "";

            }


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
            $parameter["sort_by"] = $request->get("sort_by", "desc");
            $parameter["sort_field"] = $request->get("sort_field");
            $parameter["limit"] = $this->limit($request);
            $path = $request->url();

            $data = $this->inventory->getAllWithParamByReseller($reseller_id, $parameter, $path);

            if (count($data) == 0) {
                return $this->message('No record found', 204, $this->context);
            }
            return $this->response($data, 200, $this->context);
        } catch (\Exception $ex) {
            return $this->message($ex->getMessage(), 500, $this->context);
        }
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */

    public function unusedInventory() {
        $this->context = 'Display Unused Inventory';

        try {
            $request = App::make(Request::class);
            $token_params = $request->get('params');

            $reseller_id = is_null($token_params->reseller_id) ? $token_params->id : $token_params->reseller_id;
            $data = $this->inventory->getUnusedInventories($reseller_id);
            return $this->response($data, 200, $this->context);
        } catch (\Exception $ex) {
            return $this->message($ex->getMessage(), 500, $this->context);

        }

    }

    /**
     *
     * Store inventory
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function store(Request $request) {


        $this->context = 'Store Inventory';

        try {
            $this->validate($request, [
                "macs"       => "required|array",
                "macs.*"     => "required|string|max:17|unique:macs,mac|regex:/^([a-fA-F0-9]{2}:[a-fA-F0-9]{2}:[a-fA-F0-9]{2}:[a-fA-F0-9]{2}:[a-fA-F0-9]{2}:[a-fA-F0-9]{2})$/",
                "serial"     => "required|unique:inventory,serial|max:17|alpha_num",
                "vendor"     => "required|integer|exists:vendors,id",
                "model"      => "required|integer|exists:models,id",
                "owner" => "sinteger|exists:groups,id",
                "os_version" => "sometimes|max:16",
                "batch_no"   => "sometimes:integer|max:11"
            ]);

        } catch (\Exception $ex) {
            return $this->message($ex->response->original, 422, $this->context);
        }
        try {
            $create = $request->except('macs');

            $create['serial'] = strtoupper(trim($create['serial']));

            $inventory = $this->inventory->create($create);

            $macs = $request->input('macs');

            foreach ($macs as $mac) {
                $create = [
                    'mac'          => strtoupper($mac),
                    'inventory_id' => $inventory['id'],
                    'created_at'   => Carbon::now(),
                    'updated_at'   => Carbon::now()
                ];

                $createList[] = $create;
            }

            $inventory->macs()->insert($createList);


            return $this->message("Inventory Added Successfully", 200, $this->context);

        } catch (\Exception $ex) {
            return $this->message($ex->getMessage(), 500, $this->context);

        }


    }

    /**
     * @param $serial
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function adminShow($serial, Request $request) {
        $this->context = 'Display Inventory';

        try {
            $token_params = $request->get('params');

            $data = $this->inventory->getSpecificBySerial($serial);

            if ($request->get('guard') == "reseller") {
                if ($token_params->reseller_id) {
                    $reseller_id = $token_params->reseller_id;
                }
                else {
                    $reseller_id = $token_params->id;
                }

                if ($data['owner'] != $reseller_id) {
                    throw  new ModelNotFoundException();
                }
            }

            return $this->response($data, 200, $this->context);


        } catch (ModelNotFoundException $ex) {
            return $this->message('No record found', 204, $this->context);


        } catch (\Exception $ex) {
            return $this->message($ex->getMessage(), 500, $this->context);

        }


    }

    /**
     * @param $mac
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function update($serial, Request $request) {
        $this->context = 'Update Inventory';


        try {
            $reseller_id = "";
            $token_params = $request->get('params');

            $inventory = $this->inventory->getSpecificBySerial($serial);
            try {
                $this->validate($request, [
                    "macs"       => "required|array",
                    "macs.*"     => "required|string|max:17|unique:macs,mac,$inventory->id,inventory_id|regex:/^([a-fA-F0-9]{2}:[a-fA-F0-9]{2}:[a-fA-F0-9]{2}:[a-fA-F0-9]{2}:[a-fA-F0-9]{2}:[a-fA-F0-9]{2})$/",
                    "serial"     => "required|unique:inventory,serial, $inventory->id,id|max:16",
                    "vendor"     => "required|integer|exists:vendors,id",
                    "model"      => "required|integer|exists:models,id",
                    "status"     => "sometimes|in:active,inactive,faulty",
                    "os_version" => "sometimes|max:16",
                    "batch_no"   => "sometimes:integer|max:11"
                ]);

            } catch (\Exception $ex) {
                return $this->message($ex->response->original, 422, $this->context);

            }

            $create = $request->only('vendor', 'model', 'os_version', 'batch_no');
            $create['serial'] = strtoupper($request->input('serial'));
            if ($request->filled('status')) {

                $create['status'] = $request->input('status');
            }
            $inventory = $this->inventory->update($inventory->id, $create);
            //            $inventory->macs()->delete();
            foreach ($request->input('macs') as $mac) {
                $create_mac = [
                    "inventory_id" => $inventory->id,
                    "mac"          => strtoupper($mac),
                    "created_at"   => Carbon::now(),
                    "updated_at"   => Carbon::now(),

                ];
                $create_list[] = $create_mac;

            }
            //            $inventory->macs()->insert($create_list);
            return $this->message("inventory updated Successfully", 200, $this->context);


        } catch (ModelNotFoundException $ex) {
            return $this->message('No record found', 204, $this->context);

        } catch (\Exception $ex) {
            return $this->message($ex->getMessage(), 500, $this->context);

        }
    }

    /**
     * @param $serial
     * @return \Illuminate\Http\JsonResponse
     */

    public function delete($serial) {
        $this->context = 'Delete Inventory';

        try {
            $inventory = $this->inventory->getSpecificBySerial($serial);
            if (is_null($inventory->inventory_user)) {
                $this->inventory->delete($inventory->id);
            }
            else {
                return $this->message("inventory is still in use. Please make sure inventory is not assigned to any user before deleting.", 500, $this->context);
            }

            return $this->message("inventory deleted Successfully.", 200, $this->context);


        } catch (ModelNotFoundException $ex) {
            return $this->message('No record found', 204, $this->context);

        } catch (\Exception $ex) {
            return $this->message($ex->getMessage(), 500, $this->context);

        }

    }

    public function attachInventoryToReseller(Request $request) {
        $this->context = 'Attach Inventory to Reseller';

        try {
            $this->validate($request, [
                'inventories'   => 'required|array',
                "owner"         => "required|integer|exists:reseller,id",
                'inventories.*' => 'required|exists:inventories,id'
            ]);

        } catch (\Exception $ex) {
            return $this->message($ex->response->original, 422, $this->context);

        }
        try {
            $inventory = $this->inventory->checkInventoryAssignedToReseller($request['inventories'])->pluck('id');
            if (count($inventory) != 0) throw new \Exception();
        } catch (\Exception $ex) {
            Log::error("Inventory Import", [
                "status"  => "404",
                "message" => "Some inventory are already assigned to reseller.",

            ]);

            return response()->json([
                                        "message" => "Some inventory are already assigned to reseller.",
                                        "data"    => $inventory
                                    ], 404);
        }
        try {
            $response = $this->inventory->attachInventoryReseller($request->all());

            return $this->message("Inventory successfully attached to reseller.", 200, $this->context);


        } catch (ModelNotFoundException $ex) {
            return response()->json([
                                        "message" => $ex->getMessage()
                                    ], 404);
        } catch (\Exception $ex) {
            return $this->message($ex->getMessage(), 500, $this->context);

        }
    }

    public function getInventoriesUnassignedToReseller() {
        try {
            $data = $this->inventory->getUnassignedInventoriesToReseller();
            return $this->response($data, 200, "Unassigned Inventories List");
        } catch (\Exception $ex) {
            $this->message($ex->getMessage(), 500, "Unsigned Inventories List");
        }

    }

    /**
     * Detach inventory from reseller.
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function detachInventoryFromReseller(Request $request) {
        $this->context = 'Detach Inventory from Reseller';

        try {
            $this->validate($request, [
                'inventories'   => 'required|array',
                'inventories.*' => 'required|exists:inventories,id',
                "owner"         => "required|integer|exists:reseller,id",
            ]);

        } catch (\Exception $ex) {
            return $this->message($ex->response->original, 422, $this->context);
        }

        try {
            /**
             * Get list of inventories which belongs to Reseller and is unassigned to subscribers.
             */
            $inventoryIds = $this->inventory->checkUnassignedInventoriesOfReseller($request['inventories'], $request['owner'])->pluck('id')->toArray();
            /**
             *
             */
            if (count(array_diff($request['inventories'], $inventoryIds)) == 0) {
                $create = [
                    "inventories" => $inventoryIds,
                    "owner"       => null
                ];
                $this->inventory->attachInventoryReseller($create);

                return $this->message("inventories removed from reseller successfully.", 200, "Detach Reseller from Inventory");


            }
            else {
                return $this->message("Some inventories are still assigned to subscribers.", 403, "Detach Reseller from Inventory");
            }

        } catch (\Exception $ex) {

            return $this->message($ex->getMessage(), 500, "Detach Reseller from Inventory");

        }


    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function getAllInventoryList(Request $request) {

        $this->context = 'Display Inventory List';
        try {
            $token_params = $request->get('params');

            if ($request->get('guard') == "reseller") {
                $reseller_id = is_null($token_params->reseller_id) ? $token_params->id : $token_params->reseller_id;

            }
            else {
                $reseller_id = "";

            }


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
            $parameter["sort_by"] = $request->get("sort_by", "desc");
            $parameter["sort_field"] = $request->get("sort_field");
            $parameter["limit"] = $this->limit($request);
            $path = $request->url();
            $data = $this->inventory->getAllInventoryList($reseller_id, $parameter, $path);

            if (count($data) == 0) {
                return $this->message('No record found', 204, $this->context);
            }
            return $this->response($data, 200, $this->context);
        } catch (\Exception $ex) {
            return $this->message($ex->getMessage(), 500, $this->context);
        }
    }

    function timeago($date) {
        $timestamp = strtotime($date);

        $strTime = ["second", "minute", "hour", "day", "month", "year"];
        $length = ["60", "60", "24", "30", "12", "10"];

        $currentTime = time();
        if ($currentTime >= $timestamp) {
            $diff = time() - $timestamp;
            for ($i = 0; $diff >= $length[$i] && $i < count($length) - 1; $i++) {
                $diff = $diff / $length[$i];
            }

            $diff = round($diff);
            return $diff . " " . $strTime[$i] . "s ago ";
        }
    }

    public function show($serial, Request $request) {
        $this->context = 'Display inventory';

        try {
            $token_params = $request->get('params');

            $data = $this->inventory->getPublicSpecificBySerial($serial);

            if ($request->get('guard') == "reseller") {
                if ($token_params->reseller_id) {
                    $reseller_id = $token_params->reseller_id;
                }
                else {
                    $reseller_id = $token_params->id;
                }

                if ($data['owner'] != $reseller_id) {
                    throw  new ModelNotFoundException();
                }
            }
            return $this->response($data, 200, $this->context);
        } catch (ModelNotFoundException $ex) {
            return $this->message('No record found', 204, $this->context);
        } catch (\Exception $ex) {
            return $this->message($ex->getMessage(), 500, $this->context);

        }
    }
}
