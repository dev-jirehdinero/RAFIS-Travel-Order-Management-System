<?php

namespace Database\Factories;

use App\Models\Personnel;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Personnel> */
class PersonnelFactory extends Factory
{
    protected $model = Personnel::class;

    public const NAMES = [
        'BENOSA, DENICE JOELLE A.',
        'RESPICIO, BERNADETH Z.',
        'TABUADA, ANGELA REI R.',
        'AJOSE, PAULO EMMANUEL',
        'MORILLO, ELMO',
        'GALIVO, MICHAEL JOHN',
        'ICAL, JACQUELYN',
        'VEDRA, LANCE CEDRIC',
        'VERGARA, MONICA',
        'GATACILO, JAYSON',
        'FRANCISCO, MELISSA',
    ];

    public const POSITIONS = [
        'Information Officer II',
        'Information Officer III',
        'Administrative Assistant IV',
        'Administrative Aide VI',
        'Administrative Officer II',
        'Project Development Officer I',
    ];

    public const PROGRAMS = [
        'Communications',
        'Planning and Monitoring',
        'Records and Administration',
        'Field Operations',
    ];

    public function definition(): array
    {
        return [
            // The seeder passes the real names explicitly; this is only the fallback for tests.
            'name' => strtoupper(fake()->unique()->name()),
            'position' => fake()->randomElement(self::POSITIONS),
            'employment_status' => 'Permanent',
            'unit_head' => self::NAMES[0],
            'assigned_program' => fake()->randomElement(self::PROGRAMS),
        ];
    }
}