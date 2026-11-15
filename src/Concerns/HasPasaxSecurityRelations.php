<?php

namespace Pasax\SecureMonitor\Concerns;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Pasax\SecureMonitor\Models\PasaxAllowedIp;
use Pasax\SecureMonitor\Models\PasaxBlockedIp;
use Pasax\SecureMonitor\Models\PasaxIpUnblockRequest;
use Pasax\SecureMonitor\Models\PasaxLog;
use Pasax\SecureMonitor\Models\PasaxTrustedIp;
use Pasax\SecureMonitor\Models\PasaxUserLogin;

trait HasPasaxSecurityRelations
{
    public function logins(): HasMany
    {
        return $this->hasMany(PasaxUserLogin::class, 'user_id');
    }

    public function trustedIps(): HasMany
    {
        return $this->hasMany(PasaxTrustedIp::class, 'user_id');
    }

    public function securityLogs(): HasMany
    {
        return $this->hasMany(PasaxLog::class, 'user_id');
    }

    public function pasaxLogs(): HasMany
    {
        return $this->securityLogs();
    }

    public function blockedIps(): HasMany
    {
        return $this->hasMany(PasaxBlockedIp::class, 'blocked_by');
    }

    public function allowedIps(): HasMany
    {
        return $this->hasMany(PasaxAllowedIp::class, 'allowed_by');
    }

    public function resolvedTickets(): HasMany
    {
        return $this->hasMany(PasaxIpUnblockRequest::class, 'resolved_by');
    }
}
