<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{

    protected $model =  Product::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'        => fake()->name(),
            'description' => fake()->sentence(),
            'price'       => fake()->randomFloat(2, 400, 2000),
            'stock'       => fake()->numberBetween(50, 500),
            'status'      => 1,
            'discount'    => fake()->randomFloat(2, 0, 100),
        ];
    }
}
