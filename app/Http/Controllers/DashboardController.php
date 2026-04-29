<?php

namespace App\Http\Controllers;

use App\Actions\Dashboard\BuildDashboardDataAction;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request, BuildDashboardDataAction $buildDashboardDataAction): View
    {
        return view('dashboard', $buildDashboardDataAction->execute(
            (string) $request->query('period', 'month')
        ));
    }
}
