<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Report extends Model
{
    protected $fillable = [
        'location',
        'description',
        'category',
        'severity',
        'probability',
        'risk_score',
        'risk_level',
        'status',
        'created_by',
        'updated_by',
        'action_taken',
        'attachments',
    ];

    protected $appends = ['report_date'];

    protected $casts = [
        'attachments' => 'array',
    ];

    protected function reportDate(): Attribute
    {
        return Attribute::make(
            get: fn () => Carbon::parse($this->created_at)->format('d M Y'),
        );
    }

    public function creator(): belongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
