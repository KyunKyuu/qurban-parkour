<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('communities', function (Blueprint $table) {
            $table->foreignId('pic_komunitas_id')
                ->nullable()
                ->after('pic_id')
                ->constrained('pics')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('communities', function (Blueprint $table) {
            $table->dropForeign(['pic_komunitas_id']);
            $table->dropColumn('pic_komunitas_id');
        });
    }
};
