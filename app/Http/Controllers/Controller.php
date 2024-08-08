<?php

namespace App\Http\Controllers;

use App\Jobs\UpdateUniversalisCaches;
use App\Models\UniversalisCache;
use Carbon\Carbon;
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
        if (!UniversalisCache::world($world)->where('updated_at', '>', Carbon::now()->subMinutes(config('ffxiv.universalis.rate_limit_lifetime')))->exists() && UniversalisCache::world($world)->needsUpdate()->exists()) {
            UpdateUniversalisCaches::dispatch($world);

            flash('An update has been queued to fetch the latest price data from Universalis. This page will refresh in two minutes to load the new data.')->success();

            return true;
        }

        return false;
    }
}
