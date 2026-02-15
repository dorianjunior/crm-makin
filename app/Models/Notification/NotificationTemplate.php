<?php

namespace App\Models\Notification;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class NotificationTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'name',
        'slug',
        'type',
        'channel',
        'subject',
        'body_template',
        'variables',
        'default_data',
        'is_active',
    ];

    protected $casts = [
        'variables' => 'array',
        'default_data' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($template) {
            if (empty($template->slug)) {
                $template->slug = Str::slug($template->name);
            }
        });
    }

    /**
     * Get the company that owns the template.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Scope a query to only include active templates.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to filter by type.
     */
    public function scopeType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope a query to filter by channel.
     */
    public function scopeChannel($query, string $channel)
    {
        return $query->where('channel', $channel);
    }

    /**
     * Render the subject with variables.
     */
    public function renderSubject(array $data = []): ?string
    {
        if (empty($this->subject)) {
            return null;
        }

        return $this->replaceVariables($this->subject, $data);
    }

    /**
     * Render the body with variables.
     */
    public function renderBody(array $data = []): string
    {
        return $this->replaceVariables($this->body_template, $data);
    }

    /**
     * Replace variables in text.
     */
    protected function replaceVariables(string $text, array $data): string
    {
        // Merge with default data
        $data = array_merge($this->default_data ?? [], $data);

        foreach ($data as $key => $value) {
            // Handle nested arrays with dot notation
            if (is_array($value)) {
                foreach ($this->flattenArray($value, $key) as $nestedKey => $nestedValue) {
                    $text = str_replace('{{'.$nestedKey.'}}', $nestedValue, $text);
                }
            } else {
                $text = str_replace('{{'.$key.'}}', $value, $text);
            }
        }

        return $text;
    }

    /**
     * Flatten array for dot notation.
     */
    protected function flattenArray(array $array, string $prefix = ''): array
    {
        $result = [];

        foreach ($array as $key => $value) {
            $newKey = $prefix ? "{$prefix}.{$key}" : $key;

            if (is_array($value)) {
                $result = array_merge($result, $this->flattenArray($value, $newKey));
            } else {
                $result[$newKey] = $value;
            }
        }

        return $result;
    }

    /**
     * Get required variables from the template.
     */
    public function getRequiredVariables(): array
    {
        $text = $this->subject.' '.$this->body_template;
        preg_match_all('/\{\{(\w+(?:\.\w+)*)\}\}/', $text, $matches);

        return array_unique($matches[1] ?? []);
    }

    /**
     * Validate if all required variables are provided.
     */
    public function hasAllRequiredVariables(array $data): bool
    {
        $required = $this->getRequiredVariables();
        $provided = $this->flattenArray($data);

        foreach ($required as $var) {
            if (! array_key_exists($var, $provided)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get template by type and channel.
     */
    public static function getByTypeAndChannel(int $companyId, string $type, string $channel): ?self
    {
        return static::where('company_id', $companyId)
            ->where('type', $type)
            ->where('channel', $channel)
            ->where('is_active', true)
            ->first();
    }
}
