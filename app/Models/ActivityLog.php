<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $table = 'activity_logs';

    protected $fillable = [
        'user_id', 'user_name', 'user_role', 'action', 'model', 'model_id',
        'description', 'old_data', 'new_data', 'ip_address', 'user_agent',
        'device', 'url', 'method',
    ];

    protected $casts = [
        'old_data' => 'array',
        'new_data' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getDeviceIconAttribute(): string
    {
        return match(strtolower($this->device ?? '')) {
            'mobile'  => '📱',
            'tablet'  => '📟',
            'desktop' => '🖥️',
            default   => '🌐',
        };
    }

    public function getActionColorAttribute(): string
    {
        return match($this->action) {
            'create'  => 'green',
            'update'  => 'blue',
            'delete'  => 'red',
            'login'   => 'indigo',
            'logout'  => 'gray',
            'export'  => 'purple',
            'validate'=> 'teal',
            default   => 'gray',
        };
    }
}
