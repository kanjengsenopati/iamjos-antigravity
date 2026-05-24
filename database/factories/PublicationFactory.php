<?php

namespace Database\Factories;

use App\Models\Publication;
use App\Models\Submission;
use Illuminate\Database\Eloquent\Factories\Factory;

class PublicationFactory extends Factory
{
    protected $model = Publication::class;

    public function definition(): array
    {
        return [
            'submission_id'    => Submission::factory(),
            'version'          => 1,
            'status'           => Publication::STATUS_PUBLISHED,
            'title'            => $this->faker->sentence(6),
            'abstract'         => $this->faker->paragraph(3),
            'keywords'         => implode(', ', $this->faker->words(4)),
            'pages'            => $this->faker->numberBetween(1, 50) . '-' . $this->faker->numberBetween(51, 100),
            'doi'              => null,
            'copyright_holder' => $this->faker->name,
            'copyright_year'   => $this->faker->year,
            'license_url'      => 'https://creativecommons.org/licenses/by/4.0/',
            'date_published'   => $this->faker->dateTimeBetween('-2 years', 'now')->format('Y-m-d'),
        ];
    }

    /**
     * State untuk publication yang belum published.
     */
    public function queued(): static
    {
        return $this->state(['status' => Publication::STATUS_QUEUED]);
    }

    /**
     * State untuk publication yang sudah published.
     */
    public function published(): static
    {
        return $this->state(['status' => Publication::STATUS_PUBLISHED]);
    }
}
