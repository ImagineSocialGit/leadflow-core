<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;

class WebinarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $devSeries = \App\Models\WebinarSeries::firstOrCreate([
            'slug' => 'dev-webinar',
        ], [
            'title' => 'Dev Webinar',
        ]);

    }
}
