<?php

namespace Winter\SEO\Classes;

/**
 * Base TagContainer class
 */
abstract class TagContainer
{
    /**
     * Array of tag => value pairs keyed by the tag type class name.
     * Value can be a string or an array of tag configuration properties
     */
    protected static array $tags = [];

    /**
     * Set the value of a tag
     */
    public static function set(string|array $name, string $value = ''): void
    {
        if (is_array($name)) {
            foreach ($name as $tagName => $tagValue) {
                if (is_array($tagValue)) {
                    static::append($tagValue);
                    continue;
                }
                static::set((string) $tagName, (string) $tagValue);
            }
        } else {
            static::$tags[static::class][$name] = $value;
        }
    }

    /**
     * Append a tag configuration in array format
     */
    public static function append(array $tagConfig)
    {
        static::$tags[static::class][] = $tagConfig;
    }

    /**
     * Get the value of tag
     */
    public static function get(string $name): ?string
    {
        return static::$tags[static::class][$name] ?? null;
    }

    /**
     * Return all the currently defined tags for this class
     */
    public static function all(): array
    {
        return static::$tags[static::class] ?? [];
    }

    /**
     * Clear all the currently defined tags for this class
     */
    public static function refresh(): void
    {
        unset(static::$tags[static::class]);
    }
}
