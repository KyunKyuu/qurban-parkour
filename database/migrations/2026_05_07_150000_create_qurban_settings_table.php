<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qurban_settings', function (Blueprint $table) {
            $table->id();
            $table->string('campaign_name')->nullable();
            $table->text('campaign_subtitle')->nullable();
            $table->text('campaign_tagline')->nullable();
            $table->boolean('claim_open')->nullable();
            $table->dateTime('closing_at')->nullable();
            $table->string('closing_label')->nullable();
            $table->string('default_pic_name')->nullable();
            $table->string('default_pic_label')->nullable();
            $table->string('default_pic_email')->nullable();
            $table->string('bank_account_label')->nullable();
            $table->string('certificate_title')->nullable();
            $table->text('certificate_subtitle')->nullable();
            $table->json('patungan_targets')->nullable();
            $table->json('categories')->nullable();
            $table->timestamps();
        });

        $defaults = config('qurban', []);

        DB::table('qurban_settings')->insert([
            'id' => 1,
            'campaign_name' => $defaults['campaign_name'] ?? null,
            'campaign_subtitle' => $defaults['campaign_subtitle'] ?? null,
            'campaign_tagline' => $defaults['campaign_tagline'] ?? null,
            'claim_open' => $defaults['claim_open'] ?? true,
            'closing_at' => $defaults['closing_at'] ?? null,
            'closing_label' => $defaults['closing_label'] ?? null,
            'default_pic_name' => $defaults['default_pic_name'] ?? null,
            'default_pic_label' => $defaults['default_pic_label'] ?? null,
            'default_pic_email' => $defaults['default_pic_email'] ?? null,
            'bank_account_label' => $defaults['bank_account_label'] ?? null,
            'certificate_title' => $defaults['certificate_title'] ?? null,
            'certificate_subtitle' => $defaults['certificate_subtitle'] ?? null,
            'patungan_targets' => json_encode($defaults['patungan_targets'] ?? []),
            'categories' => json_encode($defaults['categories'] ?? []),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('qurban_settings');
    }
};
