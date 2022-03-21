<?php

$router = $this->app->router;

//vendors
$router->group(['prefix' => 'admin/inventory/v1/vendors', 'middleware' => ['auth', 'client']], function () use ($router) {
    $router->group(['middleware' => ['permission:view vendor']], function () use ($router) {
        $router->get('/', [
                'uses' => 'InventoryManagement\Http\Controllers\VendorController@index']);

        $router->get('/{id:[0-9]+}', [
                'uses' => 'InventoryManagement\Http\Controllers\VendorController@adminShow']);
    });
    $router->post('/', [
            'middleware' => 'permission:create vendor',
            'uses'       => 'InventoryManagement\Http\Controllers\VendorController@store']);

    $router->patch('/{id:[0-9]+}', [
            'middleware' => 'permission:update vendor',
            'uses'       => 'InventoryManagement\Http\Controllers\VendorController@update', 'as' => 'update.inventory.vendor']);

    $router->delete('/{id:[0-9]+}', [
            'middleware' => 'permission:delete vendor',
            'uses'       => 'InventoryManagement\Http\Controllers\VendorController@delete', 'as' => 'remove.inventory.vendors']);

});

//models
$router->group(['prefix' => 'admin/inventory/v1/models', 'middleware' => ['auth:admin', 'client']], function () use ($router) {
    $router->group(['middleware' => ['permission:view model']], function () use ($router) {
        $router->get('/', [
                'uses' => 'InventoryManagement\Http\Controllers\ModelController@index']);

        $router->get('/{id:[0-9]+}', [
                'uses' => 'InventoryManagement\Http\Controllers\ModelController@adminShow']);
    });
    $router->post('/', [
            'middleware' => 'permission:create model',
            'uses'       => 'InventoryManagement\Http\Controllers\ModelController@store']);

    $router->delete('/{id:[0-9]+}', [
            'middleware' => 'permission:delete model',
            'uses'       => 'InventoryManagement\Http\Controllers\ModelController@delete']);


    $router->patch('/{id:[0-9]+}', [
            'middleware' => 'permission:update model',
            'uses'       => 'InventoryManagement\Http\Controllers\ModelController@update']);

});

//groups aka owner
$router->group(['prefix' => 'admin/service/v1/groups', 'middleware' => ['auth:admin', 'client']], function () use ($router) {
    $router->group(['middleware' => ['permission:view owner']], function () use ($router) {
        $router->get('{id:[0-9]+}', [
                'uses' => 'InventoryManagement\Http\Controllers\GroupController@adminShow', 'as' => 'groups.detail']);
        $router->get('/', [
                'uses' => 'InventoryManagement\Http\Controllers\GroupController@index', 'as' => 'list.groups']);
    });
    $router->post('/', [
            'middleware' => 'permission:create owner',
            'uses'       => 'InventoryManagement\Http\Controllers\GroupController@store', 'as' => 'add.group']);


    $router->patch('{id:[0-9]+}', [
            'middleware' => 'permission:update owner',
            'uses'       => 'InventoryManagement\Http\Controllers\GroupController@update', 'as' => 'update.groups']);

    $router->delete('{id:[0-9]+}', [
            'middleware' => 'permission:delete owner',
            'uses'       => 'InventoryManagement\Http\Controllers\GroupController@delete', 'as' => 'remove.groups']);

});

//ipcam/inventory
$router->group(['prefix' => 'admin/inventory/v1/ipcams', 'middleware' => ['auth:admin', 'client', 'role:admin']], function () use ($router) {
    $router->group(['middleware' => ['permission:view inventory']], function () use ($router) {
        $router->get('/{id:[0-9]+}/unused-inventory', [
                'uses' => 'InventoryManagement\Http\Controllers\InventoryController@unusedInventory', 'as' => 'list.allUnusedInventory']);

        $router->get('/', [
                'uses' => 'InventoryManagement\Http\Controllers\InventoryController@index', 'as' => 'list.inventory']);
        $router->get('/{serial}', [
                'uses' => 'InventoryManagement\Http\Controllers\InventoryController@adminShow', 'as' => 'inventory.detail']);

    });
    $router->post('/', [
            'middleware' => 'permission:create inventory',
            'uses'       => 'InventoryManagement\Http\Controllers\InventoryController@store', 'as' => 'add.inventory']);

    $router->post('/verify-file', [
            'middleware' => 'permission:create inventory',
            'uses'       => 'InventoryManagement\Http\Controllers\InventoryController@verify']);

    $router->post('/load-ipcam', [
            'middleware' => 'permission:create inventory',
            'uses'       => 'InventoryManagement\Http\Controllers\InventoryController@load']);

    $router->post('/attach-inv-to-groups', [
            'middleware' => 'permission:attach ipcam',
            'uses'       => 'InventoryManagement\Http\Controllers\InventoryController@attachInventoryToGroup']);

    $router->patch('/detach-inv-from-groups', [
            'middleware' => 'permission:detach ipcam',
            'uses'       => 'InventoryManagement\Http\Controllers\InventoryController@detachInventoryFromGroup']);

    $router->patch('/{serial}', [
            'middleware' => 'permission:update inventory',
            'uses'       => 'InventoryManagement\Http\Controllers\InventoryController@update']);

    $router->delete('/{serial}', [
            'middleware' => 'permission:delete inventory',
            'uses'       => 'InventoryManagement\Http\Controllers\InventoryController@delete']);

});

$router->get('images/{id:[0-9]+}', [
        'uses' => 'InventoryManagement\Http\Controllers\MediaController@getImage', 'as' => 'get.image']);

