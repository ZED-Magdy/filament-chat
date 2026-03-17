<?php

declare(strict_types=1);

namespace ZEDMagdy\FilamentChat\Tests\Fixtures;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use ZEDMagdy\FilamentChat\Traits\HasChats;

class User extends Authenticatable
{
    use HasChats;
    use HasFactory;

    protected $guarded = [];

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }
}
