<?php

namespace Tests\Feature;

use App\Models\DatabaseImage;
use App\Services\DatabaseImageService;
use App\Services\ImageProcessor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ImageProcessorTest extends TestCase
{
    use RefreshDatabase;

    private function jpeg(int $w, int $h): string
    {
        $im = imagecreatetruecolor($w, $h);
        imagefill($im, 0, 0, imagecolorallocate($im, 200, 60, 40));
        ob_start();
        imagejpeg($im);

        return ob_get_clean();
    }

    public function test_a_large_jpeg_becomes_a_smaller_webp_with_a_preview(): void
    {
        $result = ImageProcessor::process($this->jpeg(3000, 2000), 1600, 1600, 85);

        $this->assertSame([1600, 1067], [$result['width'], $result['height']]);
        $webp = getimagesizefromstring($result['webp']);
        $this->assertSame(IMAGETYPE_WEBP, $webp[2]);
        $this->assertSame([1600, 1067], [$webp[0], $webp[1]]);

        $lqip = getimagesizefromstring(base64_decode($result['lqip']));
        $this->assertSame(IMAGETYPE_JPEG, $lqip[2]);
        $this->assertSame([32, 21], [$lqip[0], $lqip[1]]);
    }

    public function test_images_that_fit_are_not_enlarged(): void
    {
        $result = ImageProcessor::process($this->jpeg(300, 200), 1600, 1600);

        $this->assertSame([300, 200], [$result['width'], $result['height']]);
    }

    public function test_a_tall_image_stays_inside_the_height_limit(): void
    {
        $result = ImageProcessor::process($this->jpeg(1000, 4000), 1600, 1600);

        $this->assertSame([400, 1600], [$result['width'], $result['height']]);
    }

    public function test_png_transparency_is_kept(): void
    {
        $im = imagecreatetruecolor(200, 200);
        imagealphablending($im, false);
        imagesavealpha($im, true);
        imagefill($im, 0, 0, imagecolorallocatealpha($im, 0, 0, 0, 127));
        ob_start();
        imagepng($im);
        $result = ImageProcessor::process(ob_get_clean(), 100, 100);

        $webp = imagecreatefromstring($result['webp']);
        $this->assertGreaterThan(0, (imagecolorat($webp, 10, 10) >> 24) & 127);
    }

    public function test_other_formats_and_garbage_are_left_alone(): void
    {
        $gif = imagecreatetruecolor(10, 10);
        ob_start();
        imagegif($gif);

        $this->assertNull(ImageProcessor::process(ob_get_clean(), 100, 100));
        $this->assertNull(ImageProcessor::process('not an image', 100, 100));
    }

    public function test_upload_is_stored_as_webp_with_the_preview(): void
    {
        $path = DatabaseImageService::store(UploadedFile::fake()->image('cat.jpg', 2400, 1800), 'posts');

        $row = DatabaseImage::where('path', $path)->first();
        $this->assertStringEndsWith('.webp', $path);
        $this->assertSame('image/webp', $row->mime_type);
        $this->assertSame(strlen($row->data), $row->size);
        $this->assertSame([1200, 900], [$row->width, $row->height]);
        $this->assertStringStartsWith('data:image/jpeg;base64,', DatabaseImageService::lqip($path));
        $this->assertSame(IMAGETYPE_WEBP, getimagesizefromstring($row->data)[2]);
    }

    public function test_listing_photos_of_any_shape_all_come_out_4_to_3(): void
    {
        foreach ([[3000, 2000, [1200, 900]], [900, 1600, [900, 675]], [1200, 1200, [1200, 900]], [4000, 1000, [1333, 1000]]] as [$w, $h, $expected]) {
            $row = DatabaseImage::where('path', DatabaseImageService::store(UploadedFile::fake()->image("p$w.jpg", $w, $h), 'posts'))->first();

            $this->assertEqualsWithDelta(4 / 3, $row->width / $row->height, 0.01, "{$w}x{$h}");
            $this->assertLessThanOrEqual(1200, $row->width);
            $this->assertLessThanOrEqual(900, $row->height);
        }
    }

    public function test_a_gif_upload_is_stored_untouched(): void
    {
        $path = DatabaseImageService::store(UploadedFile::fake()->image('anim.gif', 50, 50), 'posts');

        $row = DatabaseImage::where('path', $path)->first();
        $this->assertStringEndsWith('.gif', $path);
        $this->assertNull($row->lqip);
    }
}
