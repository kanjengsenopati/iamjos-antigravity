<?php

namespace App\Services;

use App\Models\EmailTemplate;
use App\Models\Journal;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Mail\Message;

class JournalEmailService
{
    public static function sendNotification(Journal $journal, $recipient, string $key, array $variables = []): bool
    {
        try {
            // 1. Resolve Template (Custom or Default)
            $templateData = self::resolveTemplate($journal, $key);

            if (!$templateData) {
                Log::info("Email template not found or disabled for key: {$key} in journal: {$journal->slug}");
                return false;
            }

            // Prepare recipient info
            $recipientEmail = '';
            $recipientName = '';

            if (is_string($recipient)) {
                $recipientEmail = $recipient;
                $recipientName = $variables['recipientName'] ?? $recipient;
            } elseif (is_array($recipient)) {
                $recipientEmail = $recipient['email'] ?? '';
                $recipientName = $recipient['name'] ?? $recipient['full_name'] ?? $recipientEmail;
            } elseif (is_object($recipient)) {
                $recipientEmail = $recipient->email ?? '';
                if (isset($recipient->full_name)) {
                    $recipientName = $recipient->full_name;
                } elseif (isset($recipient->name)) {
                    $recipientName = $recipient->name;
                } else {
                    $firstName = $recipient->first_name ?? '';
                    $lastName = $recipient->last_name ?? '';
                    $recipientName = trim($firstName . ' ' . $lastName);
                    if (empty($recipientName)) {
                        $recipientName = $recipientEmail;
                    }
                }
            }

            if (empty($recipientEmail)) {
                Log::warning("JournalEmailService: Recipient email is empty for key: {$key}");
                return false;
            }

            // 2. Prepare Variables
            $variables = array_merge([
                'recipientName' => $recipientName,
                'recipientEmail' => $recipientEmail,
                'journalName' => $journal->name,
                'journalUrl' => route('journal.home', $journal->slug),
            ], $variables);

            // Add signature if not present
            if (!isset($variables['signature'])) {
                $variables['signature'] = $journal->email_signature ?: ($journal->name . "\nEditorial Team"); 
            }

            // 3. Parse Content
            $subject = $variables['customSubject'] ?? self::parseVariables($templateData['subject'], $variables);
            $body = $variables['customBody'] ?? self::parseVariables($templateData['body'], $variables);

            // 4. Send Email
            Mail::send([], [], function (Message $message) use ($recipientEmail, $recipientName, $subject, $body, $journal) {
                $message->to($recipientEmail, $recipientName)
                    ->subject($subject);
                
                $principalName = $journal->getSetting('contact.principal.name') ?? $journal->name;
                $principalEmail = $journal->getSetting('contact.principal.email') ?? config('mail.from.address');

                $message->from($principalEmail, $principalName);
                $message->replyTo($principalEmail, $principalName);
                
                $message->html($body);
            });

            return true;

        } catch (\Exception $e) {
            Log::error("Failed to send email '{$key}': " . $e->getMessage());
            return false;
        }
    }

    /**
     * Resolve the email template data. 
     * Prioritizes custom database entry, falls back to Model defaults.
     */
    protected static function resolveTemplate(Journal $journal, string $key): ?array
    {
        // Step 1: Check Database for enabled custom/seeded template
        $dbTemplate = EmailTemplate::where('journal_id', $journal->id)
            ->where('key', $key)
            ->where('is_enabled', true)
            ->first();

        if ($dbTemplate) {
            return [
                'subject' => $dbTemplate->subject,
                'body' => $dbTemplate->body,
                'from_name' => $dbTemplate->from_name,
                'from_email' => $dbTemplate->from_email,
            ];
        }

        // Step 2: Fallback to Model Defaults (in case DB entry is missing or disabled? 
        // User prompt says "If not found" - implies if DB record doesn't exist.
        // If it exists but is disabled, should we send?
        // Prompt Check 1: "Look in email_templates table where ... is_enabled = true"
        // Prompt Check 2: "If not found, look in getDefaultTemplates()"
        // So if disabled in DB, we fall back to Code Default? Or do we not send?
        // Usually if "Disabled" in UI, it means "Don't send this email". 
        // BUT the Prompt requirement says Step 2 is fallback. 
        // Let's assume optimization: If DB record exists and is_enabled=false, we return NULL (don't send).
        // If DB record DOES NOT exist, we check defaults.
        
        $existingDisabled = EmailTemplate::where('journal_id', $journal->id)
            ->where('key', $key)
            ->where('is_enabled', false)
            ->exists();
            
        if ($existingDisabled) {
            // Explicitly disabled by user
            return null;
        }

        // Check defaults array
        $defaults = EmailTemplate::getDefaultTemplates();
        $default = collect($defaults)->firstWhere('key', $key);

        if ($default) {
             return [
                'subject' => $default['subject'],
                'body' => $default['body'],
                'from_name' => null,
                'from_email' => null,
            ];
        }

        // Not found anywhere
        return null;
    }

