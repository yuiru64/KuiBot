<?php

use php\PluginBase;

class Main extends PluginBase {
    private $path;
    private $config;
    private $lexicon;

    public function onLoad(): void {
        $this->handler->log("Keywords", "Load");
        $this->path = getcwd() . "/data/Keywords";
        $cachePath = $this->path . "/cache";
        if (file_exists($cachePath)) {
            $obj = unserialize(file_get_contents($cachePath));
            $this->config = $obj["config"];
            $this->lexicon = $obj["lexicon"];
        } else {
            $this->config = json_decode(file_get_contents($this->path . "/config.json"), true);
            $tmp = trim(file_get_contents($this->path . "/" . $this->config["lexicon"]));
            $tmp = str_replace("\r\n", "\n", $tmp);
            $tmp = str_replace("\\\n", "&br;", $tmp);
            $tmp = explode("\n\n", $tmp);
            $this->lexicon = [];
            foreach ($tmp as $it) {
                $its = explode("\n", $it);
                $its[0] = preg_split("/ +/", str_replace("\\ ", "&nbsp;", $its[0]));
                foreach ($its[0] as &$v) {
                    $v = str_replace("&nbsp;", " ", $v);
                }
                array_push($this->lexicon, $its);
            }
            $obj = [
                "config" => $this->config,
                "lexicon" => $this->lexicon
            ];
            file_put_contents($cachePath, serialize($obj));
        }
        $this->handler->on("privatemessage", "onMessage", $this);
        $this->handler->on("groupmessage", "onMessage", $this);
    }

    public function onMessage($event) {
        $event->message = trim(preg_replace("/^#/", "", $event->message));
        if (preg_match("/^" . $this->config["prefix"] . "/", $event->message) !== 1)
            return;
        $event->message = trim(preg_replace("/^" . $this->config["prefix"] . " */", "", $event->message));
        $keyword = null;
        $msg = null;
        $find = false;
        foreach ($this->lexicon as $v) {
            [$keywords, $msg] = $v;
            foreach ($keywords as $keyword) {
                if (preg_match("/{$keyword}/", $event->message) != 0) {
                    $find = true;
                    break 2;
                }
            }
        }
        if ($find) {
            $this->handler->preventAi();
            $event->reply(str_replace("&br;", "\n", $msg));
        }
    }

    public function onUnload(): void {
        $this->handler->log("Keywords", "Unload");
    }
}