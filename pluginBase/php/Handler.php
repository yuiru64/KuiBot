<?php
namespace php;

use php\event\BaseEvent;

class Handler {
    /** @var Handler */
    public static $singleton;
    public static $localMode = false;

    private $eventData;
    private $actionData = null;
    private $listener = [];
    private $debugData = [];
    private $option = [
        "ai" => true,
        "final" => false
    ];

    public function __construct(string $eventData) {
        $this->eventData = json_decode($eventData, true);
        self::$singleton = $this;
    }

    public function handle(PluginBase $base): void {
        try {
            $ret = BaseEvent::getEvent($this, $this->eventData);
            if ($ret === null) {
                $this->warning("Handler", "No class can handle data:\n" . var_export($this->eventData, true) . "\n");
            } else {
                [$name, $event] = $ret;
                $base->onLoad();
                if (isset($this->listener[$name])) {
                    $this->listener[$name]($event);
                }
                $base->onUnload();
            }
        } catch (\Throwable $th) {
            $this->error("Handler", $th->getMessage() . "\n" . $th->__toString());
        }
        echo json_encode([
            "debug" => $this->debugData,
            "action" => $this->actionData,
            "option" => $this->option
        ]);
    }

    public function action(string $key, string $value): void {
        if ($this->actionData === null) {
            $this->actionData = [];
        }
        $this->actionData[$key] = $value;
    }

    public function finalDispose(): void {
        $this->option["final"] = true;
    }

    public function preventAi(): void {
        $this->option["ai"] = false;
    }

    public function print(string $type, string $tag, string $msg): void {
        array_push($this->debugData, [$type, $tag, $msg]);
    }

    public function log(string $tag, string $msg): void {
        $this->print("Log", $tag, $msg);
    }

    public function warning(string $tag, string $msg): void {
        $this->print("Warning", $tag, $msg);
    }

    public function error(string $tag, string $msg): void {
        $this->print("Error", $tag, $msg);
    }

    public function debug(string $tag, string $msg): void {
        $this->print("Debug", $tag, $msg);
    }

    public function dump(string $tag, $var): void {
        $this->debug($tag, var_export($var, true));
    }

    /**
     * @param callable|string $callback
    */
    public function on(string $name, $callback, object $obj = null): void {
        if (is_string($callback)) {
            $reflectObj = new \ReflectionObject($obj);
            $reflectMethod = $reflectObj->getMethod($callback);
            $this->listener[$name] = function ($event) use ($reflectMethod, $obj) {
                $reflectMethod->invoke($obj, $event);
            };
        } else {
            $this->listener[$name] = $callback;
        }
    }
}