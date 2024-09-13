<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

final class HomeController extends Controller
{
    public function __invoke(Request $request): string
    {
        return 'Hello from Laravel!';
    }
}
