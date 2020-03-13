<?php
namespace php\event;

class GroupMessageEvent extends BaseEvent {
    /** @var array */
    public $sender;
    /** @var int */
    public $groupId;
    /** @var string */
    public $message;

    public function init(array $eventData): void {
        $this->sender = [
            "user_id" => $eventData["user_id"],
            "nickname" => $eventData["sender"]["nickname"] ?? null,
            "sex" => $eventData["sender"]["sex"] ?? "unknown",
            "age" => $eventData["sender"]["age"] ?? 0,
        ];
        $this->groupId = $eventData["group_id"];
        $this->message = $eventData["message"];
    }

    public function reply(string $message): void {
        $this->handler->action("reply", $message);
    }
}