<?php
namespace php\event;

class PrivateMessageEvent extends BaseEvent {
    /** @var int */
    public $userId;
    /** @var string */
    public $subType;
    /** @var string */
    public $message;
    /** @var array */
    public $sender;

    public function init(array $eventData): void {
        $this->sender = [
            "user_id" => $eventData["user_id"],
            "nickname" => $eventData["sender"]["nickname"] ?? null,
            "sex" => $eventData["sender"]["sex"] ?? "unknown",
            "age" => $eventData["sender"]["age"] ?? 0,
        ];
        $this->userId = $eventData["user_id"];
        $this->subType = $eventData["sub_type"];
        $this->message = $eventData["message"];
    }

    public function reply(string $message): void {
        $this->handler->action("reply", $message);
    }
}