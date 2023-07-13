<?php

namespace Database\Factories;
use App\Models\Connection;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class ConnectionFactory extends Factory
{
    protected $model = Connection::class;

    public function definition()
    {
        $user = User::inRandomOrder()->first();
        $targetUser = User::where('id', '!=', $user->id)->inRandomOrder()->first();

        return [
            'user_id' => $user->id,
            'target_user_id' => $targetUser->id,
            'status' => $this->faker->randomElement(['pending', 'accepted', 'declined']),
        ];
    }
}
