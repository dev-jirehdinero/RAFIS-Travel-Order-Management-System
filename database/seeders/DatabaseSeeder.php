<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\Personnel;
use App\Models\TravelOrder;
use App\Models\User;
use Database\Factories\PersonnelFactory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $unitHeadName = PersonnelFactory::NAMES[0];

        // Exactly one record per real staff name (no random picking, so no duplicates).
        foreach (PersonnelFactory::NAMES as $name) {
            $isUnitHead = $name === $unitHeadName;

            $person = Personnel::factory()->create([
                'name' => $name,
                'unit_head' => $isUnitHead ? null : $unitHeadName,
            ]);

            // 2-5 travel orders each, so every progress bar has data on first render.
            TravelOrder::factory()
                ->count(random_int(2, 5))
                ->for($person, 'personnel')
                ->create();

            // PLACEHOLDER login: surname@rafis.test / "password". The unit head is the OIC.
            // Each person replaces the email and password on the Account page after first login.
            User::create([
                'name' => $name,
                'email' => Str::slug(Str::before($name, ',')).'@rafis.test',
                'password' => 'password',
                'role' => $isUnitHead ? Role::Oic : Role::Staff,
                'personnel_id' => $person->id,
            ]);
        }

        User::create([
            'name' => 'RAFIS Admin',
            'email' => 'admin@rafis.test',
            'password' => 'password',
            'role' => Role::Admin,
        ]);
    }
}
