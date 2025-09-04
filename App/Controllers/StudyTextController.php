<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Http\Concerns\ValidatesArgs;
use App\Responses\JsonResponse;
use App\Services\BibleStudy\TextBundleResolver;
use Exception;

final class StudyTextController
{
    use ValidatesArgs;

    private const KINDS = [
        'common'    => 'commonContent',
        'interface' => 'interface',
    ];

    public function __construct(private TextBundleResolver $resolver) {}

    /**
     * Args:
     * - kind: common | interface
     * - subject: study or app code (normId'd)
     * - languageCodeHL
     * - variant (optional)
     */
    public function webFetch(array $args): void
    {
        try {
            $kind = $this->arg($args, 'kind', [$this, 'normId']);
            $subj = $this->arg($args, 'subject', [$this, 'normId']);
            $lang = $this->arg($args, 'languageCodeHL', [$this, 'normId']);
            $var  = $this->arg($args, 'variant', [$this, 'normId']);

            if (!isset(self::KINDS[$kind])) {
                JsonResponse::error('Invalid kind. Use: common | interface');
                return;
            }

            $mapped = self::KINDS[$kind];
            $res = $this->resolver->fetch($mapped, $subj, $lang, $var);

            $client = $_SERVER['HTTP_IF_NONE_MATCH'] ?? null;
            $client = is_string($client) ? trim($client, '"') : null;
            if ($client !== null && $client === $res['etag']) {
                JsonResponse::notModified($res['etag']);
                return;
            }

            JsonResponse::success(
                $res['data'],
                [
                    'ETag'          => '"' . $res['etag'] . '"',
                    'Cache-Control' => 'public, max-age=600',
                ],
                200
            );
        } catch (Exception $e) {
            JsonResponse::error($e->getMessage());
        }
    }
}
