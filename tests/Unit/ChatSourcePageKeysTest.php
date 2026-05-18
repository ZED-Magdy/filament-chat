<?php

declare(strict_types=1);

use ZEDMagdy\FilamentChat\Pages\ChatSourcePage;

it('exposes its single source key as an array', function (): void {
    $page = new class extends ChatSourcePage
    {
        protected static string $chatSourceKey = 'staff';
    };

    expect($page->getSourceKeys())->toBe(['staff']);
});
