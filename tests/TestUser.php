<?php

namespace Pasax\SecureMonitor\Tests;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Pasax\SecureMonitor\Concerns\HasSecurityRelations;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string $role
 *
 * @method static \Pasax\SecureMonitor\Tests\TestUser create(array $attributes = [])
 */
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
