<?php

namespace Pasax\SecureMonitor\Concerns;

use Pasax\SecureMonitor\Support\PasaxTableResolver;

trait ResolvesPasaxTable
{
    /**
     * Resolve the actual database table name with seamless backward compatibility.
     */
    public static function resolvePasaxTable(string $key, string $configured, string $legacy, string $prefixed): string
    {
        return PasaxTableResolver::resolve($key, $configured, $legacy, $prefixed);
    }

    /**
     * Reset the cached resolved table names (useful for tests and migrations).
     */
    public static function clearResolvedTableCache(): void
    {
        PasaxTableResolver::clearCache();
    }
}
