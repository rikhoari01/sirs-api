<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardService $dashboardService
    ) {}

    public function index(Request $request)
    {
        $period = $request->query('period');
        $data = $this->dashboardService->getDashboardData($period);

        return $this->successResponse($data, 'Dashboard data successfully fetched');
    }
}
