<?php
/**
 * Class InventoryController
 * Aug 2021
 * 1:05 PM
 * @author Yoel Limbu <yoyal.limbu@gmail.com>
 */

namespace InventoryManagement\Http\Controllers;


use InventoryManagement\Models\Group;
use InventoryManagement\Repo\RepoInterface\GroupInterface;
use InventoryManagement\Repo\RepoInterface\InventoryInterface;
use InventoryManagement\Repo\RepoInterface\ModelInterface;
use InventoryManagement\Repo\RepoInterface\VendorInterface;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;


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
            $parameter["with_relationship"] = ['vendor:id,name', 'model:id,name', 'group:id,name'];
            $path = $request->url();

            $data = $this->inventory->getAllWithParam($parameter, $path);
            $data = $this->inventory->removeLinks($data);
            if (count($data['data']) == 0) {
                return $this->message('No record found', 204, $this->context);
            }
            return $this->response($data, 200, $this->context);
        } catch (QueryException $ex) {
            return $this->message($ex->getTraceAsString(), 521, $this->context, 'Something went wrong.');
        } catch (\Exception $ex) {
            return $this->message($ex->getTraceAsString(), 500, $this->context, 'Something went wrong.');
        }
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */

    public function unusedInventory($id) {
        $this->context = 'Display Unused Inventory';
        try {
            $data = $this->inventory->getUnusedInventories($id);
            return $this->response($data, 200, $this->context);
        } catch (\Exception $ex) {
            return $this->message($ex->getTraceAsString(), 500, $this->context, 'Something went wrong.');
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
                    "group_id"   => "sometimes|exists:groups,id",
                    "os_version" => "sometimes|max:16",
                    "batch_no"   => "sometimes:integer|max:11"
            ]);
        } catch (\Exception $ex) {
            return $this->message($ex->response->original, 422, $this->context);
        }
        try {
            $create = $request->except('macs');
            $create['serial'] = strtoupper(trim($create['serial']));
            $defaultGroup = App::make(GroupInterface::class)->getSpecificByIdOrSlug('default-group');

            $create['group_id'] = $defaultGroup->id;
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

        } catch (QueryException $ex) {
            return $this->message($ex->getTraceAsString(), 521, $this->context, 'Something went wrong.');
        } catch (\Exception $ex) {
            return $this->message($ex->getTraceAsString(), 500, $this->context, 'Something went wrong.');
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


            return $this->response($data, 200, $this->context);


        } catch (ModelNotFoundException $ex) {
            return $this->message($ex->getTraceAsString(), 204, $this->context, 'No record found');

        } catch (\Exception $ex) {
            return $this->message($ex->getTraceAsString(), 500, $this->context, 'Something went wrong.');

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

            $inventory = $this->inventory->getSpecificBySerial($serial);

            try {
                $this->validate($request, [
                        "macs"       => "required|array",
                        "macs.*"     => "required|string|max:17|unique:macs,mac,$inventory->id,inventory_id|regex:/^([a-fA-F0-9]{2}:[a-fA-F0-9]{2}:[a-fA-F0-9]{2}:[a-fA-F0-9]{2}:[a-fA-F0-9]{2}:[a-fA-F0-9]{2})$/",
                        "serial"     => "required|unique:inventory,serial, $inventory->id,id|max:16",
                        "vendor"     => "required|integer|exists:vendors,id",
                        "model"      => "required|integer|exists:models,id",
                        "group_id"   => "sometimes|integer|exists:groups,id",
                        "status"     => "sometimes|in:1,0",
                        "is_faulty"  => "sometimes|in:1,0",
                        "os_version" => "sometimes|max:16",
                        "batch_no"   => "sometimes:integer|max:11"
                ]);

            } catch (\Exception $ex) {
                return $this->message($ex->response->original, 422, $this->context);

            }

            $create = $request->except('macs', 'serial');
            $create['serial'] = strtoupper($request->input('serial'));
            if ($request->filled('status')) {

                $create['status'] = $request->input('status');
            }

            $inventory = $this->inventory->update($inventory->id, $create);
            $inventory->macs()->forceDelete();
            foreach ($request->input('macs') as $mac) {
                $create_mac = [
                        "inventory_id" => $inventory->id,
                        "mac"          => strtoupper($mac),
                        "created_at"   => Carbon::now(),
                        "updated_at"   => Carbon::now(),
                ];
                $create_list[] = $create_mac;
            }

            $inventory->macs()->insert($create_list);
            return $this->message("inventory updated Successfully", 200, $this->context);
        } catch (ModelNotFoundException $ex) {
            return $this->message($ex->getTraceAsString(), 204, $this->context, 'No record found');

        } catch (\Exception $ex) {
            return $this->message($ex->getTraceAsString(), 500, $this->context, 'Something went wrong.');
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
            if (!is_null($inventory->subscribers)) {
                return $this->message("inventory is still in use. Please make sure inventory is not assigned to any user before deleting.", 400, $this->context);
            }

            $this->inventory->delete($inventory->id);
            return $this->message("inventory deleted Successfully.", 200, $this->context);


        } catch (ModelNotFoundException $ex) {
            return $this->message($ex->getTraceAsString(), 204, $this->context, 'No record found');

        } catch (\Exception $ex) {
            return $this->message($ex->getTraceAsString(), 500, $this->context, 'Something went wrong.');

        }

    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function attachInventoryToGroup(Request $request) {
        $this->context = 'Attach Inventory to Group';

        try {
            $this->validate($request, [
                    "group_id"         => "required|integer|exists:groups,id",
                    'inventory_serial' => 'required|exists:inventory,serial'
            ]);

        } catch (\Exception $ex) {
            return $this->message($ex->response->original, 422, $this->context);

        }
        try {
            $inventory = $this->inventory->checkInventoryAssignedToGroup($request['inventory_serial'])->pluck('id');

            if (count($inventory) != 0) throw new \Exception();
        } catch (\Exception $ex) {
            return response()->json([
                                            "message" => "Some inventory are already assigned to group.",
                                            "data"    => $inventory
                                    ], 404);
        }
        try {
            $response = $this->inventory->attachInventoryGroup($request->all());

            return $this->message("Inventory successfully attached to reseller.", 200, $this->context);


        } catch (ModelNotFoundException $ex) {
            return $this->message($ex->getTraceAsString(), 204, $this->context, 'No record found');

        } catch (\Exception $ex) {
            return $this->message($ex->getTraceAsString(), 500, $this->context, 'Something went wrong.');

        }
    }

    public function getInventoriesUnassignedToReseller() {
        $this->context = 'Unsigned Inventories List';
        try {
            $data = $this->inventory->getUnassignedInventoriesToReseller();
            $data = $this->inventory->removeLinks($data);
            return $this->response($data, 200, "Unassigned Inventories List");
        } catch (\Exception $ex) {
            return $this->message($ex->getTraceAsString(), 500, $this->context, 'Something went wrong.');
        }

    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function detachInventoryFromGroup(Request $request) {
        $this->context = 'Detach Inventory from Groups';

        try {
            $this->validate($request, [
                    'inventory_serial' => 'required|exists:inventory,serial',
                    "group_id"         => "required|integer|exists:groups,id",
            ]);

        } catch (\Exception $ex) {
            return $this->message($ex->response->original, 422, $this->context);
        }
        try {
            $request = $request->all();

            $inventory_serial = $this->inventory->checkUnassignedInventoriesOfGroup($request['inventory_serial'], $request['group_id'])->pluck('serial')->toArray();


            if (count($inventory_serial) > 0) {
                $create = [
                        "inventory_serial" => $inventory_serial,
                        "group_id"         => null
                ];

                $this->inventory->attachInventoryGroup($create);

                return $this->message("inventories removed from group successfully.", 200, "Detach group from Inventory");


            }
            return $this->message("inventory is still assigned to a subscriber.", 400, "Detach group from Inventory");

        } catch (\Exception $ex) {

            return $this->message($ex->getTraceAsString(), 500, $this->context, 'Something went wrong.');

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
            return $this->message($ex->getTraceAsString(), 500, $this->context, 'Something went wrong.');
        }
    }

    public function show($serial, Request $request) {
        $this->context = 'Display inventory';

        try {
            $token_params = $request->get('params');

            $data = $this->inventory->getPublicSpecificBySerial($serial);

            if ($request->get('guard') === "reseller") {
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
            return $this->message($ex->getTraceAsString(), 500, $this->context, 'Something went wrong.');

        }
    }

    public function verify(Request $request) {
        $this->context = 'verify file';

        try {
            $this->validate($request, [
                    'file' => 'required|file|mimes:csv,txt',
            ]);

        } catch (\Exception $ex) {
            return $this->message($ex->response->original, 422, $this->context);

        }
        try {
            $createList = [];

            //$reseller_id = Input::get('owner', null);
            $path = $request->file('file')->getRealPath();
            $file = fopen($path, "r");
            $key = -1;
            $total = 0;
            $check_array = ['vendor', 'serial', 'model', 'group', 'mac1', 'mac2', 'mac3', 'mac4', 'mac5'];
            $error_list = [];
            while (!feof($file)) {
                $total++;
                $d = fgetcsv($file);
                if ($d) {
                    if ($key == -1) {
                        $c = array_diff($d, $check_array);

                        if (!empty($c)) {
                            return $this->message("Please upload the Excel Sheet in the format suggested.", 422, $this->context);

                        }
                    }
                    else {

                        $create = array_combine($check_array, $d);
                        $vendorSlug= str_slug($create['vendor']);
                        $vendor = $this->vendor->getSpecificByColumnValue('slug', $vendorSlug);

                        if (is_null($vendor)) {
                            $create['reason'] = [
                                    "vendor" => [
                                            "Given vendor not found"
                                    ]
                            ];
                            $error_list[] = $create;
                            continue;
                        }
                        $modelSlug = str_slug($create['model']);
                        $model = $this->model->getSpecificByColumnValue('slug', $modelSlug);

                        if (is_null($model)) {
                            $create['reason'] = [
                                    "model" => [
                                            "Given model not found"
                                    ]
                            ];
                            $error_list[] = $create;
                            continue;
                        }

                        if($model->vendor->slug !== $vendor->slug)
                        {
                            $create['reason'] = [
                                "model" => [
                                    "model is not of the given vendor"
                                ]
                            ];
                            $error_list[] = $create;
                            continue;
                        }

                        $groupSlug = str_slug($create['group']);
                        $group = Group::where('slug', '=', $groupSlug)->firstOrFail();
                        if (is_null($group)) {
                            $create['reason'] = [
                                    "group" => [
                                            "Given Group not found"
                                    ]
                            ];
                            $error_list[] = $create;
                            continue;
                        }

                        $validator = Validator::make($create, [
                                "mac1"   => "required|string|max:17|unique:macs,mac|regex:/^([a-fA-F0-9]{2}:[a-fA-F0-9]{2}:[a-fA-F0-9]{2}:[a-fA-F0-9]{2}:[a-fA-F0-9]{2}:[a-fA-F0-9]{2})$/",
                                "mac2"   => "sometimes|string|max:17|unique:macs,mac|regex:/^([a-fA-F0-9]{2}:[a-fA-F0-9]{2}:[a-fA-F0-9]{2}:[a-fA-F0-9]{2}:[a-fA-F0-9]{2}:[a-fA-F0-9]{2})$/",
                                "mac3"   => "sometimes|string|max:17|unique:macs,mac|regex:/^([a-fA-F0-9]{2}:[a-fA-F0-9]{2}:[a-fA-F0-9]{2}:[a-fA-F0-9]{2}:[a-fA-F0-9]{2}:[a-fA-F0-9]{2})$/",
                                "mac4"   => "sometimes|string|max:17|unique:macs,mac|regex:/^([a-fA-F0-9]{2}:[a-fA-F0-9]{2}:[a-fA-F0-9]{2}:[a-fA-F0-9]{2}:[a-fA-F0-9]{2}:[a-fA-F0-9]{2})$/",
                                "mac5"   => "sometimes|string|max:17|unique:macs,mac|regex:/^([a-fA-F0-9]{2}:[a-fA-F0-9]{2}:[a-fA-F0-9]{2}:[a-fA-F0-9]{2}:[a-fA-F0-9]{2}:[a-fA-F0-9]{2})$/",
                                "serial" => "required|string|unique:inventory,serial"
                        ]);
                        if ($validator->fails()) {
                            $create['reason'] = $validator->errors();

                            $error_list[] = $create;

                            continue;
                        }
                        $macs = array_filter([$create['mac1'], $create['mac2'], $create['mac3'], $create['mac4'], $create['mac5']]);

                        $create['vendor'] = $vendor['id'];
                        $create['model'] = $model['id'];
                        $create['group'] = $group['id'];

                        $create['macs'] = $macs;
                        $create['serial'] = strtoupper(trim($create['serial']));
                        unset($create['mac1'], $create['mac2'], $create['mac3'], $create['mac4'], $create['mac5']);
                        $createList[] = $create;
                    }
                    $key++;
                }

            }

            fclose($file);

            DB::beginTransaction();
            $total = $total - 1;
            DB::commit();
            return $this->response([
                                           "error_data" => $error_list,
                                           "data"       => $createList,
                                           "total"      => $total,
                                           "success"    => $key,
                                   ], 200, $this->context);

        } catch (ModelNotFoundException $ex) {
            return response()->json([
                                            "message" => $ex->getMessage()
                                    ], 404);
        } catch (\Exception $ex) {
            return response()->json([
                                            "message" => $ex->getMessage()
                                    ], 500);
            //            return $this->message("Something went wrong. File may have been already uploaded or has mac or  serial which already exists.", 500, $this->context);

        }
    }

    public function load(Request $request) {
        try {
            $this->validate($request, [
                    'ipcams'          => 'required|array',
                    'ipcams.*.vendor' => 'required',
                    'ipcams.*.model'  => 'required',
                    "ipcams.*.group"  => "sometimes|integer|exists:groups,id",
                    "ipcams.*.macs"   => "required|array",
                    "ipcams.*.macs.*" => "required|string|max:17|unique:macs,mac|regex:/^([a-fA-F0-9]{2}:[a-fA-F0-9]{2}:[a-fA-F0-9]{2}:[a-fA-F0-9]{2}:[a-fA-F0-9]{2}:[a-fA-F0-9]{2})$/",
            ]);

        } catch (\Exception $ex) {
            return $this->message($ex->response->original, 422, $this->context);

        }

        try {
            $ipcams = $request['ipcams'];
            $latest_batch = $this->inventory->getLatestBatchNo();
            DB::beginTransaction();
            $allCams = [];
            foreach ($ipcams as $key => $ipcam) {
                $group = isset($ipcam['group']) && $ipcam['group'] !== '' ? $ipcam['group'] : '';
                $allCams[$key]['serial'] = strtoupper(trim($ipcam['serial']));
                $allCams[$key]['batch_no'] = $latest_batch++;
                $allCams[$key]['group_id'] = $group;
                $allCams[$key]['vendor'] = $ipcam['vendor'];
                $allCams[$key]['model'] = $ipcam['model'];
                $inventory = $this->inventory->create(array_only($ipcam, ['vendor', 'model', 'group_id', 'serial', 'batch_no']));
                $createList = [];
                foreach ($ipcam['macs'] as $mac) {
                    $create = [
                            'mac'          => strtoupper($mac),
                            'inventory_id' => $inventory['id'],
                            'created_at'   => Carbon::now(),
                            'updated_at'   => Carbon::now()
                    ];
                    $createList[] = $create;
                }
                $inventory->macs()->insert($createList);
            }
            DB::commit();
            return $this->message("File imported successfully.", 200, $this->context);

        } catch (ModelNotFoundException $ex) {
            return $this->message($ex->getTraceAsString(), 204, $this->context, 'No record found');

        } catch (QueryException $ex) {

            return $this->message($ex->getTraceAsString(), 521, $this->context, 'Something Went Wrong');

        } catch (\Exception $ex) {
            echo '<pre>';
            print_r($ex);
            echo '</pre>';
            die;
            return $this->message("Something went wrong. File may have been already uploaded or has mac or  serial which already exists.", 500, $this->context);

        }
    }
}
