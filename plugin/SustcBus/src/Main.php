<?php

use php\PluginBase;

class Main extends PluginBase {
    public const TAG = "SustcBus";
    /** @var array */
    private $config;

    public function onLoad(): void {
        $this->handler->log(self::TAG, "Load");
        $this->handler->on("privatemessage", "onMessage", $this);
        $this->handler->on("groupmessage", "onMessage", $this);
        $this->config = json_decode(file_get_contents(getcwd() . "/data/SustcBus/data.json"), true);
    }

    public function onMessage($event): void {
        if (is_string($ret = $this->handleMessage($event->message))) {
            $this->handler->preventAi();
            $event->reply($ret);
        }
    }

    public function handleMessage(string $message) {
        if (preg_match("/^#?去(科研楼|欣园)( (工作(日)?)|(休息(日)?))?/", $message) === 1) {
            $leaveBuf = "";
            $toLeaveBuf = "";
            $workday = strpos($message, "工作") !== false ? true :
                strpos($message, "休息") !== false ? false :
                (((int) (new \DateTime())->format("w")) + 6) % 7 < 5;
            $workday = $workday ? "workday" : "vacation";
            $dest = strpos($message, "欣园") !== false ? 1 : 0;
            $this->handler->debug("SS", $dest);
            foreach ($this->config[$dest][$workday] as $k => $it) {
                $timezone = new \DateTimeZone("+0800");
                $time = (new \DateTime($it, $timezone))->getTimestamp();
                $now = (new \DateTime("now", $timezone))->getTimestamp();
                if ($workday === "workday" && in_array($k, $this->config[$dest]["busy"])) {
                    $it = "*{$it}*";
                }
                if ($now >= $time && ($now - $time) < 40 * 60) {
                    $leaveBuf .= $it . " ";
                }
                if ($now < $time && ($time - $now) < 120 * 60) {
                    $toLeaveBuf .= $it . " ";
                }
            }
            $buf = "已出发:\n" . trim($leaveBuf) . "\n未出发:\n" . $toLeaveBuf;
            $this->handler->debug("SS", $buf);
            return $buf;
        }
        return null;
    }

    public function onUnload(): void {
        $this->handler->log(self::TAG, "Unload");
    }
}