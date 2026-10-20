<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    // Garde-fou : les tests réinitialisent la base (RefreshDatabase), ils ne doivent jamais viser la base de dev
    protected function setUp(): void
    {
        // Vérifié avant parent::setUp(), car RefreshDatabase s'exécute pendant ce appel
        if (env('DB_DATABASE') !== 'auction_test') {
            throw new \RuntimeException('Refus : les tests ne peuvent viser que la base auction_test (DB_DATABASE='.env('DB_DATABASE').').');
        }

        parent::setUp();
    }

    use CreatesApplication;
}
