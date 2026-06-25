<?php

namespace App\Models;

use App\Enums\ModuleReviewStatus;
use App\Enums\RiskLevel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Crypt;

class Module extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'duration',
        'risk_level',
        'pricing_baseline',
        'is_active',
        'notes',
        'review_status',
        'reviewed_by',
        'reviewed_at',
        'rejection_reason',
        'approval_note',
    ];

    protected $casts = [
        'risk_level' => RiskLevel::class,
        'review_status' => ModuleReviewStatus::class,
        'pricing_baseline' => 'decimal:2',
        'is_active' => 'boolean',
        'reviewed_at' => 'datetime',
    ];

    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_modules')
            ->withPivot(['quantity', 'unit_price', 'subtotal', 'notes'])
            ->withTimestamps();
    }

    public function workOrderItems()
    {
        return $this->hasMany(WorkOrderItem::class)->orderBy('order');
    }

    public function workOrderReferences()
    {
        return $this->hasMany(WorkOrderReference::class);
    }

    public function personels()
    {
        return $this->hasMany(ModulePersonel::class);
    }

    public function tools()
    {
        return $this->hasMany(ModuleTool::class);
    }

    public function deliverables()
    {
        return $this->hasMany(ModuleDeliverable::class)->orderBy('order');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByRiskLevel($query, $riskLevel)
    {
        if ($riskLevel) {
            return $query->where('risk_level', $riskLevel);
        }
        return $query;
    }

    public function scopeSearch($query, $search)
    {
        if ($search) {
            return $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }
        return $query;
    }

    public function scopeByReviewStatus($query, $reviewStatus)
    {
        if ($reviewStatus) {
            return $query->where('review_status', $reviewStatus);
        }
        return $query;
    }

    public function scopeReviewed($query)
    {
        return $query->where('review_status', ModuleReviewStatus::Approved->value);
    }

    public function isPendingReview(): bool
    {
        return $this->review_status === ModuleReviewStatus::Pending;
    }

    public function isReviewed(): bool
    {
        return $this->review_status === ModuleReviewStatus::Approved;
    }

    public function isRejected(): bool
    {
        return $this->review_status === ModuleReviewStatus::Rejected;
    }

    public function getRouteKey()
    {
        return Crypt::encryptString($this->getKey());
    }

    public function resolveRouteBinding($value, $field = null)
    {
        try {
            $decryptedId = Crypt::decryptString($value);
            return $this->where($this->getRouteKeyName(), $decryptedId)->firstOrFail();
        } catch (\Exception $e) {
            abort(404);
        }
    }
}
