<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Rôle administrateur (superadmin) et désactivation des packs sans suppression.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_admin')->default(false)->after('pack_id');
        });

        Schema::table('packs', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('pack_max_bid');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_admin');
        });

        Schema::table('packs', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
    }
};
