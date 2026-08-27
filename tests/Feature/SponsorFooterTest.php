<?php

namespace Tests\Feature;

use Tests\TestCase;

class SponsorFooterTest extends TestCase
{
    public function test_homepage_displays_the_ucsc_sponsorship(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Patrocinado por:')
            ->assertDontSee('Plataforma Activa')
            ->assertSee('images/ucsc-patrocinio.png', false)
            ->assertSee('alt="UCSC"', false);

        $this->assertFileExists(public_path('images/ucsc-patrocinio.png'));
    }
}
