<?php

namespace App\Helpers;

class Sanitizer
{
    /**
     * Safe HTML tags allowed in rich-text fields (story body, event description, etc.).
     * All attributes are stripped except href/target on <a> and src/alt on <img>.
     */
    private const ALLOWED_TAGS = [
        'p', 'br', 'strong', 'b', 'em', 'i', 'u', 's',
        'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
        'ul', 'ol', 'li',
        'blockquote', 'pre', 'code',
        'a', 'img',
        'table', 'thead', 'tbody', 'tr', 'th', 'td',
        'hr', 'span', 'div',
    ];

    /**
     * Strip all tags except the safe allowlist, then remove
     * any remaining on* event attributes and javascript: hrefs
     * that strip_tags leaves behind on allowed tags.
     */
    public static function richText(?string $input): string
    {
        if ($input === null || $input === '') {
            return '';
        }

        // 1. strip_tags with allowed list
        $clean = strip_tags($input, '<' . implode('><', self::ALLOWED_TAGS) . '>');

        // 2. remove all on* event attributes (onclick, onmouseover, onerror, etc.)
        $clean = preg_replace('/\s+on\w+\s*=\s*(["\']).*?\1/is', '', $clean);
        $clean = preg_replace('/\s+on\w+\s*=\s*[^\s>]+/is', '', $clean);

        // 3. remove javascript: and data: URI schemes in href/src
        $clean = preg_replace('/(href|src)\s*=\s*(["\'])\s*(javascript|data|vbscript):[^"\']*\2/is', '$1=$2#$2', $clean);

        // 4. remove style attributes (can be used for CSS injection / UI redressing)
        $clean = preg_replace('/\s+style\s*=\s*(["\'])[^"\']*\1/is', '', $clean);

        return $clean;
    }

    /**
     * Plain text only — strips ALL HTML. Use for titles, names, subjects.
     */
    public static function plainText(?string $input): string
    {
        if ($input === null || $input === '') {
            return '';
        }
        return strip_tags((string) $input);
    }
}
