<?php

namespace Database\Seeders;

use App\Models\Family;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed a couple of sample families for local development.
     *
     * The real guest list is entered through the panel at /painel/convidados.
     */
    public function run(): void
    {
        if (Family::query()->exists()) {
            return;
        }

        $farias = Family::create(['label' => 'Família Farias']);
        $farias->guests()->createMany([
            ['name' => 'Dave Farias'],
            ['name' => 'Aline Farias'],
        ]);

        $souza = Family::create(['label' => 'Família Souza']);
        $souza->guests()->create(['name' => 'João Souza']);
    }
}
