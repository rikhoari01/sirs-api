<?php

namespace App\Repositories\Eloquent;

use App\Models\Report;

class ReportRepository extends BaseRepository
{
    public function __construct()
    {
        $this->model = new Report();
    }
}
