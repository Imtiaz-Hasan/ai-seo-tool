<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Stub the @vite directive so view-rendering tests don't need built
        // assets (the manifest isn't present in CI, which doesn't run npm build).
        $this->withoutVite();
    }
}
