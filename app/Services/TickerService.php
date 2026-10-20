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

    /**
     * Bougies historiques d'une paire (cache 5 minutes).
     * Lève une exception si Binance ne répond pas, sans rien mettre en cache.
     */
    public function history(string $symbol, string $interval, int $limit): array
    {
        return Cache::remember("klines:{$symbol}:{$interval}:{$limit}", 300, function () use ($symbol, $interval, $limit) {
            $response = Http::timeout(10)->get('https://api.binance.com/api/v3/klines', [
                'symbol' => $symbol,
                'interval' => $interval,
                'limit' => $limit,
            ]);

            if ($response->failed()) {
                throw new \RuntimeException('Binance klines indisponible');
            }

            return collect($response->json())->map(fn ($kline) => [
                'time' => (int) $kline[0],
                'open' => (float) $kline[1],
                'high' => (float) $kline[2],
                'low' => (float) $kline[3],
                'close' => (float) $kline[4],
            ])->values()->all();
        });
    }
}
