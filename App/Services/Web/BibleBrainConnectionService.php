<?php
declare(strict_types=1);

namespace App\Services\Web;

use App\Http\HttpClientInterface;
use App\Services\LoggerService;

final class BibleBrainConnectionService
{
    public function __construct(
        private HttpClientInterface $http,
        private LoggerService $log
    ) {}

    public function fetchLanguagesForIsoOrHl(?string $iso, ?string $hl): array
    {
        // TODO: implement real call to BibleBrain API
        return [];
    }

    public function fetchTextFilesets(string $bibleId): array
    {
        // TODO: implement real call
        return [];
    }
}
