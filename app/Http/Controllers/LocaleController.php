<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    public function set(Request $request, string $locale): RedirectResponse
    {
        session(['locale' => $locale]);
        return back();
    }
}
