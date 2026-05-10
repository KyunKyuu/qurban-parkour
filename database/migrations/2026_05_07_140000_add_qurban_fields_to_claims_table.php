<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('claims', function (Blueprint $table) {
            if (!Schema::hasColumn('claims', 'category_type')) {
                $table->string('category_type', 30)->nullable()->after('phone');
                $table->string('category_label')->nullable()->after('category_type');
                $table->string('patungan_target', 30)->nullable()->after('category_label');
                $table->decimal('unit_price_snapshot', 12, 2)->nullable()->after('patungan_target');
                $table->decimal('contribution_amount', 12, 2)->nullable()->after('unit_price_snapshot');
                $table->string('instagram_username', 100)->nullable()->after('contribution_amount');
                $table->string('certificate_path')->nullable()->after('instagram_username');
                $table->timestamp('certificate_generated_at')->nullable()->after('certificate_path');
                $table->boolean('commission_eligible')->default(false)->after('certificate_generated_at');
                $table->decimal('commission_amount', 12, 2)->default(0)->after('commission_eligible');
                $table->decimal('subsidy_amount', 12, 2)->default(0)->after('commission_amount');
                $table->string('commission_note')->nullable()->after('subsidy_amount');

                $table->index('category_type');
                $table->index('certificate_generated_at');
                $table->index('commission_eligible');
            }
        });
    }

    public function down(): void
    {
        Schema::table('claims', function (Blueprint $table) {
            if (Schema::hasColumn('claims', 'category_type')) {
                $table->dropIndex(['category_type']);
                $table->dropIndex(['certificate_generated_at']);
                $table->dropIndex(['commission_eligible']);
                $table->dropColumn([
                    'category_type',
                    'category_label',
                    'patungan_target',
                    'unit_price_snapshot',
                    'contribution_amount',
                    'instagram_username',
                    'certificate_path',
                    'certificate_generated_at',
                    'commission_eligible',
                    'commission_amount',
                    'subsidy_amount',
                    'commission_note',
                ]);
            }
        });
    }
};
