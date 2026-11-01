<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Dashboard awal (F0: kerangka). Widget buffer Minggu &
     * grafik stok harian diisi pada fase F4.
     */
    public function index(Request $request): View
    {
        return view('dashboard.index', [
            'user' => $request->user(),
        ]);
    }
}
