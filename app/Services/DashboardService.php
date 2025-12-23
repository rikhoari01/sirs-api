<?php

namespace App\Services;

use App\Models\Report;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    protected string $period;
    protected bool $isAdmin;

    public function getDashboardData(string $period = 'weekly'): array
    {
        $this->period = $period;
        $this->isAdmin = auth()->user()->hasAnyRole(['Admin', 'SPV']);

        return [
            'statistic_data' => $this->getReportStatistics(),
            'bar_chart_data' => $this->getBarChartData(),
            'pie_chart_data' => $this->getPieChartData(),
            'line_chart_data' => $this->getLineChartData(),
        ];
    }

    protected function getReportStatistics(): array
    {
        [$start] = $this->resolvePeriod($this->period);

        $data = Report::query()
            ->where('created_at', '>=', $start)
            ->where('status', '!=', 'draft')
            ->when(!$this->isAdmin, function ($query) {
                $query->where('created_by', auth()->user()->id);
            })
            ->select('status')
            ->selectRaw('count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        return [
            'submitted' => $data['submitted'] ?? 0,
            'reviewed'  => $data['reviewed'] ?? 0,
            'closed'    => $data['closed'] ?? 0,
        ];
    }

    protected function getBarChartData(): array
    {
        [$start, $groupBy, $categories] = $this->resolvePeriod($this->period);

        $data = Report::query()
            ->select(
                DB::raw("$groupBy as label"),
                DB::raw("SUM(CASE WHEN risk_level = 'Low' THEN 1 ELSE 0 END) as low"),
                DB::raw("SUM(CASE WHEN risk_level = 'Medium' THEN 1 ELSE 0 END) as medium"),
                DB::raw("SUM(CASE WHEN risk_level = 'High' THEN 1 ELSE 0 END) as high"),
                DB::raw("SUM(CASE WHEN risk_level = 'Critical' THEN 1 ELSE 0 END) as critical")
            )
            ->where('created_at', '>=', $start)
            ->where('status', '!=', 'draft')
            ->when(!$this->isAdmin, function ($query) {
                $query->where('created_by', auth()->user()->id);
            })
            ->groupBy('label')
            ->orderBy('label')
            ->get()
            ->keyBy('label');

        $low = $medium = $high = $critical = [];
        foreach ($categories as $index => $cat) {
            $key = $cat;
            if ($this->period !== 'Yearly') {
                $key = $index;
            }

            $low[] = $data[$key]->low ?? 0;
            $medium[] = $data[$key]->medium ?? 0;
            $high[] = $data[$key]->high ?? 0;
            $critical[] = $data[$key]->critical ?? 0;
        }

        return [
            'categories' => $categories,
            'series' => [
                ['name' => 'Low', 'data' => $low],
                ['name' => 'Medium', 'data' => $medium],
                ['name' => 'High', 'data' => $high],
                ['name' => 'Critical', 'data' => $critical],
            ]
        ];
    }

    protected function getPieChartData(): array
    {
        [$start] = $this->resolvePeriod($this->period);

        $data = Report::query()
            ->select('risk_level', DB::raw('COUNT(*) as total'))
            ->where('created_at', '>=', $start)
            ->where('status', '!=', 'draft')
            ->when(!$this->isAdmin, function ($query) {
                $query->where('created_by', auth()->user()->id);
            })
            ->groupBy('risk_level')
            ->pluck('total', 'risk_level');

        return [
            'labels' => ['Low', 'Medium', 'High', 'Critical'],
            'series' => [
                $data['Low'] ?? 0,
                $data['Medium'] ?? 0,
                $data['High'] ?? 0,
                $data['Critical'] ?? 0
            ]
        ];
    }

    protected function getLineChartData(): array
    {
        [$start, $groupBy, $categories] = $this->resolvePeriod($this->period);

        $data = Report::query()
            ->select(
                DB::raw("$groupBy as label"),
                'category',
                DB::raw('COUNT(*) as total')
            )
            ->where('created_at', '>=', $start)
            ->where('status', '!=', 'draft')
            ->when(!$this->isAdmin, function ($query) {
                $query->where('created_by', auth()->id());
            })
            ->groupBy('label', 'category')
            ->orderBy('label')
            ->get();

        $series = collect($data)
            ->groupBy('category')
            ->map(function ($items, $category) use ($categories) {
                return [
                    'name' => $category,
                    'data' => collect($categories)->map(function ($label, $index) use ($items) {
                        $key = $label;
                        if ($this->period !== 'Yearly') {
                            $key = $index;
                        }
                        return $items->firstWhere('label', $key)->total ?? 0;
                    })->values()
                ];
            })
            ->values();

        return [
            'categories' => $categories,
            'series' => $series
        ];
    }

    private function resolvePeriod(string $period): array
    {
        $driver = DB::getDriverName();

        switch ($period) {
            case 'Weekly':
                $startOfWeek = Carbon::now()->startOfWeek();
                $categories = [];
                for ($i = 0; $i < 7; $i++) {
                    $categories[] = $startOfWeek->copy()->addDays($i)->format('D');
                }
                return [
                    $startOfWeek,
                    $this->dayOfWeekExpression($driver),
                    $categories
                ];

            case 'Yearly':
                return [
                    Carbon::now()->subYears(3)->startOfYear(),
                    $this->yearExpression($driver),
                    [
                        now()->subYears(3)->year,
                        now()->subYears(2)->year,
                        now()->subYears(1)->year,
                        now()->year
                    ]
                ];

            default: // Monthly
                return [
                    Carbon::now()->startOfYear(),
                    $this->monthExpression($driver),
                    ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec']
                ];
        }
    }

    private function dayOfWeekExpression(string $driver): string
    {
        return match($driver) {
            'sqlite' => "strftime('%w', created_at) - 1",
            'pgsql' => 'EXTRACT(DOW FROM created_at) - 1',
            default => 'DAYOFWEEK(created_at) - 1',
        };
    }

    private function monthExpression(string $driver): string
    {
        return match($driver) {
            'sqlite' => "strftime('%m', created_at) - 1",
            'pgsql' => 'EXTRACT(MONTH FROM created_at) - 1',
            default => 'MONTH(created_at)',
        };
    }

    private function yearExpression(string $driver): string
    {
        return match($driver) {
            'sqlite' => "strftime('%Y', created_at)",
            'pgsql' => 'EXTRACT(YEAR FROM created_at)',
            default => 'YEAR(created_at)',
        };
    }
}
