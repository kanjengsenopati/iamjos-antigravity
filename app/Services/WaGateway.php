<?php

namespace App\Services;

use App\Jobs\SendToWhatsappNotificationJob;
use App\Models\User;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Log;

class WaGateway
{
    /**
     * Safe dispatch method for WhatsApp Notifications.
     * 
     * Handles phone validation, normalization (08 → 628), and 
     * graceful failure if phone is empty or credentials missing.
     *
     * @param mixed $userOrNumber User Model instance or raw phone string
     * @param string $message The WhatsApp message to send
     * @return bool True if job was dispatched, false otherwise
     */
    public static function send($userOrNumber, string $message): bool
    {
        // 1. Resolve Phone Number from input
        $phone = null;
        $name = 'User';

        if ($userOrNumber instanceof User) {
            $phone = $userOrNumber->phone;
            $name = $userOrNumber->name ?? 'User';
        } elseif (is_string($userOrNumber)) {
            $phone = $userOrNumber;
        }

        // 2. Safety Check - Null/Empty phone
        if (empty($phone)) {
            Log::debug('WaGateway: Phone number is empty, skipping WhatsApp notification.', [
                'user_name' => $name,
            ]);
            return false; // Fail silently, do not dispatch
        }

        // 3. Check if WhatsApp credentials are configured
        $settings = SiteSetting::first();

        if (!$settings || empty($settings->wa_api_url) || empty($settings->wa_device_id)) {
            Log::warning('WaGateway: WhatsApp credentials not configured in site_settings.', [
                'has_api_url' => !empty($settings?->wa_api_url),
                'has_device_id' => !empty($settings?->wa_device_id),
            ]);
            return false; // Fail silently if not configured
        }

        // 4. Normalize Phone Number
        $phone = self::normalizePhone($phone);

        // 5. Dispatch Job (using afterResponse to not block user)
        try {
            dispatch(new SendToWhatsappNotificationJob($phone, $message));

            Log::info('WaGateway: WhatsApp notification dispatched.', [
                'phone' => self::maskPhone($phone),
                'message_length' => strlen($message),
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('WaGateway: Failed to dispatch WhatsApp job.', [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Normalize Indonesian phone number format.
     * 
     * Converts:
     * - 08xxx → 628xxx
     * - +62xxx → 62xxx
     * - Removes spaces, dashes, and other non-numeric characters
     *
     * @param string $phone Raw phone number
     * @return string Normalized phone number
     */
    public static function normalizePhone(string $phone): string
    {
        // Remove all non-numeric characters except leading +
        $phone = preg_replace('/[^0-9+]/', '', $phone);

        // Remove leading + if present
        $phone = ltrim($phone, '+');

        // Convert 08 prefix to 628 (Indonesian mobile format)
        if (str_starts_with($phone, '08')) {
            $phone = '62' . substr($phone, 1);
        }

        // Convert 8 prefix (without country code) to 628
        if (str_starts_with($phone, '8') && strlen($phone) >= 9 && strlen($phone) <= 12) {
            $phone = '62' . $phone;
        }

        return $phone;
    }

    /**
     * Mask phone number for logging (privacy).
     *
     * @param string $phone Phone number
     * @return string Masked phone (e.g., 6281****5678)
     */
    private static function maskPhone(string $phone): string
    {
        if (strlen($phone) <= 6) {
            return '****';
        }

        return substr($phone, 0, 4) . '****' . substr($phone, -4);
    }

    /**
     * Send WhatsApp notification to a User with a pre-built template.
     * Convenience method for common notification scenarios.
     *
     * @param User $user Target user
     * @param string $template Template key (welcome, submission_received, decision)
     * @param array $params Parameters to replace in message template
     * @return bool
     */
    public static function sendTemplate(User $user, string $template, array $params = []): bool
    {
        $message = self::buildMessage($template, $params);

        if (empty($message)) {
            return false;
        }

        return self::send($user, $message);
    }

    /**
     * Build message from template key.
     *
     * @param string $template Template key
     * @param array $params Parameters for placeholder replacement
     * @return string|null
     */
    private static function buildMessage(string $template, array $params): ?string
    {
        $templates = [
            'welcome' => "Selamat datang {name} di IAMJOS. Akun Anda berhasil dibuat.",
            'submission_received' => "Halo {name}, naskah Anda berjudul '{title}' telah berhasil disubmit. Pantau statusnya di dashboard.",
            'decision_update' => "Halo {name}, ada update status untuk naskah '{title}'. Status saat ini: {status}. Silakan cek dashboard.",
            'revision_request' => "Halo {name}, editor meminta revisi untuk naskah '{title}'. Silakan cek dashboard untuk detailnya.",
            'submission_accepted' => "Halo {name}, selamat! Naskah Anda '{title}' telah diterima untuk dipublikasikan.",
            'submission_rejected' => "Halo {name}, mohon maaf naskah Anda '{title}' tidak dapat kami terima. Silakan cek dashboard untuk feedback dari editor.",
            'new_submission_notification' => "Halo {name}, ada naskah baru berjudul '{title}'. Silakan tinjau di dashboard.",
            'reviewer_assigned' => "Halo {name}, Anda telah ditugaskan untuk mereview naskah '{title}' (Round {round}). Silakan cek dashboard untuk detailnya.",
            'discussion_message' => "Halo {name}, ada pesan baru di diskusi '{subject}' untuk naskah '{title}'. Silakan cek dashboard untuk detailnya.",
            'review_submitted' => "Halo {name}, reviewer telah mengirimkan review untuk naskah '{title}'. Silakan cek dashboard untuk detailnya.",
            'reviewer_accepted' => "Halo {name}, Reviewer {reviewer_name} telah menerima undangan review untuk naskah '{title}'.",
            'reviewer_declined' => "Halo {name}, Reviewer {reviewer_name} telah menolak undangan review untuk naskah '{title}'.",
        ];

        if (!isset($templates[$template])) {
            Log::warning('WaGateway: Unknown template key.', ['template' => $template]);
            return null;
        }

        $message = $templates[$template];

        // Replace placeholders
        foreach ($params as $key => $value) {
            $message = str_replace('{' . $key . '}', $value, $message);
        }

        return $message;
    }
}
