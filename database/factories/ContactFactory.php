<?php

namespace Database\Factories;

use App\Models\Contact;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Contact>
 */
class ContactFactory extends Factory
{

    public function definition(): array
    {
        return [
            'category_id' => fake()->numberBetween(1, 5),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'gender' => fake()->numberBetween(1, 3),
            'email' => fake()->email(),
            'tel' => fake()->numerify('090########'),
            'address' => fake()->address(),
            'building' => fake()->secondaryAddress(),
            'detail' => fake()->text(80),
        ];
    }
    public function configure()
    {
        return $this->afterCreating(function (Contact $contact) {
            // 全Tagの中からランダムに1〜3件のIDを取得してattach
            $tagIds = Tag::inRandomOrder()->take(rand(1, 3))->pluck('id');
            $contact->tags()->attach($tagIds);
        });
    }

}
