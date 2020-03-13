<?php
namespace php\event;

use php\Handler;

abstract class BaseEvent {
    public static $eventMap = [];

    /** @return object[]|null */
    public static function getEvent(Handler $handler, array $eventData) {
        $class = null;
        $name = null;
        foreach (self::$eventMap as $k => $v) {
            [$class, $matchFun] = $v;
            if ($matchFun($eventData)) {
                $name = $k;
                break;
            }
        }
        if ($name === null)
            return null;
        $event = new $class($handler);
        $event->init($eventData);
        return [$name, $event];
    }

    public static function registerEvent(string $name, string $class, callable $matchFun): void {
        self::$eventMap[$name] = [$class, $matchFun];
    }

    /** @var Handler */
    protected $handler;

    public function __construct(Handler $handler) {
        $this->handler = $handler;
    }

    function init(array $eventData): void {}
}
BaseEvent::registerEvent("privatemessage", PrivateMessageEvent::class, function ($eventData) {
    return $eventData["post_type"] === "message" && $eventData["message_type"] === "private";
});
BaseEvent::registerEvent("groupmessage", GroupMessageEvent::class, function ($eventData) {
    return $eventData["post_type"] === "message" && $eventData["message_type"] === "group";
});
BaseEvent::registerEvent("discussmessage", DiscussMessageEvent::class, function ($eventData) {
    return $eventData["post_type"] === "message" && $eventData["message_type"] === "group";
});
BaseEvent::registerEvent("friendadd", FriendAddEvent::class, function ($eventData) {
    return $eventData["post_type"] === "request" && $eventData["request_type"] === "friend";
});
BaseEvent::registerEvent("groupadd", GroupAddEvent::class, function ($eventData) {
    return $eventData["post_type"] === "request" && $eventData["request_type"] === "group";
});
BaseEvent::registerEvent("tick", TickEvent::class, function ($eventData) {
    return $eventData["post_type"] === "meta_event" && $eventData["meta_event_type"] === "heartbeat";
});