<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Pages load assets through @vite; tests shouldn't need a built manifest.
        $this->withoutVite();
    }
}
