<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\EmailTemplate;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $defaults = collect(EmailTemplate::getDefaultTemplates())->keyBy('key');

        $templates = EmailTemplate::all();
        foreach ($templates as $template) {
            $rawBody = $template->getRawOriginal('body') ?? '';
            $hasBlock = preg_match('/<(p|div|table|ul|ol|h[1-6]|blockquote)\b[^>]*>/i', $rawBody);

            if (!$template->is_custom && isset($defaults[$template->key])) {
                // Update default template to clean OJS HTML version
                $template->update([
                    'body' => $defaults[$template->key]['body'],
                ]);
            } elseif (!$hasBlock && !empty($rawBody)) {
                // Convert customized plain text to HTML paragraphs
                $template->update([
                    'body' => EmailTemplate::formatPlainToHtml($rawBody),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Non-destructive reverse, keep HTML
    }
};
