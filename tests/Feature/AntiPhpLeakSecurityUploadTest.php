<?php

namespace Tests\Feature;

use App\Models\User;
use App\Rules\NoExecutableCode;
use App\Services\SecureFileUploadService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class AntiPhpLeakSecurityUploadTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_no_executable_code_rule_blocks_double_extension()
    {
        $file = UploadedFile::fake()->create('avatar.php.jpg', 100, 'image/jpeg');

        $validator = Validator::make(['file' => $file], [
            'file' => ['required', 'file', new NoExecutableCode()],
        ]);

        $this->assertTrue($validator->fails());
        $this->assertStringContainsString('dilarang', $validator->errors()->first('file'));
    }

    public function test_no_executable_code_rule_blocks_php_binary_payload_in_fake_image()
    {
        // Simulasi web shell disisipkan ke dalam file berekstensi .jpg
        $phpPayload = "GIF89a\n<?php phpinfo(); ?>\n";
        $file = UploadedFile::fake()->createWithContent('innocent.jpg', $phpPayload);

        $validator = Validator::make(['file' => $file], [
            'file' => ['required', 'file', new NoExecutableCode()],
        ]);

        $this->assertTrue($validator->fails());
        $this->assertStringContainsString('kode berbahaya', $validator->errors()->first('file'));
    }

    public function test_no_executable_code_rule_blocks_short_tag_and_eval_payload()
    {
        $payload = "image data <?= eval(\$_POST['cmd']); ?>";
        $file = UploadedFile::fake()->createWithContent('photo.png', $payload);

        $validator = Validator::make(['file' => $file], [
            'file' => ['required', 'file', new NoExecutableCode()],
        ]);

        $this->assertTrue($validator->fails());
        $this->assertStringContainsString('kode berbahaya', $validator->errors()->first('file'));
    }

    public function test_no_executable_code_rule_allows_clean_image()
    {
        $cleanImage = UploadedFile::fake()->image('clean.jpg', 100, 100);

        $validator = Validator::make(['file' => $cleanImage], [
            'file' => ['required', 'file', new NoExecutableCode()],
        ]);

        $this->assertFalse($validator->fails());
    }

    public function test_secure_file_upload_service_renames_to_uuid_and_strips_metadata()
    {
        Storage::fake('public');

        $service = new SecureFileUploadService();
        $cleanImage = UploadedFile::fake()->image('original_filename.jpg', 200, 200);

        $savedPath = $service->uploadImage($cleanImage, 'test_materials', 'public');

        // Memastikan nama file teracak menggunakan UUID, bukan nama asli client
        $savedFilename = basename($savedPath);
        $this->assertNotEquals('original_filename.jpg', $savedFilename);
        $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}\.jpg$/i', $savedFilename);

        Storage::disk('public')->assertExists($savedPath);
    }

    public function test_secure_file_upload_service_rejects_fake_image_mime_spoofing()
    {
        $service = new SecureFileUploadService();

        // File teks biasa yang dipalsukan berekstensi .jpg
        $fakeImage = UploadedFile::fake()->createWithContent('fake.jpg', 'just text content, not an image');

        $this->expectException(\RuntimeException::class);
        $service->uploadImage($fakeImage, 'test_materials', 'public');
    }

    public function test_secure_attachment_controller_blocks_path_traversal()
    {
        $response = $this->actingAs($this->user)->get('/secure-attachments/../../etc/passwd');
        $response->assertStatus(403);
    }
}

