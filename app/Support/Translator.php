<?php
namespace App\Support;

use App\Models\Brand;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

/**
 * B5: lapisan penyedia terjemahan (DeepL, Google Cloud Translation, Claude API).
 * Semua teks diperlakukan sebagai HTML di dalam; teks polos di-escape lebih dulu dan
 * dikembalikan menjadi teks polos. Istilah di glosarium dibungkus <x>...</x> agar tidak
 * diterjemahkan, lalu pembungkusnya dibuang.
 */
class Translator
{
    public static function enabled(): bool
    {
        $driver = config('translator.driver');

        return in_array($driver, ['deepl', 'google', 'anthropic'], true)
            && filled(config("translator.{$driver}.key"));
    }

    /**
     * @param  array<string,array{text:string,html:bool}>  $items  kunci bebas => teks
     * @return array<string,string>                               kunci yang sama => hasil
     * @throws RuntimeException bila penyedia gagal
     */
    public static function translate(array $items, string $from, string $to): array
    {
        if (! self::enabled()) {
            throw new RuntimeException('Penyedia terjemahan belum diatur di .env (TRANSLATE_DRIVER dan kuncinya).');
        }

        $keys    = array_keys($items);
        $payload = [];
        foreach ($items as $key => $item) {
            $text = $item['html'] ? $item['text'] : htmlspecialchars($item['text'], ENT_NOQUOTES, 'UTF-8');
            $payload[] = self::protect($text);
        }

        try {
            $driver = config('translator.driver');
            $out    = match ($driver) {
                'deepl'     => self::deepl($payload, $from, $to),
                'google'    => self::google($payload, $from, $to),
                'anthropic' => self::anthropic($payload, $from, $to),
            };
        } catch (RuntimeException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw new RuntimeException('Koneksi ke penyedia terjemahan gagal: ' . $e->getMessage(), 0, $e);
        }

        if (count($out) !== count($payload)) {
            throw new RuntimeException('Jumlah hasil terjemahan tidak sesuai.');
        }

        $result = [];
        foreach ($keys as $i => $key) {
            $text = self::unprotect((string) $out[$i]);
            $result[$key] = $items[$key]['html']
                ? trim($text)
                : trim(html_entity_decode(strip_tags($text), ENT_QUOTES, 'UTF-8'));
        }

        return $result;
    }

    /* ------------------------------ glosarium ------------------------------ */

    protected static function terms(): array
    {
        $terms = (array) config('translator.protected', []);

        try {
            $terms = array_merge($terms, Brand::query()->pluck('name')->all());
        } catch (Throwable) {
            // tabel brand belum ada: cukup glosarium bawaan
        }

        $terms = array_values(array_unique(array_map('trim', array_filter($terms, fn($t) => is_string($t) && mb_strlen(trim($t)) >= 3))));

        // Istilah terpanjang dicocokkan lebih dulu.
        usort($terms, fn($a, $b) => mb_strlen($b) <=> mb_strlen($a));

        return $terms;
    }

    protected static function protect(string $html): string
    {
        $terms = self::terms();
        if (! $terms) {
            return $html;
        }

        $pattern = '/(?<![\p{L}\p{N}])(' . implode('|', array_map(fn($t) => preg_quote(htmlspecialchars($t, ENT_NOQUOTES, 'UTF-8'), '/'), $terms)) . ')(?![\p{L}\p{N}])/u';

        // Hanya teks di luar tag HTML yang dibungkus (jangan menyentuh atribut).
        $parts = preg_split('/(<[^>]+>)/u', $html, -1, PREG_SPLIT_DELIM_CAPTURE);
        foreach ($parts as $i => $part) {
            if ($part !== '' && $part[0] !== '<') {
                $parts[$i] = preg_replace($pattern, '<x>$1</x>', $part);
            }
        }

        return implode('', $parts);
    }

    protected static function unprotect(string $html): string
    {
        $html = preg_replace('#<span[^>]*(?:notranslate|translate="no")[^>]*>(.*?)</span>#isu', '$1', $html);

        return str_ireplace(['<x>', '</x>'], '', $html);
    }

    /* ------------------------------- penyedia ------------------------------ */

