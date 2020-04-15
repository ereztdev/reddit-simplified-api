<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class RedditController extends Controller
{
    public function index(Request $request)
    {
        $requestedSubReddit = $request->path();
        $redditResponseBody = $this->getPosts($requestedSubReddit);

        return view('redditExtrapolator')->with('redditResponseBody', $redditResponseBody);
    }

    public function lazyLoad(Request $request)
    {
        return json_encode($this->getPosts($request->subreddit, $request->limit, $request->type, $request->hash));
    }

    private function getPosts($requestedSubReddit, $limit = 25, $beforeOrAfter = null, $hash = null)
    {
        $baseUrl = "http://www.reddit.com/r/$requestedSubReddit.json?limit=$limit&$beforeOrAfter=$hash";
        $response = Http::withHeaders([
            'User-Agent' => 'android:com.example.myredditapp:v1.2.3 (by /u/erez)'
        ])->timeout(5)->get($baseUrl);
        $redditResponseBody = json_decode($response->body());
        if (isset($redditResponseBody->error) || count($redditResponseBody->data->children) === 0) {
            $redditResponseBody = null;
        }
        return $redditResponseBody;
    }

    public function reloadPosts(Request $request)
    {
        return json_encode($this->getPosts($request->subreddit, $request->limit));
    }
}
