<?php

namespace App\Http\Controllers;

use Parsedown;

class IndexController extends Controller {
    /**
     * Show the index page, including changelog.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getIndex() {
        // Parse the changelog file and format it
        $changelog = (new Parsedown)->text(file_get_contents(base_path('CHANGELOG.md')));
        // Break the changelog into sections by version
        $changelog = explode('<h2>', $changelog);
        // Drop the file header
        unset($changelog[0]);

        $changelog = collect($changelog)->take(5);
        foreach ($changelog as $key => $version) {
            // Re-append the header formatting since explode removes it
            $changelog[$key] = '<h2>'.$version;

            // Adjust header formatting
            $changelog[$key] = str_replace('h3', 'h4', $changelog[$key]);
            $changelog[$key] = str_replace('h2', 'h3', $changelog[$key]);
        }

        return view('index', [
            'changelog' => $changelog,
        ]);
    }
}
