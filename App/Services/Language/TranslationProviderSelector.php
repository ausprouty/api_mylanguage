<?php
declare(strict_types=1);

namespace App\Services\Language;

use App\Configuration\Config;
use App\Contracts\Translation\ProviderSelector as Contract;
use App\Contracts\Translation\TranslationProvider;

/**
 * Chooses which TranslationProvider implementation to use based on
 * configuration and environment, without hard-coding container logic.
 *
 * Default policy:
 *   - environment=local  -> 'null'   (no-op provider)
 *   - environment!=local -> 'google' (real MT provider)
 *
 * You inject a key->class map so this class has no knowledge of
 * concrete provider classes. In tests, pass a custom $get callable
 * to simulate Config::get().
 */
final class TranslationProviderSelector implements Contract
{
    /**
     * @var array<string, class-string<TranslationProvider>>
     */
    private array $map;

    /**
     * Config getter callable:
     *   fn(string $key, mixed $default): mixed
     *
     * We keep this as an untyped property for broad callable support.
     *
     * @var callable
     */
    private $get;

    /**
     * @param array<string, class-string<TranslationProvider>> $map
     * @param callable(string, mixed):mixed|null $get
     */
    public function __construct(array $map, ?callable $get = null)
    {
        $this->map = $map;
        $this->get = $get ?? static fn(string $k, mixed $d) => Config::get($k, $d);
    }

    /**
     * Returns the short key of the chosen provider (e.g., 'google' or 'null').
     */
    public function chosenKey(): string
    {
        $env = strtolower((string) ($this->get)('environment', 'remote'));

        // Default by environment
        $default = ($env === 'local') ? 'null' : 'google';

        $key = strtolower(
            (string) ($this->get)('i18n.autoMt.provider', $default)
        );

        return \array_key_exists($key, $this->map) ? $key : 'null';
    }

    /**
     * Returns the FQCN of the chosen provider class.
     * Guaranteed to exist in $map (falls back to 'null').
     *
     * @return class-string<TranslationProvider>
     */
    public function chosenClass(): string
    {
        return $this->map[$this->chosenKey()];
    }
}
