<?php
namespace php;

class Config {
    /** @var array */
    public static $config;
    public static function loadConfig(string $path): void {
        self::$config = json_decode(file_get_contents($path), true);
    }
}
Config::loadConfig("config.json");
