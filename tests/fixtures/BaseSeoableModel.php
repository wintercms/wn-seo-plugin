<?php

namespace Winter\SEO\Tests\Fixtures;

use Winter\Storm\Database\Model;
use Winter\Storm\Database\Traits\ArraySource;
use Winter\Storm\Support\Str;

class BaseSeoableModel extends Model
{
    use ArraySource {
        arraySourceGetDbPath as traitArraySourceGetDbPath;
    }

    public $recordSchema = ['id' => 'increments'];

    public $cacheArray = false;

    public function getTable()
    {
        return 'seo_test_' . Str::kebab(str_replace('\\', '', self::class));
    }

    protected function arraySourceGetDbPath(): string
    {
        return $this->arraySourceGetDbDir() . '/' . Str::kebab(str_replace('\\', '', self::class)) . '-' . md5($this->traitArraySourceGetDbPath()) . '.sqlite';
    }
}
