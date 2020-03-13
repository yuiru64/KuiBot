<?php
namespace php;

abstract class PluginBase {
    /** @var Handler */
    protected $handler;

    public function __construct($handler) {
        $this->handler = $handler;
    }

    abstract public function onLoad(): void;
    abstract public function onUnload(): void;
}