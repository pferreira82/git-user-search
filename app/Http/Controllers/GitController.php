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
        $followersRemainingOnLastPage = 0;
        $lastPage = null;
        $totalNumberOfFollowers = 0;
        $nextPage = 0;

        if (empty($username)) {

            return response()->json(['error' => 'Username is required'], 400);

        }

        $url = 'https://api.github.com/search/users?q=' . $username;

        $response = Http::withHeaders([
            'User-Agent' => 'Laravel App',
        ])->get($url);

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

        if (!$followersResponse->header('Link') && count($followersResponse->json()) > 0) {

            $allData = array_merge($data['items'][0], [ 'followers' => $followersResponse->json(), 'follower_count' => count($followersResponse->json()) ]);

            return response()->json($allData);

        } elseif ($followersResponse->header('Link') && count($followersResponse->json()) > 0) {
            $links = explode(',', $followersResponse->header('Link'));

            // Make this into its own global function
            foreach ($links as $link) {
                if (strpos($link, 'rel="next"') !== false) {
                    preg_match('/<([^>]+)>/', $link, $matches);
                    $nextUrl = $matches[1] ?? null;
                    if ($nextUrl) {
                        $parsedUrl = parse_url($nextUrl);
                        parse_str($parsedUrl['query'], $queryParams);
                        $nextPage = $queryParams['page'] ?? null;
                    }
                }
                if (strpos($link, 'rel="last"') !== false) {
                    preg_match('/<([^>]+)>/', $link, $matches);
                    $lastUrl = $matches[1] ?? null;
                    if ($lastUrl) {
                        $parsedUrl = parse_url($lastUrl);
                        parse_str($parsedUrl['query'], $queryParams);
                        $lastPage = $queryParams['page'] ?? null;
                    }
                    break;
                }
            }

            // Calculate number of followers
            if ($lastPage !== null) {
                $followersOnLastPageUrl = 'https://api.github.com/users/' . $username . ' /followers' . '?page=' . $lastPage;
                $followersOnLastPageResponse = Http::withHeaders([
                    'User-Agent' => 'Laravel App',
                ])->get($followersOnLastPageUrl);

                if ($followersOnLastPageResponse->successful()) {
                    $followersRemainingOnLastPage = 30 - count($followersOnLastPageResponse->json());
                }

                $totalNumberOfFollowers = (($lastPage - 1) * 30) + $followersRemainingOnLastPage;

            }

            $allData = array_merge($data['items'][0], [ 'followers' => $followersResponse->json(), 'follower_count' => $totalNumberOfFollowers, 'link' => $followersResponse->header('Link'), 'links' => $links, 'last_page' => $lastPage, 'next_page' => (int)$nextPage ]);
            return response()->json($allData);

        } else {
            return array_merge($data['items'][0], [ 'follower_count' => 0 ]);
        }
    }

        public function loadMoreFollowers(Request $request)
        {
            $username = $request->input('username');
            $nextPage = $request->input('nextPage');
//            return response()->json($request['nextPage']);

            $followersUrl = 'https://api.github.com/users/' . $username . ' /followers?page=' . $nextPage;

            $followersResponse = Http::withHeaders([
                'User-Agent' => 'Laravel App',
            ])->get($followersUrl);

            if ($followersResponse->failed() || isset($followersResponse->json()['message'])) {
                return response()->json(['error' => 'Followers not found'], 404);
            }

            $data = $followersResponse->json();
            $link = $followersResponse->header('Link');

            if (!$followersResponse->header('Link') && count($followersResponse->json()) > 0) {

                $allData = array_merge($data['items'][0], [ 'followers' => $followersResponse->json(), 'follower_count' => count($followersResponse->json()) ]);

                return response()->json($allData);

            } elseif ($followersResponse->header('Link') && count($followersResponse->json()) > 0) {
                $links = explode(',', $followersResponse->header('Link'));
            }

            foreach ($links as $link) {
                if (strpos($link, 'rel="next"') !== false) {
                    preg_match('/<([^>]+)>/', $link, $matches);
                    $nextUrl = $matches[1] ?? null;
                    if ($nextUrl) {
                        $parsedUrl = parse_url($nextUrl);
                        parse_str($parsedUrl['query'], $queryParams);
                        $nextPage = $queryParams['page'] ?? null;
                    }
                }
                if (strpos($link, 'rel="last"') !== false) {
                    preg_match('/<([^>]+)>/', $link, $matches);
                    $lastUrl = $matches[1] ?? null;
                    if ($lastUrl) {
                        $parsedUrl = parse_url($lastUrl);
                        parse_str($parsedUrl['query'], $queryParams);
                        $lastPage = $queryParams['page'] ?? null;
                    }
                    break;
                }
            }

            return response()->json([
                'followers_list' => $data,
                'link' => $link,
                'next_page' => $nextPage,
            ]);
        }
}
