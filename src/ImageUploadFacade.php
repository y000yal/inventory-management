<?php
/**
 * Created by PhpStorm.
 * User: ashish
 * Date: 6/28/2017
 * Time: 4:32 PM
 */

namespace  GeniussystemsNp\InventoryManagement;


use Carbon\Carbon;
use GeniussystemsNp\InventoryManagement\Repo\RepoInterface\MediaInterface;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;


class ImageUploadFacade
{
    /**
     * @var MediaInterface
     */
    protected $media;

    public function __construct(MediaInterface $media)
    {
        $this->media = $media;
    }

    /**
     * @param $files
     * @param $type
     * @return array|mixed
     */
    public function upload($files, $type)
    {
        if (is_array($files)) {
            foreach ($files as $file) {
                $media_ids[] = $this->uploadImage($file, $type);
            }
            return $media_ids;
        } else {
            return $this->uploadImage($files, $type);
        }
    }

    /**
     * @param $file
     * @param $type
     * @return mixed
     */
    private function uploadImage($file, $type)
    {

        $image_path = addslashes($file->getPathName());
        $path = "/$type/" . str_slug(Carbon::now()) . "-" . $file->getClientOriginalName();
        Storage::disk('minio')->put($path, file_get_contents($image_path), 'public');

//        $encoded_image = base64_encode(file_get_contents($image_path));
//        $create_media['data'] = $encoded_image;
        $create_media["path"] = $path;
        $create_media['type'] = $type;
        $create_media['mime_type'] = $file->getClientMimeType();
        $create_media['extension'] = $file->getClientOriginalExtension();
        $create_media['size'] = $file->getSize();

        $media = $this->media->create($create_media);
        return $media['id'];
    }

    /**
     * @param $documents
     * @return array|bool
     */

    public function uploadImgWithUrls($documents)
    {
        $base_url = config('config.channel_base_url') . "/images/";
        foreach ($documents as $document) {
            if (is_file($document['image'])) {
                $media_ids[] = $this->uploadImage($document['image'], $document['type']);
            } else {

                $data = str_after($document['image'], $base_url);
                if ((integer)$data > 0) {
                    $media_ids[] = (integer)$data;
                } else {
                    return false;
                }

            }

        }

        return $media_ids;

    }

    /**
     * Blur image
     * @param $image_id
     * @return bool
     */

    public function blurImage($image_id)
    {

        $data = $this->media->getSpecificById($image_id);

        if (!empty($data)) {
            return Image::make(base64_decode($data['data']))->resize(200, 200)->blur(90)->response($data['extension']);
        }

        return false;
    }


    public function uploadImageByUrl($url, $type)
    {

        // $image_path = addslashes($file->getPathName());
        $headers = get_headers($url, true);
        $encoded_image = base64_encode(file_get_contents($url));
        $create_media['data'] = $encoded_image;
        $create_media['type'] = $type;
        $create_media['mime_type'] = "image/" . pathinfo($url, PATHINFO_EXTENSION);
        $create_media['extension'] = pathinfo($url, PATHINFO_EXTENSION);
        $create_media['size'] = $headers['Content-Length'];

        $media = $this->media->create($create_media);
        return $media['id'];
    }


}
