<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\DatabaseImage;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DatabaseImageStorageTest extends TestCase
{
    use RefreshDatabase;

    protected User $seller;
    protected User $user;
    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'seller', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);

        $this->seller = User::factory()->create();
        $this->seller->assignRole('seller');

        $this->user = User::factory()->create();
        $this->user->assignRole('user');

        $this->category = Category::create([
            'name' => 'Mushuklar',
        ]);
    }

    public function test_user_avatar_is_stored_in_mysql_database_and_served_without_filesystem(): void
    {
        $fakeAvatar = UploadedFile::fake()->image('my_avatar.png', 100, 100);

        $response = $this->actingAs($this->user)->post(route('user.profile.update'), [
            'name' => 'Updated Name',
            'phone' => '+998901234567',
            'avatar' => $fakeAvatar,
        ]);

        $response->assertRedirect();
        $this->user->refresh();

        $this->assertNotNull($this->user->avatar);
        $this->assertStringStartsWith('avatars/', $this->user->avatar);

        // Check image exists in MySQL database_images table
        $dbImage = DatabaseImage::where('path', $this->user->avatar)->first();
        $this->assertNotNull($dbImage);
        $this->assertNotEmpty($dbImage->data);
        $this->assertStringContainsString('image/', $dbImage->mime_type);

        // Verify no files written to storage/app/public/avatars
        $localDiskFiles = glob(storage_path('app/public/avatars/*')) ?: [];
        $nonGitignoreFiles = array_filter($localDiskFiles, fn($f) => basename($f) !== '.gitignore');
        $this->assertEmpty($nonGitignoreFiles, 'Images must not be stored on the local filesystem.');

        // Stream image from MySQL
        $viewResponse = $this->get($this->user->avatarUrl());
        $viewResponse->assertStatus(200);
        $viewResponse->assertHeader('Content-Type', $dbImage->mime_type);
        $this->assertSame($dbImage->data, $viewResponse->getContent());

        // Also test /storage/{path} fallback route
        $fallbackResponse = $this->get('/storage/' . $this->user->avatar);
        $fallbackResponse->assertStatus(200);
        $this->assertSame($dbImage->data, $fallbackResponse->getContent());
    }

    public function test_user_updating_avatar_deletes_old_image_from_mysql_database(): void
    {
        $firstAvatar = UploadedFile::fake()->image('first.jpg', 80, 80);
        $this->actingAs($this->user)->post(route('user.profile.update'), [
            'name' => $this->user->name,
            'avatar' => $firstAvatar,
        ]);

        $this->user->refresh();
        $oldPath = $this->user->avatar;
        $this->assertTrue(DatabaseImage::where('path', $oldPath)->exists());

        // Update with second avatar
        $secondAvatar = UploadedFile::fake()->image('second.png', 80, 80);
        $this->actingAs($this->user)->post(route('user.profile.update'), [
            'name' => $this->user->name,
            'avatar' => $secondAvatar,
        ]);

        $this->user->refresh();
        $newPath = $this->user->avatar;

        $this->assertNotSame($oldPath, $newPath);
        $this->assertFalse(DatabaseImage::where('path', $oldPath)->exists(), 'Old avatar must be deleted from MySQL database.');
        $this->assertTrue(DatabaseImage::where('path', $newPath)->exists(), 'New avatar must exist in MySQL database.');
    }

    public function test_seller_post_images_are_stored_in_mysql_and_deleted_when_post_is_deleted(): void
    {
        $img1 = UploadedFile::fake()->image('cat1.jpg', 200, 200);
        $img2 = UploadedFile::fake()->image('cat2.jpg', 200, 200);

        $response = $this->actingAs($this->seller)->post(route('posts.store'), [
            'title' => 'Fors mushugi',
            'description' => 'Ajoyib zotli mushuk',
            'category_id' => $this->category->id,
            'breed' => 'Persian',
            'quantity' => 1,
            'gender' => 'female',
            'age' => '1 yosh',
            'price' => 2000000,
            'currency' => 'UZS',
            'location' => 'Toshkent',
            'status' => 'active',
            'images' => [$img1, $img2],
        ]);

        $response->assertRedirect(route('posts.index'));

        $post = Post::where('title', 'Fors mushugi')->firstOrFail();
        $this->assertCount(2, $post->allImages());

        foreach ($post->allImages() as $imgPath) {
            $this->assertTrue(
                DatabaseImage::where('path', $imgPath)->exists(),
                "Image {$imgPath} must be stored in database_images."
            );

            // Verify streaming from DB
            $stream = $this->get(route('images.show', ['path' => $imgPath]));
            $stream->assertStatus(200);
            $stream->assertHeader('Content-Type', 'image/jpeg');
        }

        // Verify no files on disk
        $localFiles = glob(storage_path('app/public/posts/*')) ?: [];
        $realFiles = array_filter($localFiles, fn($f) => basename($f) !== '.gitignore');
        $this->assertEmpty($realFiles, 'Post images must not be stored on local filesystem.');

        // Delete post
        $delResponse = $this->actingAs($this->seller)->delete(route('posts.destroy', $post));
        $delResponse->assertRedirect(route('posts.index'));

        // Verify images deleted from MySQL
        foreach ($post->allImages() as $imgPath) {
            $this->assertFalse(
                DatabaseImage::where('path', $imgPath)->exists(),
                "Image {$imgPath} must be deleted from database_images upon post deletion."
            );
        }
    }
}
