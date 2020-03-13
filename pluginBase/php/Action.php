<?php
namespace php;

class Action {
    protected static $apiMap = [
        "getFriendList" => "get_friend_list",
        "getGroupList" => "get_group_list",
        "getGroupMember" => "get_group_member_info",
        "getGroupMemberList" => "get_group_member_list",
        "sendPrivateMessage" => "send_private_msg",
        "sendGroupMessage" => "send_group_msg",
        "sendDiscussMessage" => "send_discuss_msg",
        "like" => "send_like",
        "kickGroupUser" => "set_group_kick",
        "banGroup" => "set_group_whole_ban",
        "cancelBanGroup" => "set_group_whole_ban",
        "banGroupMember" => "set_group_ban",
        "cancelBanGroupMember" => "set_group_ban"
    ];

    /** @return string|null */
    private static function sendApiRequest(string $api, array $data = null) {
        if (!Handler::$localMode) {
            return Request::postJson(
                "http://" . Config::$config["bot.api"]["host"] . ":" . Config::$config["bot.api"]["port"] . "/" . self::$apiMap[$api],
                $data
            );
        } else {
            $buf = "{$api}: " . json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            Handler::$singleton->log("Action", $buf);
        }
        return null;
    }

    private static function tryToJson($data) {
        if (is_null($data)) return null;
        return json_decode($data, true);
    }

    /**
     * @return null|array
     */
    public static function getFriendList() {
        return self::tryToJson(self::sendApiRequest("getFriendList"));
    }

    /** @return null|array */
    public static function getGroupList() {
        return self::tryToJson(self::sendApiRequest("getGroupList"));
    }

    public static function getGroupMember(int $groupId, int $userId) {
        return self::tryToJson(self::sendApiRequest("getGroupMember"), [
            "group_id" => $groupId,
            "user_id" => $userId
        ]);
    }

    /** @return null|array */
    public static function getGroupMemberList(int $groupId) {
        return self::tryToJson(self::sendApiRequest("getGroupMemberList"), [
            "group_id" => $groupId
        ]);
    }

    public static function sendPrivateMessage(int $userId, string $msg): void {
        self::sendApiRequest("sendPrivateMessage", [
            "user_id" => $userId,
            "message" => $msg
        ]);
    }

    public static function sendGroupMessage(int $groupId, string $msg): void {
        self::sendApiRequest("sendGroupMessage", [
            "group_id" => $groupId,
            "message" => $msg
        ]);
    }

    public static function sendDiscussMessage(int $discussId, string $msg): void {
        self::sendApiRequest("sendDiscussMessage", [
            "discuss_id" => $discussId,
            "message" => $msg
        ]);
    }

    public static function like(int $userId, int $times): void {
        self::sendApiRequest("like", [
            "user_id" => $userId,
            "times" => min($times, 10)
        ]);
    }

    public static function kickGroupMember(int $groupId, int $userId): void {
        self::sendApiRequest("kickGroupMember", [
            "group_id" => $groupId,
            "user_id" => $userId
        ]);
    }

    public static function banGroup(int $groupId): void {
        self::sendApiRequest("banGroup", [
            "group_id" => $groupId,
            "enable" => true
        ]);
    }

    public static function cancelBanGroup(int $groupId): void {
        self::sendApiRequest("cancelBanGroup", [
            "group_id" => $groupId,
            "enable" => false
        ]);
    }

    public static function banGroupMember(int $groupId, int $userId, int $seconds): void {
        self::sendApiRequest("banGroupMember", [
            "group_id" => $groupId,
            "user_id" => $userId,
            "duraction" => $seconds
        ]);
    }

    public static function cancelBanGroupMember(int $groupId, int $userId): void {
        self::sendApiRequest("cancelBanGroupMember", [
            "group_id" => $groupId,
            "user_id" => $userId,
            "duraction" => 0
        ]);
    }
}