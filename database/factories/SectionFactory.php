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
        $name = $this->faker->words(3, true);
        
        return [
            'journal_id' => Journal::factory(),
            'name' => $name,
            'title' => $name, // Same as name for consistency
            'abbreviation' => $this->faker->lexify('???'),
            'is_active' => true,
            'sort_order' => 0,
        ];
    }
}
