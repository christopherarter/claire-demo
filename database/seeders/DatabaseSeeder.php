<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        User::factory()->count(30)->create();
        Business::factory()->count(5)->create();
        User::all()->each(function (User $user) {
            $businesses = Business::inRandomOrder()->take(rand(1, 4))->get();
            $user->businesses()->attach($businesses);
        });
    }
}
