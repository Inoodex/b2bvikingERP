<?php

namespace Tests\Feature\Phase6;

use App\Models\GeneralSetting;
use App\Models\Product;
use App\Models\SystemBackupLog;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SystemControlsAndMaintenanceTest extends TestCase
{
    use DatabaseTransactions;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $role = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);

        $this->adminUser = User::firstOrCreate(
            ['email' => 'phase6admin_controls@b2bviking.dk'],
            [
                'name'     => 'Phase 6 Admin',
                'password' => bcrypt('password123'),
                'role_id'  => 1,
            ]
        );

        if (!$this->adminUser->hasRole('Admin')) {
            $this->adminUser->assignRole($role);
        }
    }

    #[Test]
    public function test_feature_toggles_screen_and_ajax_toggle()
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.settings.feature-toggles'));

        $response->assertStatus(200);
        $response->assertSee('Feature Toggles Switchboard');

        // Toggle PayPal switch off
        $ajaxResponse = $this->actingAs($this->adminUser)
            ->postJson(route('admin.settings.feature-toggles.toggle'), [
                'feature' => 'feature_paypal_checkout',
                'enabled' => false,
            ]);

        $ajaxResponse->assertStatus(200);
        $ajaxResponse->assertJson([
            'success' => true,
            'enabled' => false,
        ]);

        $this->assertFalse(is_feature_enabled('feature_paypal_checkout'));

        // Toggle back on
        $ajaxResponse2 = $this->actingAs($this->adminUser)
            ->postJson(route('admin.settings.feature-toggles.toggle'), [
                'feature' => 'feature_paypal_checkout',
                'enabled' => true,
            ]);

        $ajaxResponse2->assertStatus(200);
        $this->assertTrue(is_feature_enabled('feature_paypal_checkout'));
    }

    #[Test]
    public function test_database_backup_lifecycle_with_yajra_datatable()
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.backups.index'));

        $response->assertStatus(200);
        $response->assertSee('Database Backups &amp; Disaster Recovery', false);
        $response->assertSee('backup-table');

        // Generate backup
        $createResponse = $this->actingAs($this->adminUser)
            ->post(route('admin.backups.create'));

        $createResponse->assertRedirect(route('admin.backups.index'));

        $latestBackup = SystemBackupLog::orderBy('id', 'desc')->first();
        $this->assertNotNull($latestBackup);
        $this->assertEquals('completed', $latestBackup->status);

        $filePath = storage_path('app/' . $latestBackup->file_path);
        $this->assertTrue(File::exists($filePath));
        $this->assertGreaterThan(0, filesize($filePath));

        // Download backup
        $downloadResponse = $this->actingAs($this->adminUser)
            ->get(route('admin.backups.download', $latestBackup->id));

        $downloadResponse->assertStatus(200);
        $downloadResponse->assertHeader('content-type', 'application/sql');

        // Delete backup
        $deleteResponse = $this->actingAs($this->adminUser)
            ->delete(route('admin.backups.destroy', $latestBackup->id));

        $deleteResponse->assertRedirect(route('admin.backups.index'));
        $this->assertDatabaseMissing('system_backup_logs', ['id' => $latestBackup->id]);
        $this->assertFalse(File::exists($filePath));
    }

    #[Test]
    public function test_universal_recycle_bin_restore_lifecycle_with_yajra_datatable()
    {
        $product = Product::create([
            'name'           => 'Recycle Bin Test Product ' . rand(100, 999),
            'slug'           => 'recycle-bin-test-' . rand(1000, 9999),
            'product_number' => 'PRD-TEST-' . rand(1000, 9999),
            'sku'            => 'SKU-TEST-' . rand(1000, 9999),
            'price'          => 150.00,
            'status'         => 1,
        ]);

        // Soft delete the product
        $product->delete();
        $this->assertSoftDeleted('products', ['id' => $product->id]);

        // Check Recycle Bin screen with Yajra table
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.recycle-bin.index', ['type' => 'products']));

        $response->assertStatus(200);
        $response->assertSee('recyclebin-table');

        // AJAX Restore product
        $restoreResponse = $this->actingAs($this->adminUser)
            ->postJson(route('admin.recycle-bin.restore', ['type' => 'products', 'id' => $product->id]));

        $restoreResponse->assertStatus(200);
        $restoreResponse->assertJson(['success' => true]);
        $this->assertNotSoftDeleted('products', ['id' => $product->id]);

        // Force delete
        $product->delete();
        $forceDeleteResponse = $this->actingAs($this->adminUser)
            ->delete(route('admin.recycle-bin.force-delete', ['type' => 'products', 'id' => $product->id]));

        $forceDeleteResponse->assertRedirect(route('admin.recycle-bin.index', ['type' => 'products']));
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }
}
