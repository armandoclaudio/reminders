<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected $user = null;

    public function signIn($user = null)
    {
        $this->user = $user ?: User::factory()->create();
        $this->actingAs($this->user);
    }
}
