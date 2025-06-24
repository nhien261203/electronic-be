<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->boolean('status')->default(1)->after('parent_id'); // 1 = hiển thị, 0 = ẩn
        });

        Schema::table('brands', function (Blueprint $table) {
            $table->boolean('status')->default(1)->after('country'); // 1 = hiển thị, 0 = ẩn
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('brands', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