    /**
     * Parse and replace variables in the text.
     * Supports {$variableName} syntax.
     */
    public static function parseVariables(string $text, array $variables): string
    {
        foreach ($variables as $key => $value) {
            $val = is_scalar($value) ? (string)$value : '';
            $text = str_replace('{$' . $key . '}', $val, $text);
        }

        // Clean up remaining un-replaced {$variable} tags if any
        $text = preg_replace('/\{\$[a-zA-Z0-9_]+\}/', '', $text);

        return $text;
    }

    /**
     * Convert plain text email body to HTML paragraphs if not already HTML.
     */
    public static function formatEmailBodyHtml(string $body): string
    {
        if (preg_match('/<(p|div|br|table|ul|ol|h[1-6])\b[^>]*>/i', $body)) {
            return $body;
        }

        $paragraphs = array_filter(array_map('trim', explode("\n\n", $body)));
        if (empty($paragraphs)) {
            return '';
        }

        $html = '';
        foreach ($paragraphs as $p) {
            $html .= '<p>' . nl2br($p) . '</p>';
        }

        return $html;
    }

    /**
     * Get parsed template array for a given key and journal.
     */
    public static function getParsedTemplate(Journal $journal, string $key, array $variables = []): ?array
    {
        $templateData = self::resolveTemplate($journal, $key);
        if (!$templateData) {
            return null;
        }

        $defaultVars = [
            'journalName' => $journal->name,
            'journalUrl' => route('journal.home', $journal->slug),
            'signature' => $journal->email_signature ?: ($journal->name . "\nEditorial Team"),
            'editorComments' => '',
        ];

        $mergedVars = array_merge($defaultVars, $variables);

        $subject = self::parseVariables($templateData['subject'], $mergedVars);
        $body = self::parseVariables($templateData['body'], $mergedVars);
        $bodyHtml = self::formatEmailBodyHtml($body);

        return [
            'subject' => $subject,
            'body' => $body,
            'body_html' => $bodyHtml,
        ];
    }

    /**
     * Get all parsed email templates relevant for a submission workflow modal.
     */
    public static function getSubmissionEmailTemplates(Journal $journal, $submission): array
    {
        $authorName = '';
        if (isset($submission->user) && $submission->user) {
            $authorName = $submission->user->full_name ?: trim(($submission->user->first_name ?? '') . ' ' . ($submission->user->last_name ?? ''));
        }
        if (empty($authorName) && isset($submission->authors) && count($submission->authors) > 0) {
            $first = $submission->authors->first();
            if ($first) {
                $authorName = trim(($first->given_name ?? '') . ' ' . ($first->family_name ?? ''));
            }
        }
        if (empty($authorName)) {
            $authorName = 'Author';
        }

        $submissionUrl = route('journal.submissions.show', [
            'journal' => $journal->slug,
            'submission' => $submission->url_slug ?? $submission->slug ?? $submission->id
        ]);

        $vars = [
            'authorName' => $authorName,
            'firstAuthorName' => $authorName,
            'recipientName' => $authorName,
            'submissionTitle' => $submission->title ?? '',
            'submissionCode' => $submission->submission_code ?? ('#' . ($submission->id ?? '')),
            'submissionUrl' => $submissionUrl,
            'journalName' => $journal->name,
            'signature' => $journal->email_signature ?: ($journal->name . "\nEditorial Team"),
        ];

        $keys = [
            'EDITOR_DECISION_ACCEPT',
            'EDITOR_DECISION_REVISIONS',
            'EDITOR_DECISION_DECLINE',
            'LAYOUT_REQUEST',
            'COPYEDIT_REQUEST',
        ];

        $result = [];
        foreach ($keys as $key) {
            $parsed = self::getParsedTemplate($journal, $key, $vars);
            if ($parsed) {
                $result[$key] = $parsed;
            }
        }

        return $result;
    }
}

