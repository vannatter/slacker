<?php

declare(strict_types=1);

namespace Slacker\Plugin;

/**
 * Explicit set of enabled plugins. Only commands registered here can run.
 */
final class PluginRegistry
{
    /** @var array<string, Plugin> */
    private array $plugins = [];

    public function add(Plugin $plugin): self
    {
        $this->plugins[strtolower($plugin->name())] = $plugin;
        return $this;
    }

    public function get(string $name): ?Plugin
    {
        return $this->plugins[strtolower($name)] ?? null;
    }

    /** @return array<string, Plugin> */
    public function all(): array
    {
        $plugins = $this->plugins;
        ksort($plugins);
        return $plugins;
    }
}
