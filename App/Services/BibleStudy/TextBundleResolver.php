<?php
declare(strict_types=1);

namespace App\Services\BibleStudy;

use App\Contracts\Templates\TemplateAssemblyService;
use App\Contracts\Translation\TranslationService;
use Psr\SimpleCache\CacheInterface;

final class TextBundleResolver
{
    public function __construct(
        private TemplateAssemblyService $templates,
        private TranslationService $translator,
        private CacheInterface $cache
    ) {}

    /**
     * @return array{data:array<string,mixed>, etag:string}
     */
    public function fetch(
        string $kind,
        string $subject,
        string $languageCodeHL,
        ?string $variant
    ): array {
        $ver = $this->templates->version($kind, $subject);

        $tplKey = $this->tplKey($kind, $subject, $ver);
        $base = $this->cache->get($tplKey);

        if (!is_array($base)) {
            $base = $this->templates->get($kind, $subject);
            $this->cache->set($tplKey, $base);
        }

        $isBaseLang = ($languageCodeHL === $this->translator->baseLanguage());
        $hasVariant = ($variant !== null && $variant !== '');

        \App\Support\Trace::info('TextBundleResolver decision', [
            'kind'         => $kind,
            'subject'      => $subject,
            'lang'         => $languageCodeHL,
            'baseLang'     => $this->translator->baseLanguage(),
            'variant'      => $variant,
            'isBaseLang'   => $isBaseLang,
            'hasVariant'   => $hasVariant,
            'tplKey'       => $tplKey,
            'trKey'        => $this->trKey($kind,$subject,$languageCodeHL,$variant,$ver),
        ]);

        if ($isBaseLang && !$hasVariant) {
            $out = $base;
            $etag = $this->etag($out, $ver);
            return ['data' => $out, 'etag' => $etag];
        }

        $trKey = $this->trKey(
            $kind,
            $subject,
            $languageCodeHL,
            $variant,
            $ver
        );

        $out = $this->cache->get($trKey);
        if (is_array($out)) {
            return ['data' => $out, 'etag' => $this->etag($out, $ver)];
        }

        $out = $this->translator->translateBundle(
            $base,
            $languageCodeHL,
            $variant
        );

        $this->cache->set($trKey, $out);

        return ['data' => $out, 'etag' => $this->etag($out, $ver)];
    }

    private function tplKey(
        string $kind,
        string $subject,
        string $ver
    ): string {
        return "tpl:{$kind}:{$subject}:{$ver}";
    }

    private function trKey(
        string $kind,
        string $subject,
        string $lang,
        ?string $variant,
        string $ver
    ): string {
        $v = $variant ?: 'default';
        return "tr:{$kind}:{$subject}:{$lang}:{$v}:{$ver}";
    }

    private function etag(array $payload, string $ver): string
    {
        $hash = hash(
            'xxh128',
            json_encode($payload, JSON_UNESCAPED_UNICODE)
        );
        return $ver . '-' . $hash;
    }
}
