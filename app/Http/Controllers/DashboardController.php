<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $products = Product::orderBy('name')->get();

        return view('dashboard', [
            'products' => $products,
        ]);
    }
}
