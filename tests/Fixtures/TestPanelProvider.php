<?php

declare(strict_types=1);

namespace ZEDMagdy\FilamentChat\Tests\Fixtures;

use Filament\Panel;
use Filament\PanelProvider;

class TestPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('testing')
            ->path('testing')
            ->authGuard('web');
    }
}
