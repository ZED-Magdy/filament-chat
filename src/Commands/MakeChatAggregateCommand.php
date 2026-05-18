<?php

declare(strict_types=1);

namespace ZEDMagdy\FilamentChat\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

use function Laravel\Prompts\info;
use function Laravel\Prompts\text;

class MakeChatAggregateCommand extends Command
{
    protected $signature = 'make:chat-aggregate
        {name? : The name of the aggregate page (e.g. All Messages)}
        {--sources= : Comma-separated list of source keys to combine (e.g. staff,support)}';

    protected $description = 'Create a new aggregate chat source and its corresponding Filament page';

    public function __construct(protected Filesystem $files)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $name = $this->argument('name') ?? text(
            label: 'What is the aggregate page name?',
            placeholder: 'e.g. All Messages, Inbox',
            required: true,
        );

        $name = Str::studly($name);
        $key = Str::kebab($name);
        $label = Str::headline($name);

        $sourcesOption = $this->option('sources');
        $sourcesRaw = $sourcesOption ?? (
            $this->option('no-interaction')
                ? ''
                : text(
                    label: 'Which source keys should this page combine?',
                    placeholder: 'e.g. staff,support',
                    required: true,
                )
        );

        $sourceKeys = collect(explode(',', (string) $sourcesRaw))
            ->map(fn (string $value): string => trim($value))
            ->filter()
            ->values()
            ->all();

        if ($sourceKeys === []) {
            $this->components->warn('No source keys provided; the generated aggregate will list no sources. Edit getSourceKeys() to add them.');
        }

        $sourceKeysCode = collect($sourceKeys)
            ->map(fn (string $value): string => "'".$value."'")
            ->implode(', ');

        $aggregateClass = "{$name}AggregateChatSource";
        $pageClass = "{$name}ChatPage";

        $this->createAggregate($key, $label, $aggregateClass, $pageClass, $sourceKeysCode);
        $this->createPage($key, $pageClass);

        info("Aggregate [{$aggregateClass}] and page [{$pageClass}] created successfully.");
        $this->newLine();
        $this->components->bulletList([
            "Aggregate: <comment>app/Chat/{$aggregateClass}.php</comment>",
            "Page:      <comment>app/Filament/Pages/{$pageClass}.php</comment>",
        ]);

        $this->newLine();
        $this->components->info('Register the aggregate in your panel provider:');
        $this->line("  FilamentChatPlugin::make()->aggregates([\\App\\Chat\\{$aggregateClass}::class])");

        return self::SUCCESS;
    }

    protected function createAggregate(string $key, string $label, string $aggregateClass, string $pageClass, string $sourceKeysCode): void
    {
        $stub = $this->files->get($this->getStubPath('chat-aggregate.php.stub'));

        $pageFullClass = "App\\Filament\\Pages\\{$pageClass}";

        $content = str_replace(
            ['{{ namespace }}', '{{ class }}', '{{ key }}', '{{ label }}', '{{ pageClass }}', '{{ pageBasename }}', '{{ sourceKeys }}'],
            ['App\\Chat', $aggregateClass, $key, $label, $pageFullClass, $pageClass, $sourceKeysCode],
            $stub,
        );

        $path = app_path("Chat/{$aggregateClass}.php");
        $this->ensureDirectoryExists(dirname($path));
        $this->files->put($path, $content);
    }

    protected function createPage(string $key, string $pageClass): void
    {
        $stub = $this->files->get($this->getStubPath('chat-aggregate-page.php.stub'));

        $content = str_replace(
            ['{{ namespace }}', '{{ class }}', '{{ key }}'],
            ['App\\Filament\\Pages', $pageClass, $key],
            $stub,
        );

        $path = app_path("Filament/Pages/{$pageClass}.php");
        $this->ensureDirectoryExists(dirname($path));
        $this->files->put($path, $content);
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
