<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;

class HomeController extends BaseController
{
    public function index()
    {
        // Jika sudah ada session login, arahkan langsung ke presentation/u/0/
        if (session()->has('user_id')) {
            return redirect()->to('/presentation/u/0/');
        }

        return view('pages/home', [
            'title'              => 'Masuk — LeafDeck',
            'googleLoginEnabled' => filter_var(env('GOOGLE_LOGIN_ENABLED', false), FILTER_VALIDATE_BOOLEAN),
        ]);
    }
}
