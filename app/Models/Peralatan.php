<?php

namespace App\Models;

use App\Enums\CalibrationStatus;
use App\Enums\EquipmentCondition;
use App\Enums\OwnershipStatus;
use App\Enums\PeralatanReviewStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Crypt;

class Peralatan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'peralatans';

    protected $fillable = [
        'code',
        'name',
        'description',
        'location',
        'calibration_status',
        'calibration_expired_date',
        'condition',
        'ownership_status',
        'is_active',
        'review_status',
        'reviewed_by',
        'reviewed_at',
        'rejection_reason',
        'approval_note',
    ];

    protected $casts = [
        'calibration_status' => CalibrationStatus::class,
        'condition' => EquipmentCondition::class,
        'ownership_status' => OwnershipStatus::class,
        'review_status' => PeralatanReviewStatus::class,
        'calibration_expired_date' => 'date',
        'is_active' => 'boolean',
        'reviewed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relationships
    public function evidences(): HasMany
    {
        return $this->hasMany(PeralatanEvidence::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeSearch($query, $search)
    {
        if ($search) {
            return $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
            });
        }
        return $query;
    }

    public function scopeByCalibrationStatus($query, $status)
    {
        if ($status) {
            return $query->where('calibration_status', $status);
        }
        return $query;
    }

    public function scopeByCondition($query, $condition)
    {
        if ($condition) {
            return $query->where('condition', $condition);
        }
        return $query;
    }

    public function scopeByOwnershipStatus($query, $status)
    {
        if ($status) {
            return $query->where('ownership_status', $status);
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
        return $query->where('review_status', PeralatanReviewStatus::Approved->value);
    }

    // Accessors
    public function getIsActiveAttribute(): bool
    {
        return $this->attributes['is_active'] ?? true;
    }

    public function getCalibrationStatusExpiredAttribute(): bool
    {
        if (!$this->calibration_expired_date) {
            return false;
        }
        return $this->calibration_expired_date->isPast();
    }

    public function isPendingReview(): bool
    {
        return $this->review_status === PeralatanReviewStatus::Pending;
    }

    public function isReviewed(): bool
    {
        return $this->review_status === PeralatanReviewStatus::Approved;
    }

    public function isRejected(): bool
    {
        return $this->review_status === PeralatanReviewStatus::Rejected;
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
