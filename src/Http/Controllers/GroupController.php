<?php
/**
 * Class ResellerController
 * Aug 2021
 * 1:05 PM
 * @author Yoel Limbu <yoyal.limbu@gmail.com>
 */

namespace GeniussystemsNp\InventoryManagement\Http\Controllers;


use GeniussystemsNp\InventoryManagement\Facades\ImageUploadFacade;
use GeniussystemsNp\InventoryManagement\Repo\RepoInterface\GroupInterface;
use GeniussystemsNp\InventoryManagement\Repo\RepoInterface\MediaInterface;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

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
            $parameter["sort_by"] = $request->get("sort_by", "desc");
            $parameter["sort_field"] = $request->get("sort_field");
            $parameter["limit"] = $this->limit($request);
            $path = $request->url();

            $data = $this->group->getAllWithParam($parameter, $path);

            if (count($data) == 0) {

                return $this->message("No record found", 204, $this->context);
            }

            return $this->response($data, 200, $this->context);

        } catch (QueryException $exception) {
            return $this->message('Sql error', 400, $this->context);
        } catch (\Exception $ex) {
            return $this->message($ex->getMessage(), 500, $this->context);
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
        $token_params = $request->get('params');
        try {
            $this->validate($request, [
                    "name"    => "required|string",
                    "logo" => "required|image|mimes:jpeg",
                    'profile' => "sometimes",
                    'details' => "sometimes",
                    "status"  => "required|in:0,1",
            ]);
        } catch (\Exception $ex) {
            return $this->message($ex->response->original, 422, $this->context);
        }
        try {
            $req = $request->all();
            $req['logo'] = ImageUploadFacade::upload($request->file('logo'), "groups");
            $req['slug'] = str_slug($request->get('name'));
            $reseller = $this->group->create($req);
        }  catch (\Exception $ex) {
            return $this->message($ex->getMessage(), 500, $this->context);
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
            return $this->message('Sql error', 400, $this->context);
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

    public function update($id, Request $request,MediaInterface $media) {

        $this->context = "Group Update";
        $token_params = $request->get('params');
        try {
            $this->validate($request, [
                    "name"    => "required|string",
                    'profile' => "sometimes",
                    'details' => "sometimes",
                    "status"  => "required|in:0,1",
            ]);
        } catch (\Exception $ex) {
            return $this->message($ex->response->original, 422, $this->context);
        }
        try {
            $group = $this->group->getSpecificById($id);
            $req = $request->all();

            if ($request->hasFile('logo')) {

                $req['logo'] = ImageUploadFacade::upload($request->file('logo'), "groups");

                $media->delete($group->getOriginal('logo'));

            }

            $group = $this->group->update($group->id, $req);
            return $this->message("Group updated successfully", 200, $this->context);
        }catch (ModelNotFoundException $ex) {
            return $this->message("No record found", 204, $this->context);

        } catch (\Exception $ex) {
            return $this->message($ex->getMessage(), 500, $this->context);

        }


    }

    /**
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete($id) {
        $this->context = "Group Delete";
        try {
            $this->group->delete($id);
            return $this->message("Reseller deleted successfully", 200, $this->context);
        } catch (QueryException $exception) {
            return $this->message('Sql error', 400, $this->context);
        } catch (ModelNotFoundException $ex) {
            return $this->message("No record found", 204, $this->context);

        } catch (\Exception $ex) {
            return $this->message($ex->getMessage(), 500, $this->context);

        }

    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */


}
