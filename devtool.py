#!/bin/env python
import sys
import os
import shutil
import json
import re
import importlib
from core.Log import Log

USAGE = """
./devtool.py <action> <args>
    action  args                    description
    -------------------------------------------
    version                         Get Version
    help                            Get Help
    test    <name>                  Test
    new     (plugin|test)           Create new plugin or test
    del     (plugin|test) <name>    Del Plugin or test
""".strip()
LOADER = """
<?php
$mode = __FILE__;
$pos = strpos($mode, "@");
$mode = $pos === false ? "baare" : substr($mode, $pos + 1, strrpos($mode, ".") - $pos - 1);
$libload = function () {
	$dirs = scandir(__DIR__);
	foreach ($dirs as $dir) {
		$autoload = __DIR__ . "/{$dir}/autoload.php";
		if (file_exists($autoload))
			require_once($autoload);
		$autoload = __DIR__ . "/{$dir}/autoload@bare.php";
		if (file_exists($autoload))
			require_once($autoload);
		$autoload = __DIR__ . "/{$dir}/autoload@module.php";
		if (file_exists($autoload))
			require_once($autoload);
	}
};
if ($mode === "lib") {
	$libload();
} else {
	if ($mode !== "bare" && $mode !== "module") {
		echo "[autoload/" . __FILE__ . "] Invalid mode of autoload" . PHP_EOL;
		exit;
	}
	$prefix = $mode === "module" ? "/src/" : "/";
	spl_autoload_register(function ($class) use ($prefix) {
		$baseDir = __DIR__ . $prefix;
		$file = str_replace('\\\\', '/', $baseDir . $class) . '.php';
		// echo "[autoload] Loading " . $file . PHP_EOL;
		if (file_exists($file)) {
			require_once($file);
		}
	});
}
""".strip()
PLUGIN = """
<?php

use php\PluginBase;
use php\event\PrivateMessageEvent;

class Main extends PluginBase {
    public const TAG = "{{tag}}";

    public function onLoad(): void {
        $this->handler->log(self::TAG, "Load");
        $this->handler->on("privatemessage", "onPrivateMessage", $this);
    }

    public function onPrivateMessage(PrivateMessageEvent $event): void {
        if ($event->message === "Hello") {
            $this->handler->preventAi();
            $event->reply("Hello World");
        }
    }

    public function onUnload(): void {
        $this->handler->log(self::TAG, "Unload");
    }
}
""".strip()
TEST = """
####### Please don't modify following #############################
import sys, os
sys.path.append(os.path.abspath(os.path.dirname(__file__) + "/.."))
###################################################################

import testUtil.testUtil as testUtil
import testUtil.eventUtil as eventUtil

@testUtil.test("Description here")
def test(dispatch):
    # dispatch(your event)
    pass

def main():
    # Please add your test here
    testUtil.run([test])
""".strip()
LOGO = """
    ___       ___       ___       ___       ___       ___       ___
   /\  \     /\  \     /\__\     /\  \     /\  \     /\  \     /\__\\
  /::\  \   /::\  \   /:/ _/_    \:\  \   /::\  \   /::\  \   /:/  /
 /:/\:\__\ /::\:\__\ |::L/\__\   /::\__\ /:/\:\__\ /:/\:\__\ /:/__/
 \:\/:/  / \:\:\/  / |::::/  /  /:/\/__/ \:\/:/  / \:\/:/  / \:\  \\
  \::/  /   \:\/  /   L;;/__/   \/__/     \::/  /   \::/  /   \:\__\\
   \/__/     \/__/                         \/__/     \/__/     \/__/
"""[1:-1]
VERSION = "1.0.1"
INFO = "devtool " + VERSION + " By Yvzzi\n" +\
"""
1.0.0 Only support php plugin now, support serveral events simulation
"""
TAG = "devtool"

path = os.path.realpath(os.path.dirname(__file__) )
_len = len(sys.argv)
if _len == 1 or sys.argv[1] == "help":
    Log.log(TAG, USAGE)
elif sys.argv[1] == "version":
    Log.log(TAG, "\n" + LOGO + "  v" + VERSION + "\n\n" + INFO)
elif sys.argv[1] == "test":
    if _len < 3:
        Log.error(TAG, "Please input test name")
    for i in range(2, _len):
        Log.log(TAG, "-*-*-*-*-*-*- Test Name: {} -*-*-*-*-*-*-".format(sys.argv[i]))
        importlib.import_module("test." + sys.argv[i]).main()
        Log.log(TAG, "-*-*-*-*-*-*- End -*-*-*-*-*-*-")
elif sys.argv[1] == "new" and _len >= 3:
    if sys.argv[2] == "plugin":
        path += "/plugin"
        _dict = {}
        _dict["name"]: str = input("? Plugin Name (Use letters or numbers):\n")
        _dict["name"] = _dict["name"][0:1].upper() + _dict["name"][1:]
        if re.match(r"^\w+", _dict["name"]) is None:
            Log.error(TAG, "Invalid format of name")
            sys.exit()
        _dict["author"] = input("? Plugin Author:\n")
        _dict["description"] = input("? Plugin Description:\n")
        _dict["version"] = input("? Plugin Version (Like 1.0.0):\n")
        Log.log(TAG, "\nname: {name}\nauthor: {author}\ndescription: {description}\nversion: {version}".format_map(_dict))
        checkStr = input("? Is that ok, please answer with 'y' or 'n':\n")
        if checkStr != "y":
            sys.exit()
        path = path + "/" + _dict["name"]
        flag = os.path.exists(path)
        if flag:
            Log.error(TAG, "Plugin already exists? Please check {}!".format(path))
        os.makedirs(path)
        fp = open(path + "/plugin.json", "w")
        _dict["main"] = "Main"
        _dict["type"] = "php"
        fp.write(json.dumps(_dict, indent=4. ensure_ascii=False))
        fp.close()
        fp = open(path + "/autoload@module.php", "w")
        fp.write(LOADER)
        fp.close()
        path = path + "/src"
        os.makedirs(path)
        fp = open(path + "/Main.php", "w")
        fp.write(PLUGIN.replace("{{tag}}", _dict["name"]))
        fp.close()
        Log.log(TAG, "Finished")
    elif sys.argv[2] == "test" and _len >= 4:
        if sys.argv[3].strip() == "":
            Log.error(TAG, "Invalid test name")
            sys.exit()
        path += "/test/" + sys.argv[3] + ".py"
        if os.path.exists(path):
            Log.error(TAG, "Test already exists")
            sys.exit()
        fp = open(path, "w")
        fp.write(TEST)
        fp.close()
        Log.log(TAG, "Finished")
elif sys.argv[1] == "del":
    path = os.path.realpath(os.path.dirname(__file__))
    if sys.argv[2] == "plugin" and _len >= 3:
        if sys.argv[3].strip() == "":
            Log.error(TAG, "Invalid plugin name")
            sys.exit()
        if os.path.exists(path + "/plugin/" + sys.argv[3]):
            shutil.rmtree(path + "/plugin/" + sys.argv[3])
        if os.path.exists(path + "/data/" + sys.argv[3]):
            shutil.rmtree(path + "/data/" + sys.argv[3])
        Log.log(TAG, "Finished")
    elif sys.argv[2] == "test" and _len >= 4:
        if sys.argv[3].strip() == "":
            Log.error(TAG, "Invalid plugin name")
            sys.exit()
        if os.path.exists(path + "/test/" + sys.argv[3] + ".py"):
            os.remove(path + "/test/" + sys.argv[3] + ".py")
        Log.log(TAG, "Finished")
