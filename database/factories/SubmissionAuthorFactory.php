<?php

namespace Database\Factories;

use App\Models\SubmissionAuthor;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubmissionAuthorFactory extends Factory
{
    protected $model = SubmissionAuthor::class;

    public function definition()
    {
        return [
            'submission_id'      => Submission::factory(),
            'publication_id'     => null, // diisi saat create jika diperlukan
            'user_id'            => User::factory(),
            'first_name'         => $this->faker->firstName,
            'last_name'          => $this->faker->lastName,
            'name'               => function (array $attributes) {
                return $attributes['first_name'] . ' ' . $attributes['last_name'];
            },
            'email'              => $this->faker->unique()->safeEmail,
            'affiliation'        => $this->faker->company,
            'country'            => $this->faker->countryCode,
            'is_corresponding'   => false,
            'is_primary_contact' => false,
            'sort_order'         => 0,
        ];
    }
}
