<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;

afterEach(function (): void {
    // Clean up generated files
    File::deleteDirectory(app_path('Chat'));
    File::delete(app_path('Filament/Pages/StaffChatPage.php'));
    File::delete(app_path('Filament/Pages/PatientChatPage.php'));
});

it('creates a chat source and page', function (): void {
    $this->artisan('make:chat-source', [
        'name' => 'Staff',
        '--model' => 'User',
        '--no-interaction' => true,
    ])->assertSuccessful();

    expect(app_path('Chat/StaffChatSource.php'))->toBeFile()
        ->and(app_path('Filament/Pages/StaffChatPage.php'))->toBeFile();

    $sourceContent = File::get(app_path('Chat/StaffChatSource.php'));
    expect($sourceContent)
        ->toContain('class StaffChatSource extends ChatSource')
        ->toContain("return 'staff'")
        ->toContain("return 'Staff Chat'")
        ->toContain('App\Models\User')
        ->toContain('StaffChatPage::class');

    $pageContent = File::get(app_path('Filament/Pages/StaffChatPage.php'));
    expect($pageContent)
        ->toContain('class StaffChatPage extends ChatSourcePage')
        ->toContain("chatSourceKey = 'staff'");
});

it('accepts a fully qualified model class', function (): void {
    $this->artisan('make:chat-source', [
        'name' => 'Patient',
        '--model' => 'App\Models\Patient',
        '--no-interaction' => true,
    ])->assertSuccessful();

    $sourceContent = File::get(app_path('Chat/PatientChatSource.php'));
    expect($sourceContent)
        ->toContain('App\Models\Patient')
        ->toContain('Patient::class');
});

it('uses studly case for the class name', function (): void {
    $this->artisan('make:chat-source', [
        'name' => 'customer support',
        '--model' => 'User',
        '--no-interaction' => true,
    ])->assertSuccessful();

    expect(app_path('Chat/CustomerSupportChatSource.php'))->toBeFile()
        ->and(app_path('Filament/Pages/CustomerSupportChatPage.php'))->toBeFile();

    // Clean up
    File::delete(app_path('Filament/Pages/CustomerSupportChatPage.php'));
});
