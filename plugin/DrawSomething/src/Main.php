<?php

use php\Action;
use php\PluginBase;
use php\event\PrivateMessageEvent;
use php\event\GroupMessageEvent;

class Main extends PluginBase {
    public const TAG = "DrawSomething";

    private $path;
    /** @var array */
    private $config;

    public function onLoad(): void {
        $this->handler->log(self::TAG, "Load");
        $this->path = getcwd() . "/data/DrawSomething";
        if (!file_exists($this->path . "/data"))
            mkdir($this->path, 0777, true);
        if (!file_exists($this->path . "/config.json")) {
            file_put_contents($this->path . "/config.json", json_encode([
                "player.max" => 10,
                "player.min" => 2,
                "game.round" => 2,
                "game.tickPeriod" => 3,
                "game.questions" => [
                    [
                        "word" => "apple",
                        "tip" => "A fruit"
                    ],
                    [
                        "word" => "banana",
                        "tip" => "A fruit"
                    ]
                ]
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }
        $this->config = json_decode(file_get_contents($this->path . "/config.json"), true);
        $this->handler->on("groupmessage", "onGroupMessage", $this);
        $this->handler->on("privatemessage", "onPrivateMessage", $this);
        $this->handler->on("tick", "onTick", $this);
    }

    public function onTick($_): void {
        foreach ($this->getAllGroupSessions() as $session) {
            if ($session["status"] === "playing") {
                if (($session["time"] * $this->config["game.tickPeriod"]) % 30 === 0) {
                    if ($session["round"] >= $this->config["game.round"]) {
                        Action::sendGroupMessage($session["groupId"], "游戏结束");
                        usleep(100000);
                        $id = array_search(max($session["scores"]), $session["scores"]);
                        Action::sendGroupMessage($session["groupId"], "恭喜{$id}成为本次游戏的冠军");
                        $this->delGroupSession($session["groupId"]);
                        return;
                    }

                    if ($session["word"] !== null) {
                        Action::sendGroupMessage($session["groupId"], "正确答案是 {$session['word']}");
                        usleep(100000);
                    }

                    $question = $this->config["game.questions"][rand(0, count($this->config["game.questions"]) - 1)];

                    Action::sendPrivateMessage($session["players"][$session["turn"]],
                        "请画出{$question['word']}, 并发送至群里");
                    usleep(100000);
                    $session["word"] = $question["word"];
                    Action::sendGroupMessage($session["groupId"], "这一轮是" . $session["players"][$session["turn"]]);
                    usleep(100000);
                    Action::sendGroupMessage($session["groupId"], "回合开始, 提示: {$question['tip']}, 请把答案私聊发送给我哟");

                    $session["turn"]++;
                    if ($session["turn"] >= count($session["players"])) {
                        $session["turn"] = 0;
                        $session["round"] += 1;
                    }
                    $session["time"] = 0;
                }
                $session["time"]++;
                $this->saveGroupSession($session);
            }
        }
    }

    public function onGroupMessage(GroupMessageEvent $event): void {
        if (!preg_match("/^#\s*.+/", $event->message))
            return;
        $event->message = preg_replace("/^#\s*/", "", $event->message);
        if ($event->message === "创建你画我猜") {
            $session = $this->getGroupSession($event->groupId);
            $this->saveGroupSession($session);
            $event->reply("创建成功, 请输入'加入你画我猜'加入游戏");
            $this->handler->preventAi();
            $this->handler->finalDispose();
        } else {
            if ($this->hasGroupSession($event->groupId)) {
                if ($event->message === "结束游戏") {
                    $this->delGroupSession($event->groupId);
                    $event->reply("游戏已结束");
                    return;
                }

                $session = $this->getGroupSession($event->groupId);

                if ($session["status"] === "waiting") {
                    if ($event->message === "加入你画我猜") {
                        if (count($session["players"]) >= $this->config["player.max"]) {
                            $event->reply("游戏以达到最大人数, 您暂时无法加入");
                        } else {
                            array_push($session["players"], $event->sender["user_id"]);
                            $event->reply("{$event->sender['user_id']}已经加入了游戏");
                        }
                    } elseif ($event->message === "开始你画我猜") {
                        $session["status"] = "playing";
                        shuffle($session["players"]);
                        $event->reply("游戏即将开始, 请做好准备");
                    }
                }

                $this->handler->preventAi();
                $this->handler->finalDispose();
                $this->saveGroupSession($session);
            }
        }
    }

    public function onPrivateMessage(PrivateMessageEvent $event): void {
        $hasFind = null;
        foreach ($this->getAllGroupSessions() as $session) {
            if (in_array($event->userId, $session["players"])) {
                $hasFind = $session;
            }
        }
        if ($hasFind) {
            $session = $hasFind;
            if ($session["status"] !== "playing")
                return;
            if (!in_array($event->userId, $session["players"]))
                return;
            if ($event->message === $session["word"]) {
                Action::sendGroupMessage($session["groupId"], "{$event->userId} 猜出了正确的答案");
                $session["scores"][$event->userId] = ($session["scores"][$event->userId] ?? 0) + 10;
            } else {
                Action::sendGroupMessage($session["groupId"],
                    "{$event->userId}: {$event->message}, 相似度: "
                    . similar_text($event->message, $session["word"]) . "%");
            }
            $this->saveGroupSession($session);
        }
    }

    public function getAllGroupSessions(): array {
        $ret = [];
        foreach (scandir($this->path . "/data") as $name) {
            if ($name === "." || $name === "..")
                continue;
            array_push($ret, $this->getGroupSession((int) $name));
        }
        return $ret;
    }

    public function delGroupSession(int $groupId): bool {
        return unlink($this->path . "/data/{$groupId}");
    }

    public function hasGroupSession(int $groupId): bool {
        return file_exists($this->path . "/data/{$groupId}");
    }

    public function saveGroupSession(array $session): void {
        $path = $this->path . "/data/{$session['groupId']}";
        file_put_contents($path, json_encode($session));
    }

    public function getGroupSession(int $groupId): array {
        $path = $this->path . "/data/{$groupId}";
        if (!file_exists($path)) {
            file_put_contents($path, json_encode([
                "lastActiveTime" => time(),
                "groupId" => $groupId,
                "players" => [],
                "scores" => [],
                "round" => 0,
                "turn" => 0,
                "word" => null,
                "status" => "waiting",
                "time" => 0
            ]));
            $groupData = json_decode(file_get_contents($path), true);
        } else {
            $groupData = json_decode(file_get_contents($path), true);
            $groupData["lastActiveTime"] = time();
        }
        return $groupData;
    }

    public function onUnload(): void {
        $this->handler->log(self::TAG, "Unload");
    }
}