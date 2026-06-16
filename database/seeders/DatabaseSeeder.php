<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UnitSeeder::class,
            RbacSeeder::class,
            DemoUserSeeder::class,
            FacultyLeadersSeeder::class,
            CmsSeeder::class,
            DocumentNumberFormatSeeder::class,
            LetterNumberFormatSeeder::class,
            ServiceSeeder::class,
            DemoRequestsSeeder::class,
        ]);
    }
}
