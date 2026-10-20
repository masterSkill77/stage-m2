<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Une demande d'ami reste en attente tant que l'autre personne ne l'a pas acceptée.
     * Les amitiés existantes sont considérées comme déjà acceptées.
     */
    public function up(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->timestamp('accepted_at')->nullable()->after('friend_id');
        });

        DB::table('contacts')->update(['accepted_at' => DB::raw('created_at')]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropColumn('accepted_at');
        });
    }
};
