<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    protected $fillable = ['key', 'value'];

    protected $casts = ['value' => 'json'];

    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
    }

    // Default agent page access flags
    public static function defaultPageAccess(): array
    {
        return [
            'reports'         => true,
            'agents'          => true,
            'knowledge_read'  => true,
            'knowledge_write' => true,
            'settings'        => true,
        ];
    }

    // All routing departments
    public static function allDepartments(): array
    {
        return ['IT', 'HR', 'Finance', 'OPIC', 'Dispatch', 'Asset/Admin', 'Marketing', 'RSO', 'Store'];
    }

    public static function agentPageAccess(): array
    {
        return array_merge(
            static::defaultPageAccess(),
            static::get('agent_page_access', [])
        );
    }

    public static function agentRoutingDepts(): array
    {
        return static::get('agent_routing_departments', static::allDepartments());
    }
}
