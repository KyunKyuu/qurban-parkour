<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class LegacyFlowRetirementTest extends TestCase
{
    public function test_admin_analytics_redirects_to_qurban_dashboard(): void
    {
        $user = User::factory()->make([
            'role' => 'SUPERADMIN',
        ]);

        $response = $this->actingAs($user)->get('/admin/analytics');

        $response->assertRedirect('/admin');
        $response->assertSessionHas('error');
    }

    public function test_admin_redeems_redirect_to_claims_page(): void
    {
        $user = User::factory()->make([
            'role' => 'SUPERADMIN',
        ]);

        $response = $this->actingAs($user)->get('/admin/redeems');

        $response->assertRedirect('/admin/claims');
        $response->assertSessionHas('error');
    }

    public function test_admin_merchants_redirect_to_dashboard(): void
    {
        $user = User::factory()->make([
            'role' => 'SUPERADMIN',
        ]);

        $response = $this->actingAs($user)->get('/admin/merchants');

        $response->assertRedirect('/admin');
        $response->assertSessionHas('error');
    }

    public function test_merchant_dashboard_shows_retired_page(): void
    {
        $user = User::factory()->make([
            'role' => 'MERCHANT',
        ]);

        $response = $this->actingAs($user)->get('/merchant');

        $response->assertStatus(410);
        $response->assertSee('Portal merchant lama sudah dipensiunkan.', false);
    }

    public function test_merchant_scan_validate_returns_gone_json(): void
    {
        $user = User::factory()->make([
            'role' => 'MERCHANT',
        ]);

        $response = $this->actingAs($user)->postJson('/merchant/scan/validate', [
            'code' => 'TEST123',
        ]);

        $response->assertStatus(410);
        $response->assertJson([
            'success' => false,
            'message' => 'Portal merchant lama sudah dipensiunkan.',
        ]);
    }
}
