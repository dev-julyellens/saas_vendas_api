<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_api_only_application_has_no_web_root(): void
    {
        $this->get('/')->assertNotFound();
    }
}
