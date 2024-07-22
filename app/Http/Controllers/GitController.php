<?php

namespace App\Http\Controllers;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class GitController extends Controller
{
    public function showSearchForm()
    {
        return view('search-user');
    }
    // Get GitHub index user's

    /**
     * @throws ConnectionException
     */
    public function searchUser(Request $request) {
        $username = $request->input('username');

        if (empty($username)) {
            return response()->json(['error' => 'Username is required'], 400);
        }

//        dd($username);

        $url = 'https://api.github.com/search/users?q=' . $username;

        $response = Http::withHeaders([
            'User-Agent' => 'Laravel App',
        ])->get($url);

//        dd($response->status());

        if ($response->failed()) {
            return response()->json(['error' => 'User not found or other error'], $response->status());
        }

        $data = $response->json();

        if ($data['total_count'] == 0) {
            return response()->json(['error' => 'Username not found'], 404);
        }

        $followersUrl = 'https://api.github.com/users/' . $username . ' /followers';

        $followersResponse = Http::withHeaders([
            'User-Agent' => 'Laravel App',
        ])->get($followersUrl);

        if ($followersResponse->header('Link') && count($followersResponse->json()) > 0) {
            $allData = array_merge($data['items'][0], [ 'followers' => $followersResponse->json(), 'follower_count' => count($followersResponse->json()) ]);

            return response()->json($allData);
        } else {
            return response()->json($data['items'][0]);
        }

//        $allData = array_merge($data['items'][0], $followersResponse->headers()['Link']);
    }
}
