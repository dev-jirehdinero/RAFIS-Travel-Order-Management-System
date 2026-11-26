<?php

namespace Database\Factories;

use App\Enums\TravelOrderStatus;
use App\Models\Personnel;
use App\Models\TravelOrder;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<TravelOrder> */
class TravelOrderFactory extends Factory
{
    protected $model = TravelOrder::class;

    public function definition(): array
    {
        $departure = Carbon::instance(fake()->dateTimeBetween('2026-10-01', '2026-12-15'));
        $submitted = fake()->boolean();

        return [
            'assignment_id' => 'TO-2026-'.fake()->unique()->numerify('#####'),
            'personnel_id' => Personnel::factory(),
            'official_station' => fake()->randomElement(['Regional Office', 'Provincial Office', 'Central Office']),
            'google_drive_link' => $submitted ? 'https://drive.google.com/file/d/'.fake()->bothify('????????????').'/view' : null,
            'departure_date' => $departure->toDateString(),
            'return_date' => $departure->copy()->addDays(fake()->numberBetween(1, 4))->toDateString(),
            'destination' => fake()->randomElement(['Tacloban City', 'Cebu City', 'Davao City', 'Baguio City', 'Iloilo City', 'Legazpi City']),
            'status' => $submitted ? TravelOrderStatus::Submitted : TravelOrderStatus::NotStarted,
        ];
    }
}
