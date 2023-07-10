<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class TickerService
{
    public function show()
    {
        $lastUpdatedTime = Cache::get('date_last_ticker');
        $tickerCached = Cache::get('ticker');
        if (time() - $lastUpdatedTime > 24 * 60 * 60 || !$tickerCached) {
            Cache::put('date_last_ticker', time());
            $response = $this->getFromBinance();
            Cache::put('ticker', $response);
            $tickerCached = $response;
        }
        return $tickerCached;
    }
    public function getFromBinance()
    {
        $response = Http::get('https://api.binance.com/api/v3/ticker/24hr');
        return array_slice([...$response->json()], 0, 15);
    }
}
