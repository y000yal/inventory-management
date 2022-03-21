<?php

namespace InventoryManagement\Database\Factories;


use InventoryManagement\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;

class VendorFactory extends Factory {
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Vendor::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array {
        return [
                'name' => $this->faker->name,
                'slug' => $this->faker->slug,
        ];
    }
}