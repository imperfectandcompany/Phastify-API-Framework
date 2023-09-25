<?php
class LocalizationCache
{
    private $cache = [];

    public function get($key)
    {
        return $this->cache[$key] ?? null;
    }

    public function set($key, $value)
    {
        $this->cache[$key] = $value;
    }
}