import os
import sys
import json
import functools
from typing import List, Callable
from core.PluginLoader import PluginLoader
from core.Log import Log

def test(description: str=""):
    def warp(fun: Callable):
        @functools.wraps(fun)
        def decoratedFun():
            Log.log("testUtil", "=> Case Name: {}".format("None" if description is None else description))
            fp = open(os.getcwd() + "/config.json")
            config = json.loads(fp.read())
            loader = PluginLoader(os.getcwd() + "/plugin", config)
            events = []
            def dispatch(*args: tuple):
                events.extend(args)
            fun(dispatch)
            for event in events:
                ret = loader.runPlugins(json.dumps(event))
                if ret:
                    Log.debug("testUtil", "=> Final Result:\n" + json.dumps(ret, indent=4, ensure_ascii=False))
        return decoratedFun
    return warp

def run(_list: List[Callable]):
    PluginLoader.localMode = True
    os.chdir(os.path.dirname(os.path.realpath(__file__ )) + "/..")
    for fun in _list:
        fun()
        print()