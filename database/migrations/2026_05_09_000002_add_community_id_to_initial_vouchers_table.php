<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('initial_vouchers', function (Blueprint $table) {
            $table->foreignId('community_id')
                ->nullable()
                ->after('assigned_pic_id')
                ->constrained('communities')
                ->onDelete('set null');

            $table->index('community_id');
        });
    }

    public function down(): void
    {
        Schema::table('initial_vouchers', function (Blueprint $table) {
            $table->dropForeign(['community_id']);
            $table->dropIndex(['community_id']);
            $table->dropColumn('community_id');
        });
    }
};
