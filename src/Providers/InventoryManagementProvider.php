<?php
/**
 * Class InventoryManagementProvider
 *
 * @category
 * @author Yoel Limbu <yoyal.limbu@gmail.com>
 */
namespace GeniussystemsNp\InventoryManagement\Providers;

use Faker\Factory;
use GeniussystemsNp\InventoryManagement\ImageUploadFacade;
use GeniussystemsNp\InventoryManagement\Repo\Eloquent\GroupRepo;
use GeniussystemsNp\InventoryManagement\Repo\Eloquent\MediaRepo;
use GeniussystemsNp\InventoryManagement\Repo\Eloquent\ModelRepo;
use GeniussystemsNp\InventoryManagement\Repo\Eloquent\InventoryRepo;
use GeniussystemsNp\InventoryManagement\Repo\Eloquent\VendorRepo;
use GeniussystemsNp\InventoryManagement\Repo\RepoInterface\GroupInterface;
use GeniussystemsNp\InventoryManagement\Repo\RepoInterface\InventoryInterface;
use GeniussystemsNp\InventoryManagement\Repo\RepoInterface\MediaInterface;
use GeniussystemsNp\InventoryManagement\Repo\RepoInterface\ModelInterface;
use GeniussystemsNp\InventoryManagement\Repo\RepoInterface\VendorInterface;
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
