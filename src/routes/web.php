<?php

use \Illuminate\Support\Facades\Route;

$router = $this->app->router;

$router->group(['prefix' => 'admin/inventory/v1/vendors'], function () use ($router) {

    $router->get('/', [
        'uses' => 'GeniussystemsNp\InventoryManagement\Http\Controllers\VendorController@index', 'as' => 'list.inventory.vendors']);

    $router->get('/{id:[0-9]+}', [
        'uses' => 'GeniussystemsNp\InventoryManagement\Http\Controllers\VendorController@adminShow', 'as' => 'inventory.vendor.detail']);

    $router->post('/', [
        'uses' => 'GeniussystemsNp\InventoryManagement\Http\Controllers\VendorController@store', 'as' => 'add.inventory.vendors']);

    $router->patch('/{id:[0-9]+}', [
        'uses' => 'GeniussystemsNp\InventoryManagement\Http\Controllers\VendorController@update', 'as' => 'update.inventory.vendor']);

    $router->delete('/{id:[0-9]+}', [
        'uses' => 'GeniussystemsNp\InventoryManagement\Http\Controllers\VendorController@delete', 'as' => 'remove.inventory.vendors']);

});
$router->group(['prefix' => 'admin/inventory/v1/models'], function () use ($router) {
    $router->post('/', [
        'uses' => 'GeniussystemsNp\InventoryManagement\Http\Controllers\ModelController@store', 'as' => 'add.inventory.models']);

    $router->delete('/{id:[0-9]+}', [
        'uses' => 'GeniussystemsNp\InventoryManagement\Http\Controllers\ModelController@delete', 'as' => 'remove.inventory.models']);

    $router->get('/', [
        'uses' => 'GeniussystemsNp\InventoryManagement\Http\Controllers\ModelController@index', 'as' => 'list.inventory.models']);

    $router->get('/{id:[0-9]+}', [
        'uses' => 'GeniussystemsNp\InventoryManagement\Http\Controllers\ModelController@adminShow', 'as' => 'inventory.model.detail']);

    $router->patch('/{id:[0-9]+}', [
        'uses' => 'GeniussystemsNp\InventoryManagement\Http\Controllers\ModelController@update', 'as' => 'update.inventory.models']);

});
$router->group(['prefix' => 'admin/inventory/v1/ipcams'], function () use ($router) {

    $router->get('/{serial}', [
        'uses' => 'GeniussystemsNp\InventoryManagement\Http\Controllers\InventoryController@adminShow', 'as' => 'inventory.detail']);

    $router->patch('/{serial}', [
        'uses' => 'GeniussystemsNp\InventoryManagement\Http\Controllers\InventoryController@update', 'as' => 'update.inventory']);

    $router->delete('/{serial}', [
        'uses' => 'GeniussystemsNp\InventoryManagement\Http\Controllers\InventoryController@delete', 'as' => 'delete.inventory']);

    $router->get('/', [
        'uses' => 'GeniussystemsNp\InventoryManagement\Http\Controllers\InventoryController@index', 'as' => 'list.inventory']);

    $router->post('/', [
        'uses' => 'GeniussystemsNp\InventoryManagement\Http\Controllers\InventoryController@store', 'as' => 'add.inventory']);


});
$router->group(['prefix' => 'admin/service/v1/groups'], function () use ($router) {
    $router->get('/', [
        'uses' => 'GeniussystemsNp\InventoryManagement\Http\Controllers\GroupController@index', 'as' => 'list.groups']);

    $router->post('/', [
        'uses' => 'GeniussystemsNp\InventoryManagement\Http\Controllers\GroupController@store', 'as' => 'add.group']);

    $router->post('stbs', [
        'uses' => 'StbController@attachStbToReseller', 'as' => 'attach.reseller']);

    $router->delete('stbs', [
        'uses' => 'StbController@detachStbFromReseller', 'as' => 'detach.stb.from.reseller']);

    $router->get('{id:[0-9]+}', [
        'uses' => 'GeniussystemsNp\InventoryManagement\Http\Controllers\GroupController@adminShow', 'as' => 'groups.detail']);

    $router->patch('{id:[0-9]+}', [
        'uses' => 'GeniussystemsNp\InventoryManagement\Http\Controllers\GroupController@update', 'as' => 'update.groups']);

    $router->delete('{id:[0-9]+}', [
        'uses' => 'GeniussystemsNp\InventoryManagement\Http\Controllers\GroupController@delete', 'as' => 'remove.groups']);

    $router->patch('{username}/password', [
        'uses' => 'GeniussystemsNp\InventoryManagement\Http\Controllers\GroupController@changePwd', 'as' => 'groups.change.password']);

});
