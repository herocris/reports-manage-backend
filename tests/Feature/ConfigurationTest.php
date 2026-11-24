<?php

namespace Tests\Feature;

use App\Providers\TelescopeServiceProvider;
use Laravel\Telescope\TelescopeServiceProvider as PackageTelescopeServiceProvider;
use Tests\TestCase;

class ConfigurationTest extends TestCase
{
    public function test_telescope_is_not_registered_outside_local_development(): void
    {
        $this->assertNull($this->app->getProvider(TelescopeServiceProvider::class));
        $this->assertNull($this->app->getProvider(PackageTelescopeServiceProvider::class));
    }
}
