#!/bin/env python
# coding=utf-8
import hmac
from flask import Flask, request
import os
import requests
import json
from core.PluginLoader import PluginLoader
from core.Log import Log

os.chdir(os.path.dirname(os.path.realpath(__file__)))
config = json.loads(open(os.getcwd() + "/config.json").read())
loader = PluginLoader(os.getcwd() + "/plugin", config)
app = Flask(__name__)
debug = config["server"]["debug"]

@app.route('/', methods=['POST'])
def receive():
    global config, loader, debug
    sig = hmac.new(bytes(config["bot.secret"], encoding="utf-8"), request.get_data(), 'sha1').hexdigest()
    received_sig = request.headers['X-Signature'][len('sha1='):]
    if sig == received_sig:
        data = request.get_data().decode("utf-8")
        if debug:
            Log.debug("main", data)
        ret = loader.runPlugins(data)
        if ret is not None:
            ret = json.dumps(ret)
            if debug:
                Log.debug("main", ret)
        return ret if ret is not None else ""
    else:
        pass
    return ""

if __name__ == '__main__':
    app.run(
      host=config["server"]["host"],
      port=config["server"]["port"],
      debug=config["server"]["debug"]
    )
