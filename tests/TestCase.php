<?php

declare(strict_types=1);

namespace ZEDMagdy\FilamentChat\Tests;

use Filament\Actions\ActionsServiceProvider;
use Filament\FilamentServiceProvider;
use Filament\Forms\FormsServiceProvider;
use Filament\Notifications\NotificationsServiceProvider;
use Filament\Schemas\SchemasServiceProvider;
use Filament\Support\SupportServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\MediaLibrary\MediaLibraryServiceProvider;
use ZEDMagdy\FilamentChat\FilamentChatServiceProvider;
use ZEDMagdy\FilamentChat\Tests\Fixtures\TestPanelProvider;
use ZEDMagdy\FilamentChat\Tests\Fixtures\User;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'ZEDMagdy\\FilamentChat\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app): array
    {
        return [
            LivewireServiceProvider::class,
            FilamentServiceProvider::class,
            FormsServiceProvider::class,
            SupportServiceProvider::class,
            ActionsServiceProvider::class,
            SchemasServiceProvider::class,
            NotificationsServiceProvider::class,
            MediaLibraryServiceProvider::class,
            FilamentChatServiceProvider::class,
            TestPanelProvider::class,
        ];
    }

    protected function defineDatabaseMigrations(): void
    {
        // Create a users table for testing
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamps();
        });

        // Spatie media library table (used by message attachments)
        (include __DIR__.'/../vendor/spatie/laravel-medialibrary/database/migrations/create_media_table.php.stub')->up();

        // Run package migrations
        foreach (File::allFiles(__DIR__.'/../database/migrations') as $migration) {
            (include $migration->getRealPath())->up();
        }
    }

    public function getEnvironmentSetUp($app): void
    {
        config()->set('database.default', 'testing');
        config()->set('queue.default', 'sync');
        config()->set('broadcasting.default', 'null');
        config()->set('filament-chat.table_prefix', 'chat_');
        config()->set('auth.providers.users.model', User::class);
        config()->set('filesystems.disks.public', [
            'driver' => 'local',
            'root' => storage_path('app/public'),
        ]);
    }
}
