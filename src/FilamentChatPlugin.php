<?php

declare(strict_types=1);

namespace ZEDMagdy\FilamentChat;

use Filament\Contracts\Plugin;
use Filament\Panel;

class FilamentChatPlugin implements Plugin
{
    /** @var array<class-string<ChatSource>> */
    protected array $sources = [];

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }

    public function getId(): string
    {
        return 'filament-chat';
    }

    /**
     * @param  array<class-string<ChatSource>>  $sources
     */
    public function sources(array $sources): static
    {
        $this->sources = $sources;

        return $this;
    }

    /**
     * @return array<class-string<ChatSource>>
     */
    public function getSources(): array
    {
        return $this->sources;
    }

    /**
     * @return array<ChatSource>
     */
    public function getResolvedSources(): array
    {
        return array_map(
            fn (string $sourceClass): ChatSource => app($sourceClass),
            $this->sources,
        );
    }

    public function getSource(string $key): ?ChatSource
    {
        foreach ($this->getResolvedSources() as $source) {
            if ($source->getKey() === $key) {
                return $source;
            }
        }

        return null;
    }

    public function register(Panel $panel): void
    {
        $pages = [];

        foreach ($this->getResolvedSources() as $source) {
            $pages[] = $source->getPageClass();
        }

        $panel->pages($pages);
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
