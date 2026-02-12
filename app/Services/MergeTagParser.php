<?php

namespace App\Services;

use App\Models\Recipient;

class MergeTagParser
{
    /**
     * Parse merge tags in text, replacing {{tag}} with recipient data.
     */
    public function parse(string $text, Recipient $recipient): string
    {
        // Replace built-in tags
        $text = str_replace('{{nama}}', $recipient->name ?? '', $text);
        $text = str_replace('{{email}}', $recipient->email, $text);
        $text = str_replace('{{name}}', $recipient->name ?? '', $text);

        // Replace custom field tags
        $customFields = $recipient->custom_fields ?? [];
        foreach ($customFields as $key => $value) {
            $text = str_replace('{{' . $key . '}}', (string) $value, $text);
        }

        // Clean up any remaining unmatched tags
        $text = preg_replace('/\{\{[^}]+\}\}/', '', $text);

        return $text;
    }

    /**
     * Extract all merge tags from text.
     */
    public function extractTags(string $text): array
    {
        preg_match_all('/\{\{([^}]+)\}\}/', $text, $matches);
        return array_unique($matches[1] ?? []);
    }
}
