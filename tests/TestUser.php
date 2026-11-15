<?php

namespace Pasax\SecureMonitor\Tests;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Pasax\SecureMonitor\Concerns\HasSecurityRelations;

class TestUser extends Authenticatable
{
    use HasSecurityRelations;

    protected $table = 'users';

    protected $guarded = [];

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}
