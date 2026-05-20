<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (empty(config('jwt.secret')))
        {
            config(['jwt.secret' => 'test-jwt-secret-key-with-at-least-32-characters']);
        }
    }
}
