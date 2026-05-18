<?php

declare(strict_types=1);

namespace ZEDMagdy\FilamentChat;

use Filament\Contracts\Plugin;
use Filament\Panel;

class FilamentChatPlugin implements Plugin
{
    /** @var array<class-string<ChatSource>> */
    protected array $sources = [];

    /** @var array<class-string<AggregateChatSource>> */
    protected array $aggregates = [];

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        $id = app(static::class)->getId();

        try {
            /** @var static $plugin */
            $plugin = filament($id);

            return $plugin;
        } catch (\LogicException $e) {
            foreach (filament()->getPanels() as $panel) {
                if ($panel->hasPlugin($id)) {
                    /** @var static $plugin */
                    $plugin = $panel->getPlugin($id);

                    return $plugin;
                }
            }

            throw $e;
        }
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

    /**
     * @param  array<class-string<AggregateChatSource>>  $aggregates
     */
    public function aggregates(array $aggregates): static
    {
        $this->aggregates = $aggregates;

        return $this;
    }

    /**
     * @return array<class-string<AggregateChatSource>>
     */
    public function getAggregates(): array
    {
        return $this->aggregates;
    }

    /**
     * @return array<AggregateChatSource>
     */
    public function getResolvedAggregates(): array
    {
        return array_map(
            fn (string $aggregateClass): AggregateChatSource => app($aggregateClass),
            $this->aggregates,
        );
    }

    public function getAggregate(string $key): ?AggregateChatSource
    {
        foreach ($this->getResolvedAggregates() as $aggregate) {
            if ($aggregate->getKey() === $key) {
                return $aggregate;
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

        foreach ($this->getResolvedAggregates() as $aggregate) {
            $pages[] = $aggregate->getPageClass();
        }

        $panel->pages($pages);
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
