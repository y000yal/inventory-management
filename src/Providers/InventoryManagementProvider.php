<?php
/**
 * Class InventoryManagementProvider
 *
 * @category
 * @author Yoel Limbu <yoyal.limbu@gmail.com>
 */
namespace InventoryManagement\Providers;

use Faker\Factory;
use InventoryManagement\ImageUploadFacade;
use InventoryManagement\Repo\Eloquent\GroupRepo;
use InventoryManagement\Repo\Eloquent\MediaRepo;
use InventoryManagement\Repo\Eloquent\ModelRepo;
use InventoryManagement\Repo\Eloquent\InventoryRepo;
use InventoryManagement\Repo\Eloquent\VendorRepo;
use InventoryManagement\Repo\RepoInterface\GroupInterface;
use InventoryManagement\Repo\RepoInterface\InventoryInterface;
use InventoryManagement\Repo\RepoInterface\MediaInterface;
use InventoryManagement\Repo\RepoInterface\ModelInterface;
use InventoryManagement\Repo\RepoInterface\VendorInterface;
use \Illuminate\Support\ServiceProvider;
class InventoryManagementProvider extends ServiceProvider {
    /**
     * Bootstrap the application services.
     *
     * @return void
     */

    public function boot() {

        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register() {
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->app->bind('imageUploader',ImageUploadFacade::class);
        $this->app->bind(VendorInterface::class, VendorRepo::class);
        $this->app->bind(ModelInterface::class, ModelRepo::class);
        $this->app->bind(InventoryInterface::class, InventoryRepo::class);
        $this->app->bind(GroupInterface::class, GroupRepo::class);
        $this->app->bind(MediaInterface::class, MediaRepo::class);
    }

    public function loadRoutesFrom($path) {
        require $path;
    }
}
