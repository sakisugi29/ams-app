<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CorrectionRequestDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'correction_request_id',
        'field_name',
        'before_value',
        'after_value',
    ];

    public function correctionRequest()
    {
        return $this->belongsTo(CorrectionRequest::class);
    }
}