    protected static function deepl(array $texts, string $from, string $to): array
    {
        $key      = (string) config('translator.deepl.key');
        $endpoint = config('translator.deepl.endpoint')
            ?: (str_ends_with($key, ':fx') ? 'https://api-free.deepl.com/v2/translate' : 'https://api.deepl.com/v2/translate');

        $form = [
            'source_lang'  => strtoupper($from),
            'target_lang'  => $to === 'en' ? 'EN-US' : strtoupper($to),
            'tag_handling' => 'html',
            'ignore_tags'  => 'x',
        ];

        $query = http_build_query($form);
        foreach ($texts as $t) {
            $query .= '&text=' . rawurlencode($t);
        }

        $res = Http::timeout(config('translator.timeout'))
            ->withHeaders(['Authorization' => 'DeepL-Auth-Key ' . $key])
            ->withBody($query, 'application/x-www-form-urlencoded')
            ->post($endpoint);

        if (! $res->successful()) {
            throw new RuntimeException('DeepL menolak permintaan (HTTP ' . $res->status() . '). ' . ($res->status() === 456 ? 'Kuota bulanan habis.' : 'Periksa DEEPL_API_KEY.'));
        }

        return collect($res->json('translations', []))->pluck('text')->all();
    }

    protected static function google(array $texts, string $from, string $to): array
    {
        $texts = array_map(fn($t) => str_replace(['<x>', '</x>'], ['<span class="notranslate">', '</span>'], $t), $texts);

        $res = Http::timeout(config('translator.timeout'))
            ->asJson()
            ->post('https://translation.googleapis.com/language/translate/v2?key=' . urlencode((string) config('translator.google.key')), [
                'q' => $texts, 'source' => $from, 'target' => $to, 'format' => 'html',
            ]);

        if (! $res->successful()) {
            throw new RuntimeException('Google Translate menolak permintaan (HTTP ' . $res->status() . '). Periksa GOOGLE_TRANSLATE_KEY.');
        }

        return collect($res->json('data.translations', []))
            ->map(fn($t) => self::decodeGoogleEntities((string) ($t['translatedText'] ?? '')))
            ->all();
    }

    /** Google mengembalikan tanda kutip sebagai entitas (&#39;); kembalikan ke karakter aslinya. */
    protected static function decodeGoogleEntities(string $t): string
    {
        return str_replace(['&#39;', '&quot;'], ["'", '"'], $t);
    }

    protected static function anthropic(array $texts, string $from, string $to): array
    {
        $names = ['id' => 'Indonesian', 'en' => 'English'];

        $system = "You are a professional translator for a corporate website (DECAA.ID, an Indonesian technology company). "
            . "Translate from {$names[$from]} to {$names[$to]} with a natural, professional tone suitable for company news. "
            . "Each input string is HTML. Keep every HTML tag, attribute, and the order of tags exactly as is; translate only the text. "
            . "Never translate or alter text inside <x>...</x>; keep those tags in place. "
            . "Do not add notes or explanations. Reply with ONLY a JSON array of strings, the same length and order as the input array.";

        $res = Http::timeout(config('translator.timeout'))
            ->withHeaders(['x-api-key' => (string) config('translator.anthropic.key'), 'anthropic-version' => '2023-06-01'])
            ->asJson()
            ->post('https://api.anthropic.com/v1/messages', [
                'model'      => config('translator.anthropic.model'),
                'max_tokens' => config('translator.anthropic.max_tokens'),
                'system'     => $system,
                'messages'   => [['role' => 'user', 'content' => json_encode($texts, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]],
            ]);

        if (! $res->successful()) {
            throw new RuntimeException('Claude API menolak permintaan (HTTP ' . $res->status() . '). Periksa ANTHROPIC_API_KEY dan nama model.');
        }

        if ($res->json('stop_reason') === 'max_tokens') {
            throw new RuntimeException('Teks terlalu panjang untuk sekali terjemah. Naikkan TRANSLATE_CLAUDE_MAX_TOKENS atau pecah beritanya.');
        }

        $raw = trim((string) $res->json('content.0.text'));
        $raw = preg_replace('/^```(?:json)?\s*|\s*```$/', '', $raw);
        $out = json_decode($raw, true);

        if (! is_array($out)) {
            throw new RuntimeException('Balasan Claude tidak bisa dibaca sebagai hasil terjemahan.');
        }

        return array_values($out);
    }
}