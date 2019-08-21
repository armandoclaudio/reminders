<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected $user = null;

    public function signIn($user = null)
    {
        $this->user = $user ?: factory('App\User')->create();
        $this->actingAs($this->user);
    }
}
