<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkOrderItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'module_id',
        'order',
        'name',
        'description',
        'nature',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    public function subitems()
    {
        return $this->hasMany(WorkOrderSubitem::class, 'work_order_item_id')->orderBy('order');
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
