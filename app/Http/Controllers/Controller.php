<?php

namespace App\Http\Controllers;

use App\Jobs\UpdateUniversalisCaches;
use App\Models\UniversalisCache;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;

abstract class Controller {
    /**
     * Create a new controller instance.
     */
    public function __construct() {
        // Flash any errors
        if (Session::get('errors')) {
            foreach (Session::get('errors')->all() as $message) {
                flash($message)->error();
            }
        }
    }

    /**
     * Dispatches Universalis cache updates for a world if necessary and informs the user.
     *
     * @param string $world
     *
     * @return bool
     */
    public function checkUniversalisCache($world) {
        if (!UniversalisCache::world($world)->where('updated_at', '>', Carbon::now()->subMinutes(config('ffxiv.economy.universalis.rate_limit_lifetime')))->exists() && UniversalisCache::world($world)->needsUpdate()->exists()) {
            UpdateUniversalisCaches::dispatch($world);

            flash('An update has been queued to fetch the latest price data from Universalis. This page will refresh in one minute to load the new data.')->success();

            return true;
        }

        return false;
    }

    /**
     * Handles cookie handling/information retrieval for a given request.
     *
     * @param string $name
     * @param array  $inputs
     *
     * @return Request $request
     */
    public function handleSettingsCookie(Request $request, $name, $inputs) {
        // If there's an extant cookie, fetch and decode settings stored on it
        if (Cookie::get($name)) {
            $cookieInputs = json_decode(Cookie::get($name), true);
        }

        // Determine whether or not this is a complete restore from cookie
        $isRestore = count($request->all()) ? false : true;

        foreach (array_keys($inputs) as $value) {
            if (!$isRestore && !$request->get($value)) {
                // Handle input(s) unset in the incoming request, as otherwise these will be treated as absent and re-set (if previously set)
                unset($inputs[$value]);
            } elseif ($request->get($value) !== null) {
                // If set in the incoming request, just set the input from the request to be stored
                $inputs[$value] = $request->get($value);
            } elseif (isset($cookieInputs[$value])) {
                // Otherwise retrieve prior input from cookie and both store it for later and add it to the request
                $inputs[$value] = $cookieInputs[$value];
                $request->merge([$value => $cookieInputs[$value]]);
            } else {
                // Otherwise, unset the value entirely so that only relevant information is saved to the cookie
                unset($inputs[$value]);
            }
        }

        // Queue the cookie itself with the assembled data
        Cookie::queue($name, json_encode($inputs), 2592000);

        return $request;
    }
}
