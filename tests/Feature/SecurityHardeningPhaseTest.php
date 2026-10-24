<?php

namespace Tests\Feature;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\User;
use App\Policies\PurchaseOrderPolicy;
use App\Services\SecureUploadService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class SecurityHardeningPhaseTest extends TestCase
{
    public function test_phase5_security_headers_middleware_is_applied_globally()
    {
        $response = $this->get('/login');

        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-XSS-Protection', '1; mode=block');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('Permissions-Policy', 'geolocation=(self), camera=(self), microphone=()');
        $this->assertFalse($response->headers->has('X-Powered-By'));
    }

    public function test_phase2_session_configuration_is_hardened()
    {
        $this->assertTrue(config('session.http_only'));
        $this->assertEquals('lax', config('session.same_site'));
        $this->assertTrue(config('session.encrypt'));
    }

    public function test_phase3_rbac_purchase_order_policy_blocks_po_nota_harian()
    {
        $policy = new PurchaseOrderPolicy();

        $user = new User();
        // Simulasi role po_nota_harian
        $user->setRelation('roles', collect([
            (object)['name' => 'po_nota_harian']
        ]));

        $po = new PurchaseOrder();

        // po_nota_harian boleh melihat (viewAny & view)
        $this->assertTrue($policy->viewAny($user));
        $this->assertTrue($policy->view($user, $po));

        // po_nota_harian DILARANG melakukan mutasi (create, update, delete, approve)
        $this->assertFalse($policy->create($user));
        $this->assertFalse($policy->update($user, $po));
        $this->assertFalse($policy->delete($user, $po));
        $this->assertFalse($policy->approve($user, $po));
    }

    public function test_phase4_anti_php2shell_double_extension_is_rejected()
    {
        $this->expectException(ValidationException::class);

        $fakeWebshell = UploadedFile::fake()->create('malicious.php.jpg', 100, 'image/jpeg');
        SecureUploadService::validateSafeFile($fakeWebshell);
    }

    public function test_phase4_valid_file_stored_with_randomized_filename_in_private_disk()
    {
        Storage::fake('private');

        $fakeImage = UploadedFile::fake()->image('nota_asli.jpg', 600, 600);
        $path = SecureUploadService::processAndStore($fakeImage, 'purchase_receipts', 'private');

        $this->assertNotEquals('nota_asli.jpg', basename($path));
        Storage::disk('private')->assertExists($path);
    }
}
