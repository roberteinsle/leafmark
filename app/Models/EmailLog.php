<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailLog extends Model
{
    protected $fillable = [
        'user_id',
        'recipient',
        'subject',
        'type',
        'status',
        'error_message',
        'smtp_config',
        'stack_trace',
    ];

    protected $casts = [
        'smtp_config' => 'array',
    ];

    /**
     * Get the user who triggered the email.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Log a successful email send.
     */
    public static function logSuccess(string $recipient, string $subject, string $type = 'test', ?int $userId = null, ?array $smtpConfig = null): self
    {
        return self::create([
            'user_id' => $userId,
            'recipient' => $recipient,
            'subject' => $subject,
            'type' => $type,
            'status' => 'sent',
            'smtp_config' => $smtpConfig,
        ]);
    }

    /**
     * Log a failed email send.
     */
    public static function logFailure(string $recipient, string $subject, string $errorMessage, string $type = 'test', ?int $userId = null, ?array $smtpConfig = null, ?string $stackTrace = null): self
    {
        return self::create([
            'user_id' => $userId,
            'recipient' => $recipient,
            'subject' => $subject,
            'type' => $type,
            'status' => 'failed',
            'error_message' => $errorMessage,
            'smtp_config' => $smtpConfig,
            'stack_trace' => $stackTrace,
        ]);
    }
}
