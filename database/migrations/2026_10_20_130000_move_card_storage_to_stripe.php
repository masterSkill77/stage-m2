<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Les numéros de carte et CVC ne sont plus stockés : la carte est enregistrée chez Stripe,
     * on ne garde que l'identifiant du moyen de paiement et de quoi l'afficher.
     */
    public function up(): void
    {
        Schema::table('user_configs', function (Blueprint $table) {
            $table->string('stripe_customer_id')->nullable()->after('user_id');
            $table->string('stripe_payment_method_id')->nullable()->after('stripe_customer_id');
            $table->string('card_brand', 32)->nullable();
            $table->string('card_last4', 4)->nullable();
            $table->dropColumn(['card_number', 'card_expires_month', 'card_expires_year', 'cvc']);
        });
    }

    /**
     * Les anciennes données de carte ne peuvent pas être restaurées.
     */
    public function down(): void
    {
        Schema::table('user_configs', function (Blueprint $table) {
            $table->string('card_number')->nullable();
            $table->string('card_expires_month')->nullable();
            $table->string('card_expires_year')->nullable();
            $table->string('cvc')->nullable();
            $table->dropColumn(['stripe_customer_id', 'stripe_payment_method_id', 'card_brand', 'card_last4']);
        });
    }
};
