<?php
declare(strict_types=1);

namespace App\Http;

use Exception;

final class RetryHttpClient implements HttpClientInterface
{
    private HttpClientInterface $inner;
    private int $maxAttempts;
    private int $baseDelayMs;
    /** @var int[] */
    private array $retryOn;

    /**
     * @param int[] $retryOn HTTP status codes to retry on
     */
    public function __construct(
        HttpClientInterface $inner,
        int $maxAttempts = 3,
        int $baseDelayMs = 200,
        array $retryOn = [429, 500, 502, 503, 504]
    ) {
        $this->inner = $inner;
        $this->maxAttempts = max(1, $maxAttempts);
        $this->baseDelayMs = max(0, $baseDelayMs);
        $this->retryOn = $retryOn;
    }

    public function get(string $url, ?RequestOptions $opt = null): HttpResponse
    {
        $attempt = 0;
        $lastEx = null;
        while (true) {
            $attempt++;
            try {
                $resp = $this->inner->get($url, $opt);
                if (!in_array($resp->status, $this->retryOn, true)) {
                    return $resp;
                }
                if ($attempt >= $this->maxAttempts) {
                    return $resp; // give up, return last response
                }
            } catch (Exception $e) {
                $lastEx = $e;
                if ($attempt >= $this->maxAttempts) {
                    throw $lastEx;
                }
            }

            $delay = $this->computeDelayMs($attempt, $opt);
            usleep($delay * 1000);
        }
    }

    private function computeDelayMs(int $attempt, ?RequestOptions $opt): int
    {
        // exponential backoff: base * 2^(attempt-1), bounded
        $factor = 1;
        for ($i = 1; $i < $attempt; $i++) $factor *= 2;
        $delay = $this->baseDelayMs * $factor;
        $max = 5000;
        if ($delay > $max) $delay = $max;
        return $delay;
    }
}
