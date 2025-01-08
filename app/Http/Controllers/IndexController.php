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
        $changelogRaw = file_get_contents(base_path('CHANGELOG.md'));
        $changelog = (new Parsedown)->text($changelogRaw);
        // Break the changelog into sections by version
        $changelog = explode('<h2>', $changelog);
        // Drop the file header
        unset($changelog[0]);

        $changelog = collect($changelog)->take(6);
        foreach ($changelog as $key => $entry) {
            if ($key == 6) {
                // Get the version string for the version that would be omitted
                $version = '['.substr($entry, strpos($entry, '">') + 2, strpos($entry, '</a>') - (strpos($entry, '">') + 2));

                // Get the full header string from the raw changelog,
                // and format it to match the anchor link
                $changelog[$key] = substr($changelogRaw, strpos($changelogRaw, $version));
                $changelog[$key] = substr($changelog[$key], 0, strpos($changelog[$key], "\n"));
                foreach (['://' => '-', '...' => '-', '.' => '-', '(' => '-', '/' => '-', '[' => '', ']' => '', ')' => '', ' ' => ''] as $search => $replace) {
                    $changelog[$key] = str_replace($search, $replace, $changelog[$key]);
                }
            } else {
                // Re-append the header formatting since explode removes it
                $changelog[$key] = '<h2>'.$changelog[$key];

                // Adjust header formatting
                foreach (['h3' => 'h4', 'h2' => 'h3'] as $search => $replace) {
                    $changelog[$key] = str_replace($search, $replace, $changelog[$key]);
                }
            }
        }

        return view('index', [
            'changelog' => $changelog,
        ]);
    }
}
