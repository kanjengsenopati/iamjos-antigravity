<?php

namespace Database\Factories;

use App\Models\Section;
use App\Models\Journal;
use Illuminate\Database\Eloquent\Factories\Factory;

class SectionFactory extends Factory
{
    protected $model = Section::class;

    public function definition()
    {
        return [
            'journal_id' => Journal::factory(),
            'name' => $this->faker->words(3, true),
            'abbreviation' => $this->faker->lexify('???'),
            'is_active' => true,
            'sort_order' => 0,
        ];
    }
}
