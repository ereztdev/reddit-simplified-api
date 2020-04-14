<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class RedditController extends Controller
{
    public function index(Request $request)
    {
        $requestedSubReddit = $request->path();
        $response = Http::withHeaders([
            'User-Agent' => 'android:com.example.myredditapp:v1.2.3 (by /u/erez)'
        ])->get("http://www.reddit.com/r/$requestedSubReddit.json");
        $redditResponseBody = json_decode($response->body());
        if (isset($redditResponseBody->error) || count($redditResponseBody->data->children) === 0) {
            $redditResponseBody = null;
        }
        return view('redditExtrapolator')->with('redditResponseBody', $redditResponseBody);
    }

    public function lazyLoad(Request $request)
    {
        dd($request);

    }
}
