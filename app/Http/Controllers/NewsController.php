<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class NewsController extends Controller
{
    public function index()
    {
        $lastUpdatedTime = Cache::get('date_last_news');
        $newsCached = Cache::get('news');
        if (time() - $lastUpdatedTime > 24 * 60 * 60 || !$newsCached) {
            Cache::put('date_last_news', time());
            $response = Http::get(env('CRYPTO_NEWS'));
            Cache::put('news', $response['results']);
            $newsCached = $response['results'];
        }
        return response()->json($newsCached);
    }
}
