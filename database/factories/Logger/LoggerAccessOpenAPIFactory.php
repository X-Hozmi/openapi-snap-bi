<?php

namespace Database\Factories\Logger;

use App\Models\Logger\LoggerAccessOpenAPI;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends Factory<LoggerAccessOpenAPI>
 */
class LoggerAccessOpenAPIFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Model>
     */
    protected $model = LoggerAccessOpenAPI::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'method' => fake()->randomElement(['POST', 'GET', 'PUT', 'DELETE']),
            'url' => fake()->url(),
            'ip_address' => fake()->ipv4(),
            'request_headers' => fake()->words(5),
            'response_headers' => fake()->words(5),
            'request_contents' => fake()->words(5),
            'response_contents' => fake()->words(5),
            'response_code' => fake()->numberBetween(200, 500),
            'processing_time' => fake()->randomNumber(3, true),
        ];
    }
}
