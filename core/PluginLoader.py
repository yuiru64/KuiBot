import os.path
import json
import subprocess
import time
from typing import Union
from subprocess import Popen
from core.Log import Log

class PluginLoader:
    localMode = False

    _plugin: list = []
    _config: dict
    _root: str
    def __init__(self, root, config):
        self._config = config
        self._root = root
        dirs = os.listdir(root)
        for _dir in dirs:
            path = root + "/" + _dir
            if os.path.isdir(path):
                path += "/plugin.json"
                try:
                    fp = open(path, encoding="utf-8")
                except OSError:
                    Log.error("PluginLoader", "Not found plugin.json")
                    continue
                obj = json.loads(fp.read())
                obj["name"] = _dir
                Log.log("PluginLoader", "Enable Plugin {name} v{version} by {author}: {description}".format(**obj))
                self._plugin.append(obj)
        self._sortPluginByPriorty()

    def _sortPluginByPriorty(self):
        _pluginDict = {i["name"]: i for i in self._plugin}
        priority: dict = self._config["execute.priority"]
        priority = sorted(priority, key=lambda it: it[0], reverse=True)
        _new = []
        for i in priority:
            if not os.path.exists(self._root + "/" + i):
                Log.warning("PluginLoader", "Not found {} but you set a priorty here".format(_pluginDict[i]))
            _new.append(_pluginDict[i])
            del _pluginDict[i]
        for i in _pluginDict:
            _new.append(_pluginDict[i])
        self._plugin = _new

    def runPlugins(self, _input: str) -> Union[dict, None]:
        result = None
        for plugin in self._plugin:
            _type = plugin["type"]
            cmd = self._config["execute.map"][_type] + " "\
                + "pluginBase/php/index.php "\
                + self._root + "/" + plugin["name"] + "/autoload@module.php "\
                + plugin["main"] + " "\
                + "localMode" if PluginLoader.localMode else ""
            try:
                ret = PluginLoader._executeTimeout(cmd, self._config["execute.timeout"], _input)
            except Exception as e:
                Log.error("PluginLoader", e.__str__())
                return None
            if ret is None:
                Log.warning("PluginLoader", "Plugin {} executes time out".format(plugin["name"]))
                continue

            obj = json.loads(ret)
            PluginLoader._handlePluginPrint(obj["debug"])
            if result is None:
                result = obj
            else:
                result["action"] = PluginLoader._mergeObj(result["action"], obj["action"])
                result["option"] = PluginLoader._mergeObj(result["option"], obj["option"])
            if obj["option"]["final"]:
                Log.debug("PluginLoader", "Plugin {} prevent iterator proccess".format(plugin["name"]))
                break
        if result:
            if not result["option"]["ai"]:
                if result["action"] is None:
                    result["action"] = {"block": True}
                else:
                    result["action"]["block"] = True
            return result["action"]
        else:
            return None

    @staticmethod
    def _mergeObj(target: Union[dict, None], source: Union[dict, None]):
        if target is None:
            return source
        if source is None:
            return target
        target.update(source)
        return target

    @staticmethod
    def _handlePluginPrint(data: list):
        for i in data:
            if i[0] == "Log":
                Log.log(i[1], i[2])
            if i[0] == "Warning":
                Log.warning(i[1], i[2])
            if i[0] == "Error":
                Log.error(i[1], i[2])
            if i[0] == "Debug":
                Log.debug(i[1], i[2])

    @staticmethod
    def _executeTimeout(cmd: str, timeout: int, _input: str):
        fp = Popen(cmd, stdin=subprocess.PIPE, stdout=subprocess.PIPE, stderr=subprocess.PIPE, shell=True)
        fail = False
        timeOut = False
        try:
            res = fp.communicate(bytes(_input, "utf-8"), timeout=timeout / 1000)
        except subprocess.TimeoutExpired:
            timeOut = True
        if fp.poll() is None:
            fp.terminate()
            time.sleep(0.001)
            if fp.poll() is not None:
                fp.kill()
            fail = True
        if res[1] != b"" or fail:
            _str = res[1]
            try:
                _str = res[1].decode("utf-8")
            except:
                pass
            raise Exception(_str)
        return None if timeOut else res[0]
