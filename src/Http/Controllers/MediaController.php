<?php
/**
 * Class InventoryController
 * Aug 2021
 * 1:05 PM
 * @author Yoel Limbu <yoyal.limbu@gmail.com>
 */

namespace InventoryManagement\Http\Controllers;



use App\Repo\RepoInterface\MediaInterface;
use InventoryManagement\Repo\RepoInterface\MediaInterface as RepoInterfaceMediaInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class MediaController extends Controller {
    /**
     * @var MediaInterface
     */
    private $media,$docs;

    /**
     * MediaController constructor.
     * @param MediaInterface $media
     */
    public function __construct(RepoInterfaceMediaInterface $media) {
        $this->media = $media;
    }

    public function getImage($id) {
        $this->context = 'Image';
        try {
            $data = $this->media->getSpecificById($id);

            if (!empty($data)) {
                Log::info("Channel Image", [
                        "status" => "200",
                ]);
                $file = Storage::disk('minio')->get($data->path);
                return response($file, 200)->header('Content-Type', 'image/jpeg');
            }
            else {
                return $this->message("No record found", 204, $this->context);
            }

        } catch (ModelNotFoundException $ex) {
            return $this->message($ex->getTraceAsString(), 204, $this->context, 'No record found');

        } catch (\Exception $ex) {
            return $this->message($ex->getTraceAsString(), 500, $this->context, 'Something went wrong.');

        }

    }

    public function imageTransfer(Request $request) {
        //bucket path
        // $filePath = '/apks/' . $app;
        //dd($filePath);


        try {
            $this->validate($request, [

                    "table"       => "required",
                    "column"      => "required",
                    "to"          => "required|integer",
                    "from"        => "required|integer",
                    "folder_name" => "sometimes",
                    "media_table" => "sometimes",
                    "is_pivot"    => "sometimes|in:0,1",

            ]);


        } catch (\Exception $ex) {

            return $this->message($ex->response->original, 422, "Epg Create");

        }
        try {
            $table = $request->input("table");
            $column = $request->input("column");

            if ($request->filled("media_table")) {
                $media = $request->input("media_table");
                if ($request->has("is_pivot")) {
                    $data = DB::table($table)
                              ->join($media, "$table.$column", "=", "$media.id")
                              ->select("$media.*")
                              ->get();
                }
                else {
                    $data = DB::table($table)
                              ->join($media, "$table.$column", "=", "$media.id")
                              ->whereBetween("$table.id", [$request->input("from"), $request->input("to")])
                              ->select("$media.*")
                              ->get();
                }

                if (count($data) > 0) {
                    $folder_name =
                            $request->filled("folder_name") ? $request->input("folder_name") : $request->input("table");


                    foreach ($data as $key => $value) {


                        if (!empty($value->data)) {
                            $path = "/$folder_name/$value->id.$value->extension";


                            Storage::disk('minio')->put($path, base64_decode($value->data), 'public');

                            DB::table($media)->where("id", $value->id)->update([
                                                                                       "path" => $path
                                                                               ]);

                        }
                        else {
                            continue;
                        }
                    }
                }

            }
            else {
                $data = DB::table($request->input("table"))
                          ->whereBetween("id", [$request->input("from"), $request->input("to")])
                          ->pluck($request->input("column"), "id");

                if (count($data) > 0) {
                    $folder_name =
                            $request->filled("folder_name") ? $request->input("folder_name") : $request->input("table");


                    foreach ($data as $key => $value) {


                        if (!empty($value)) {
                            $image_data = explode(",", $value, 2);
                            $image = isset($image_data[1]) ? $image_data[1] : $image_data[0];

                            Storage::disk('minio')->put("/$folder_name/$key.png", base64_decode($image), 'public');

                        }
                        else {
                            continue;
                        }
                    }
                }
            }


            return "success";
        } catch (\Exception $ex) {
            return $this->message($ex->getTraceAsString(), 500, $this->context, 'Something went wrong.');

        }
    }

}
