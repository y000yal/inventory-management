<?php
/**
 * Created by PhpStorm.
 * @author Kabina Suwal <kabina.suwal92@gmail.com>
 * Date: 13-Jul-18
 * Time: 04:47 PM
 */

namespace InventoryManagement\Repo\Eloquent;


use \InventoryManagement\Models\Media;
use \InventoryManagement\Repo\RepoInterface\MediaInterface;

class MediaRepo extends BaseRepo implements MediaInterface
{
    protected $media;

    public function __construct(Media $media)
    {
        parent::__construct($media);
        $this->media = $media;
    }

    public function delete($id)
    {

        $this->media->where('id', $id)->delete();
    }
}
