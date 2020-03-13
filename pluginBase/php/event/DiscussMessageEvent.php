<?php
namespace php\event;

class DiscussMessageEvent extends BaseEvent {
    /** @var array */
    public $sender;
    /** @var int */
    public $discussId;
    /** @var string */
    public $message;

    public function init(array $eventData): void {
        $this->sender = [
            "user_id" => $eventData["user_id"],
            "nickname" => $eventData["sender"]["nickname"] ?? null,
            "sex" => $eventData["sender"]["sex"] ?? "unknown",
            "age" => $eventData["sender"]["age"] ?? 0,
        ];
        $this->discussId = $eventData["discuss_id"];
        $this->message = $eventData["message"];
    }

    public function reply(string $message): void {
        $this->handler->action("reply", $message);
    }
}