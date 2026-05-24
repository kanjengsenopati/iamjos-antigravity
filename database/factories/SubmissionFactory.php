<?php

namespace Database\Factories;

use App\Models\Submission;
use App\Models\Journal;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubmissionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Submission::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'journal_id'   => Journal::factory(),
            'section_id'   => \App\Models\Section::factory(),
            'user_id'      => User::factory(),
            'issue_id'     => null, // null by default — assign issue secara eksplisit jika diperlukan
            'title'        => $this->faker->sentence,
            'locale'       => 'en', // Default locale
            'status'       => Submission::STATUS_PUBLISHED,
            'stage'        => Submission::STAGE_PRODUCTION,
            'submitted_at' => now(),
            'published_at' => now(),
            'seq_id'       => $this->faker->unique()->numberBetween(1000, 99999),
        ];
    }
}
