<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class AdminQurbanSettingsPageTest extends TestCase
{
    public function test_admin_qurban_settings_page_renders(): void
    {
        $user = User::factory()->make([
            'role' => 'SUPERADMIN',
        ]);

        $response = $this->actingAs($user)->get('/admin/settings/qurban');

        $response->assertStatus(200);
        $response->assertSee('Settings Kurban', false);
        $response->assertSee('Harga, komisi, dan copy kategori', false);
    }
}
