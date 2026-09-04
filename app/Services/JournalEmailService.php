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
            $journalHomeUrl = route('journal.public.home', $journal->slug);
            $editorName = auth()->check()
                ? (auth()->user()->full_name ?: auth()->user()->name)
                : ($journal->getSetting('contact.principal.name') ?? 'Editor');

            $variables = array_merge([
                'recipientName' => $recipientName,
                'recipientEmail' => $recipientEmail,
                'journalName' => $journal->name,
                'journalUrl' => $journalHomeUrl,
                'editorName' => $editorName,
            ], $variables);

            // Add signature if not present
            if (!isset($variables['signature'])) {
                $variables['signature'] = $journal->email_signature 
                    ?: ('<a href="' . $journalHomeUrl . '" target="_blank" style="color: #2563eb; text-decoration: underline; font-weight: 600;">' . e($journal->name) . '</a><br>Editorial Team'); 
            }

            // 3. Parse Content
            $subject = $variables['customSubject'] ?? self::parseVariables($templateData['subject'], $variables);
            $body = $variables['customBody'] ?? self::parseVariables($templateData['body'], $variables);
            $bodyHtml = self::formatEmailBodyHtml($body);

            // 4. Send Email
            Mail::send([], [], function (Message $message) use ($recipientEmail, $recipientName, $subject, $bodyHtml, $journal) {
                $message->to($recipientEmail, $recipientName)
                    ->subject($subject);
                
                $systemEmail = config('mail.from.address') ?: 'ejournal@apdesyi.or.id';
                $fromName = config('mail.from.name') ?: ($journal->name ?? 'IAMJOS System');
                $principalName = $journal->getSetting('contact.principal.name') ?? $journal->name;
                $principalEmail = $journal->getSetting('contact.principal.email');

                $message->from($systemEmail, $fromName);
                if ($principalEmail) {
                    $message->replyTo($principalEmail, $principalName);
                }
                
                $message->html($bodyHtml);
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
     * Automatically convert plain text URLs into HTML hyperlinked <a> tags.
     */
    public static function autoLinkUrls(string $html): string
    {
        $pattern = '/(?<!href="|href=\'|>)(https?:\/\/[^\s<]+)/i';
        return preg_replace_callback($pattern, function ($matches) {
            $url = $matches[1];
            $cleanUrl = rtrim($url, '.,;)');
            $trailing = substr($url, strlen($cleanUrl));
            return '<a href="' . $cleanUrl . '" target="_blank" style="color: #2563eb; text-decoration: underline;">' . $cleanUrl . '</a>' . $trailing;
        }, $html);
    }

    /**
     * Convert email body into clean, beautiful OJS-style HTML email.
     */
    public static function formatEmailBodyHtml(string $body): string
    {
        // 1. If body does not contain block HTML tags (p, div), convert double newlines to paragraphs
        $hasBlock = preg_match('/<(p|div|table|ul|ol|h[1-6]|blockquote)\b[^>]*>/i', $body);
        if (!$hasBlock) {
            $normalized = str_replace(["\r\n", "\r"], "\n", $body);
            $paragraphs = array_filter(array_map('trim', explode("\n\n", $normalized)), function ($p) {
                return $p !== '';
            });
            $html = '';
            foreach ($paragraphs as $p) {
                $html .= '<p style="margin: 0 0 16px 0; line-height: 1.6;">' . nl2br($p) . "</p>\n";
            }
            $body = $html;
        } else {
            // Clean up any double nested <p><p> tags from variable replacement
            $body = preg_replace('/<p>\s*<p>/i', '<p>', $body);
            $body = preg_replace('/<\/p>\s*<\/p>/i', '</p>', $body);
        }

        // 2. Auto-link plain text URLs
        $content = self::autoLinkUrls($body);

        // 3. Wrap in clean OJS 3 HTML Email Layout
        return self::wrapInOjsLayout($content);
    }

    /**
     * Wrap email body in a clean OJS 3 style responsive email wrapper.
     */
    public static function wrapInOjsLayout(string $content): string
    {
        return '<!DOCTYPE html>' . "\n"
            . '<html>' . "\n"
            . '<head>' . "\n"
            . '    <meta charset="utf-8">' . "\n"
            . '    <meta name="viewport" content="width=device-width, initial-scale=1.0">' . "\n"
            . '    <style>' . "\n"
            . '        body { margin: 0; padding: 24px 16px; background-color: #f8fafc; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; font-size: 14px; line-height: 1.6; color: #1e293b; }' . "\n"
            . '        p { margin: 0 0 16px 0; line-height: 1.6; color: #1e293b; }' . "\n"
            . '        p:last-child { margin-bottom: 0; }' . "\n"
            . '        a { color: #2563eb; text-decoration: underline; font-weight: 500; }' . "\n"
            . '        ul, ol { margin: 0 0 16px 0; padding-left: 20px; }' . "\n"
            . '        li { margin-bottom: 4px; }' . "\n"
            . '    </style>' . "\n"
            . '</head>' . "\n"
            . '<body style="margin: 0; padding: 24px 16px; background-color: #f8fafc; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, Helvetica, Arial, sans-serif; font-size: 14px; line-height: 1.6; color: #1e293b;">' . "\n"
            . '    <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #f8fafc; padding: 20px 0;">' . "\n"
            . '        <tr>' . "\n"
            . '            <td align="center">' . "\n"
            . '                <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0" style="max-width: 600px; background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); text-align: left;">' . "\n"
            . '                    <tr>' . "\n"
            . '                        <td style="padding: 32px 32px 28px 32px; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, Helvetica, Arial, sans-serif; font-size: 14px; line-height: 1.6; color: #1e293b;">' . "\n"
            . '                            ' . $content . "\n"
            . '                        </td>' . "\n"
            . '                    </tr>' . "\n"
            . '                </table>' . "\n"
            . '            </td>' . "\n"
            . '        </tr>' . "\n"
            . '    </table>' . "\n"
            . '</body>' . "\n"
            . '</html>';
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

        $journalHomeUrl = route('journal.public.home', $journal->slug);
        $editorName = auth()->check()
            ? (auth()->user()->full_name ?: auth()->user()->name)
            : ($journal->getSetting('contact.principal.name') ?? 'Editor');

        $defaultVars = [
            'journalName' => $journal->name,
            'journalUrl' => $journalHomeUrl,
            'editorName' => $editorName,
            'signature' => $journal->email_signature ?: ('<a href="' . $journalHomeUrl . '" target="_blank" style="color: #2563eb; text-decoration: underline; font-weight: 600;">' . e($journal->name) . '</a><br>Editorial Team'),
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
        if (isset($submission->authors) && count($submission->authors) > 0) {
            $first = $submission->authors->where('is_primary_contact', true)->first() ?: $submission->authors->first();
            if ($first) {
                $authorName = $first->name ?: trim(($first->given_name ?? '') . ' ' . ($first->family_name ?? ''));
            }
        }
        if (empty($authorName) && isset($submission->author) && $submission->author) {
            $authorName = $submission->author->full_name;
        }
        if (empty($authorName) && isset($submission->user) && $submission->user) {
            $authorName = $submission->user->full_name ?: trim(($submission->user->first_name ?? '') . ' ' . ($submission->user->last_name ?? ''));
        }
        if (empty($authorName)) {
            $authorName = 'Author';
        }

        $submissionUrl = route('journal.submissions.show', [
            'journal' => $journal->slug,
            'submission' => $submission->url_slug ?? $submission->slug ?? $submission->id
        ]);

        $journalHomeUrl = route('journal.public.home', $journal->slug);
        $editorName = auth()->check()
            ? (auth()->user()->full_name ?: auth()->user()->name)
            : ($journal->getSetting('contact.principal.name') ?? 'Editor');

        $vars = [
            'authorName' => $authorName,
            'firstAuthorName' => $authorName,
            'recipientName' => $authorName,
            'editorName' => $editorName,
            'submissionTitle' => $submission->title ?? '',
            'submissionCode' => $submission->submission_code ?? ('#' . ($submission->id ?? '')),
            'submissionUrl' => $submissionUrl,
            'journalName' => $journal->name,
            'journalUrl' => $journalHomeUrl,
            'signature' => $journal->email_signature ?: ('<a href="' . $journalHomeUrl . '" target="_blank" style="color: #2563eb; text-decoration: underline; font-weight: 600;">' . e($journal->name) . '</a><br>Editorial Team'),
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

