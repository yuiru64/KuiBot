<?php
namespace php\event;

class FriendAddEvent extends BaseEvent {
    /** @var int */
    public $userId;
    /** @var string */
    public $comment;

    public function init(array $eventData): void {
        $this->userId = $eventData["user_id"];
        $this->comment = $eventData["comment"];
    }

    public function approve(string $remark = null): void {
        $this->handler->action("approve", true);
        if ($remark)
            $this->handler->action("remark", $remark);
    }
}