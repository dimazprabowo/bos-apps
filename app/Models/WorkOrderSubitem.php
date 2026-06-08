<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkOrderSubitem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'work_order_item_id',
        'order',
        'name',
        'description',
        'nature',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function workOrderItem()
    {
        return $this->belongsTo(WorkOrderItem::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    public function getNatureLabelAttribute(): string
    {
        return $this->nature === 'mandatory' ? 'Wajib' : 'Opsional';
    }
}
