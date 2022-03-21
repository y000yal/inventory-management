<?php
/**
 * Class StbModelFactory
 *
 * @category
 * @package InventoryManagement\Database\Factories
 * @author Yoel Limbu <yoyal.limbu@gmail.com>
 */

namespace InventoryManagement\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use InventoryManagement\Models\InventoryModel;

class InventoryFactory extends Factory{
    protected $model = InventoryModel::class;

    public function definition(): array {
        return [
                'name' => $this->faker->name,
                'slug' => $this->faker->slug,
        ];
    }
}