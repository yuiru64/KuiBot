<?php
namespace php\event;

class GroupAddEvent extends BaseEvent {
    /** @var int */
    public $userId;
    /** @var int */
    public $groupId;
    /** @var string */
    public $comment;
    /** @var string */
    public $subType;

    public function init(array $eventData): void {
        $this->userId = $eventData["user_id"];
        $this->groupId = $eventData["group_id"];
        $this->comment = $eventData["comment"];
        $this->subType = $eventData["sub_type"];
    }

    public function approve(): void {
        $this->handler->action("approve", true);
    }

    public function reject(string $reason): void {
        $this->handler->action("approve", false);
        if ($reason)
            $this->handler->action("reason", $reason);
    }
}