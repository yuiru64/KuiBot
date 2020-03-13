<?php
require_once __DIR__ . "/../autoload@bare.php";
require_once $argv[1];

use php\Handler;
use php\PluginBase;

$buf = "";
while (!feof(STDIN)) {
    $buf .= fread(STDIN, 1024);
}
$handler = new Handler($buf);
if (isset($argv[3]) && $argv[3] === "localMode")
    Handler::$localMode = true;
$base = new $argv[2]($handler);
if (!($base instanceof PluginBase)) {
    $handler->error("index", "Plugin Not extends PluginBase");
} else {
    $handler->handle($base);
}
