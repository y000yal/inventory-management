<?php
/**
 * Class ResellerController
 * Aug 2021
 * 1:05 PM
 * @author Yoel Limbu <yoyal.limbu@gmail.com>
 */

namespace InventoryManagement\Http\Controllers;


use InventoryManagement\Facades\ImageUploadFacade;
use InventoryManagement\Repo\RepoInterface\GroupInterface;
use InventoryManagement\Repo\RepoInterface\MediaInterface;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;

class GroupController extends Controller {
    protected $group;

    public function __construct(GroupInterface $group) {
        $this->group = $group;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function index(Request $request) {

        $this->context = "Group Display List";

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
            $path = $request->url();
            $data = $this->group->getAllWithParam($parameter, $path);
            $data = $this->group->removeLinks($data);

            if (count($data['data']) == 0) {
                return $this->message("No record found", 204, $this->context);
            }
            return $this->response($data, 200, $this->context);

        } catch (QueryException $exception) {
            return $this->message($exception->getTraceAsString(), 521, $this->context, 'Something went wrong.');
        } catch (\Exception $ex) {
            return $this->message($ex->getTraceAsString(), 500, $this->context, 'Something went wrong.');
        }


    }

    /**
     *
     * Store reseller
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */


    public function store(Request $request) {
        $this->context = "Add Group";

        try {
            $this->validate($request, [
                    "name"    => "required|string|unique:groups,name",
                    "logo"    => "required|image",
                    'profile' => "sometimes",
                    'details' => "sometimes",
                    "status"  => "sometimes|in:0,1",
            ]);
        } catch (\Exception $ex) {
            return response()->json([
                                            "status"  => "422",
                                            "message" => $ex->response->original,
                                    ], 422);
        }
        try {
            $req = $request->all();
            $req['logo'] = ImageUploadFacade::upload($request->file('logo'), "groups");
            $req['slug'] = str_slug($request->get('name'));
            $this->group->create($req);
        } catch (\Exception $ex) {
            return $this->message($ex->getTraceAsString(), 500, $this->context, 'Something went wrong.');
        }
        return response()->json([
                                        "message" => "Group Created Successfully"
                                ], 200);

    }

    /**
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     * @throws AuthorizationException
     */
    public function adminShow($id) {
        $this->context = "Group Display";

        try {
            $data = $this->group->getSpecificById($id);
            return $this->response($data, 200, $this->context);
        } catch (QueryException $exception) {
            return $this->message($exception->getTraceAsString(), 521, $this->context, 'Something went wrong.');

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

    public function update($id, Request $request, MediaInterface $media) {

        $this->context = "Group Update";
        try {
            $this->validate($request, [
                    "name"    => "required|string",
                    'profile' => "sometimes",
                    'details' => "sometimes",
                    "status"  => "sometimes|in:0,1",
            ]);
        } catch (\Exception $ex) {
            return $this->message($ex->response->original, 422, $this->context);
        }
        try {
            $group = $this->group->getSpecificById($id);
            $req = $request->except('logo');

            if ($request->hasFile('logo')) {
                $req['logo'] = ImageUploadFacade::upload($request->file('logo'), "groups");
                $media->delete($group->getOriginal('logo'));
            }
            elseif (filter_var($request->get('logo'), FILTER_VALIDATE_URL)) {
                $base_url = config('Config.channel_base_url') . "/images/";
                $url = $request->get('logo');
                $id = str_after($url, $base_url);
                $req['logo'] = $id;
            }

            $group = $this->group->update($group->id, $req);
            return $this->message("Group updated successfully", 200, $this->context);
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
        $this->context = "Group Delete";
        try {
            $group = $this->group->getSpecificById($id);
            if ($group->slug === 'default-group')
                return response()->json(['message' => 'Sorry default group cannot be deleted.'], 400);
            if (count($group->inventories) > 0)
                return response()->json(['message' => 'This group still contains inventories. Please delete related inventories first.'], 400);
            $this->group->delete($group->id);
            return $this->message("Group deleted successfully", 200, $this->context);
        }
        catch (QueryException $exception) {
            return $this->message($exception->getTraceAsString(), 521, $this->context, 'Something went wrong.');

        } catch (ModelNotFoundException $ex) {
            return $this->message($ex->getTraceAsString(), 204, $this->context, 'No record found');
        } catch (\Exception $ex) {
            return $this->message($ex->getTraceAsString(), 500, $this->context, 'Something went wrong.');
        }

    }

}
