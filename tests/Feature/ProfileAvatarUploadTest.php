<?php

namespace Tests\Feature;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileAvatarUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_upload_avatar_manually(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        // Ensure profile exists
        $user->profile()->create();

        $this->actingAs($user);

        $file = UploadedFile::fake()->image('avatar.jpg', 200, 200);

        $response = $this->post(route('profile.profile.update'), [
            'avatar' => $file,
            'nickname' => null,
            'birthdate' => null,
            'daily_goal_minutes' => null,
            'gender' => null,
        ]);

        $response->assertRedirect(route('profile.edit'));

        $profile = Profile::where('user_id', $user->id)->first();
        $this->assertNotNull($profile);
        $this->assertNotNull($profile->avatar);
        // Assert the file was stored on the public disk
    $this->assertTrue(Storage::disk('public')->exists($profile->avatar));
    }
}
