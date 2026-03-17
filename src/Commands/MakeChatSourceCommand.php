<?php

declare(strict_types=1);

namespace ZEDMagdy\FilamentChat\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

use function Laravel\Prompts\info;
use function Laravel\Prompts\text;

class MakeChatSourceCommand extends Command
{
    protected $signature = 'make:chat-source
        {name? : The name of the chat source (e.g. Staff, Patient)}
        {--model= : The participant model class (e.g. User)}';

    protected $description = 'Create a new chat source and its corresponding Filament page';

    public function __construct(protected Filesystem $files)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $name = $this->argument('name') ?? text(
            label: 'What is the chat source name?',
            placeholder: 'e.g. Staff, Patient, Support',
            required: true,
        );

        $name = Str::studly($name);
        $key = Str::kebab($name);

        $modelOption = $this->option('model');
        $model = $modelOption ?? (
            $this->option('no-interaction')
                ? 'User'
                : text(
                    label: 'What is the participant model?',
                    placeholder: 'e.g. User, Patient',
                    default: 'User',
                    required: true,
                )
        );

        $modelClass = $this->resolveModelClass($model);
        $sourceClass = "{$name}ChatSource";
        $pageClass = "{$name}ChatPage";

        $this->createSource($name, $key, $sourceClass, $pageClass, $modelClass);
        $this->createPage($name, $key, $pageClass);

        info("Chat source [{$sourceClass}] and page [{$pageClass}] created successfully.");
        $this->newLine();
        $this->components->bulletList([
            "Source: <comment>app/Chat/{$sourceClass}.php</comment>",
            "Page:   <comment>app/Filament/Pages/{$pageClass}.php</comment>",
        ]);

        $this->newLine();
        $this->components->info('Register the source in your panel provider:');
        $this->line("  FilamentChatPlugin::make()->sources([\\App\\Chat\\{$sourceClass}::class])");

        return self::SUCCESS;
    }

    protected function createSource(string $name, string $key, string $sourceClass, string $pageClass, string $modelClass): void
    {
        $stub = $this->files->get($this->getStubPath('chat-source.php.stub'));

        $modelBasename = class_basename($modelClass);
        $pageFullClass = "App\\Filament\\Pages\\{$pageClass}";

        $content = str_replace(
            ['{{ namespace }}', '{{ class }}', '{{ key }}', '{{ label }}', '{{ participantModel }}', '{{ participantModelBasename }}', '{{ pageClass }}', '{{ pageBasename }}'],
            ['App\\Chat', $sourceClass, $key, "{$name} Chat", $modelClass, $modelBasename, $pageFullClass, $pageClass],
            $stub,
        );

        $path = app_path("Chat/{$sourceClass}.php");
        $this->ensureDirectoryExists(dirname($path));
        $this->files->put($path, $content);
    }

    protected function createPage(string $name, string $key, string $pageClass): void
    {
        $stub = $this->files->get($this->getStubPath('chat-source-page.php.stub'));

        $content = str_replace(
            ['{{ namespace }}', '{{ class }}', '{{ key }}'],
            ['App\\Filament\\Pages', $pageClass, $key],
            $stub,
        );

        $path = app_path("Filament/Pages/{$pageClass}.php");
        $this->ensureDirectoryExists(dirname($path));
        $this->files->put($path, $content);
    }

    protected function resolveModelClass(string $model): string
    {
        if (str_contains($model, '\\')) {
            return $model;
        }

        return "App\\Models\\{$model}";
    }

    protected function getStubPath(string $stub): string
    {
        $customPath = base_path("stubs/filament-chat/{$stub}");

        if ($this->files->exists($customPath)) {
            return $customPath;
        }

        return __DIR__.'/../../stubs/'.$stub;
    }

    protected function ensureDirectoryExists(string $directory): void
    {
        if (! $this->files->isDirectory($directory)) {
            $this->files->makeDirectory($directory, 0755, true);
        }
    }
}
