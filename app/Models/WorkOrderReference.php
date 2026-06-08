<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkOrderReference extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'module_id',
        'document_name',
        'document_id',
        'file_path',
        'file_name',
        'file_size',
        'file_status',
        'file_processed_at',
        'file_error',
    ];

    public function module()
    {
        return $this->belongsTo(Module::class);
    }
}
