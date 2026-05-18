<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;

afterEach(function (): void {
    File::deleteDirectory(app_path('Chat'));
    File::delete(app_path('Filament/Pages/AllMessagesChatPage.php'));
    File::delete(app_path('Filament/Pages/CustomerSupportChatPage.php'));
});

it('creates an aggregate chat source and page', function (): void {
    $this->artisan('make:chat-aggregate', [
        'name' => 'All Messages',
        '--sources' => 'staff,support',
        '--no-interaction' => true,
    ])->assertSuccessful();

    expect(app_path('Chat/AllMessagesAggregateChatSource.php'))->toBeFile()
        ->and(app_path('Filament/Pages/AllMessagesChatPage.php'))->toBeFile();

    $sourceContent = File::get(app_path('Chat/AllMessagesAggregateChatSource.php'));
    expect($sourceContent)
        ->toContain('class AllMessagesAggregateChatSource extends AggregateChatSource')
        ->toContain("return 'all-messages'")
        ->toContain("return 'All Messages'")
        ->toContain("'staff', 'support'")
        ->toContain('AllMessagesChatPage::class');

    $pageContent = File::get(app_path('Filament/Pages/AllMessagesChatPage.php'));
    expect($pageContent)
        ->toContain('class AllMessagesChatPage extends AggregateChatSourcePage')
        ->toContain("aggregateKey = 'all-messages'");
});

it('generates an empty source list when no sources are given', function (): void {
    $this->artisan('make:chat-aggregate', [
        'name' => 'All Messages',
        '--no-interaction' => true,
    ])->assertSuccessful();

    $sourceContent = File::get(app_path('Chat/AllMessagesAggregateChatSource.php'));
    expect($sourceContent)->toContain('return [];');
});

it('uses studly case for multi-word names', function (): void {
    $this->artisan('make:chat-aggregate', [
        'name' => 'customer support',
        '--sources' => 'staff',
        '--no-interaction' => true,
    ])->assertSuccessful();

    expect(app_path('Chat/CustomerSupportAggregateChatSource.php'))->toBeFile()
        ->and(app_path('Filament/Pages/CustomerSupportChatPage.php'))->toBeFile();

    $sourceContent = File::get(app_path('Chat/CustomerSupportAggregateChatSource.php'));
    expect($sourceContent)
        ->toContain('class CustomerSupportAggregateChatSource extends AggregateChatSource')
        ->toContain("return 'customer-support'");
});
