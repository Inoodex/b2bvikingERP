<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomProductRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_no',
        'user_id',
        'product_name',
        'product_description',
        'example_image',
        'quantity_needed',
        'expected_price',
        'status',
        'admin_note'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getExampleImageAttribute($value)
    {
        if (empty($value)) {
            return [];
        }

        if (is_array($value)) {
            return $value;
        }

        $decoded = json_decode($value, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }

        return [$value];
    }

    public function exampleImagePaths(): array
    {
        return array_values(array_filter(array_map(
            fn ($image) => $this->normalizeExampleImagePath($image),
            $this->example_image ?? []
        )));
    }

    public function resolveExampleImagePath(int $index): ?string
    {
        $images = $this->example_image ?? [];

        if (!array_key_exists($index, $images)) {
            return null;
        }

        return $this->normalizeExampleImagePath($images[$index]);
    }

    /**
     * Get the status badge class for display
     */
    public function getStatusBadgeAttribute()
    {
        switch ($this->status) {
            case 'pending':
                return 'badge-warning';
            case 'approved':
                return 'badge-success';
            case 'rejected':
                return 'badge-danger';
            default:
                return 'badge-secondary';
        }
    }

    /**
     * Get the status label for display
     */
    public function getStatusLabelAttribute()
    {
        return ucfirst($this->status);
    }

    private function normalizeExampleImagePath($image): ?string
    {
        if (is_array($image)) {
            $image = $image['path'] ?? $image['url'] ?? ($image[0] ?? null);
        }

        if (!is_string($image)) {
            return null;
        }

        $image = trim($image);

        return $image !== '' ? $image : null;
    }
}
