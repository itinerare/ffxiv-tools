<?php

namespace App\Http\Controllers;

class IndexController extends Controller {
    /**
     * Show the index page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getIndex() {
        return view('index');
    }
}
