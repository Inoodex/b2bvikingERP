<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemBackupLog extends Model
{
    use HasFactory;

    protected $table = 'system_backup_logs';

    protected $fillable = [
        'file_name',
        'disk',
        'file_path',
        'file_size_bytes',
        'backup_type',
        'status',
        'triggered_by',
        'completed_at',
    ];

    protected $casts = [
        'file_size_bytes' => 'integer',
        'completed_at'    => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'triggered_by');
    }
}
