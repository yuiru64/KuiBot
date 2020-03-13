# KuiAI
![](https://img.shields.io/badge/version-1.0.1-green)
In order to give a simple way to use http api of cq project. This is a experimental version and it has nothing with ai hahaha.

## 1. Install
```bash
git clone https://github.com/Yvzzi/KuiAI.git
```

## 2. Structure

```
KuiAIProject
+--core				core of project (no need to care about it)
+--pluginBase		Dependent files of plugin
|  +--php			Dependent files of php plugin
|  +--python		Dependent files of python plugin
|  +--...
+--testUtil			Dependent files of test util
+--test				Put your tests here
+--data				Place plugin data here
+--plugin			Put your plugin here
+--devtool.py		Dev tool
+--main.py			Run server mode (details in 3.1) with it
+--config.json		Config of KuiAI
```

Files or directories shown below are important, you does not need to care about others :

`pluginBase/php/event/*Event.php`
These are classes files of event obj expect `pluginBase/php/event/BaseEvent.php`.

`testUtil/eventUtil.py`
This is used to simulate event of QQ, when you use local-mode (details in 3) and intend to test your plugins.

`config.json`
is a config for KuiAI, but you only need to care about one configuration item in config, **execute.priority**, 

```
{
	"execute.priority": {
		"APlugin": 0,
		"BPlugin": 1
	}
}
```

which give the execution order of plugins. The smaller it is, the earlier that the plugin is executed.

## 3. Usage

### 3.1 Two Mode to run

There are two modes to run this project, server mode and local-test mode. The former is used to handle with real QQ messages. The latter is only used to test your plugins.

### 3.2 Run the server

This need a Compute Cloud, Add more description later.

### 3.3 Development

This a useful tool to make and test your plugin, it is `devtool.py` your can use `python devtool.py help` to get some helps.

#### 3.3.1 Plugin structure

```
plugin/PluginName
+--src
|  +--cn
|     +--hello
|        +--Main.php
+--plugin.json
+--autoload@module.php
```

`plugin.json`
is used to records plugin infos like name, version, description, one thing should be to consider is that **'main' is used to show entrance of plugin, you should give a namespace here. Your plugin name placed in `plugin.json` should be identical with the directory name `PluginName`.**

`src`
is used to please your code, the namespace of `Main` shown above is `cn\hello\Main`.

`autoload@module.php`
is a classloader for php, you does not need to care about it.

#### 3.3.2 Plugin Data

If you need find somewhere to place your data, you can place it in `data/<PluginName>`, where `<PluginName>` is the same as your plugin name in plugin

####3.3.2 Make a plugin

```bash
python devtool.py new plugin
```

It generate a default plugin with entrance `Main`

####3.3.3 Make a test

```bash
python devtool.py new test <testName>
```

####3.3.4 Del a plugin

```bash
python devtool.py del plugin <pluginName>
```

#### 3.3.4 Del a Test

```
python devtool.py del test <testName>
```

### 3.4 Design my plugin

#### 3.4.2 Life cycle (生命周期)

When a plugin boot, the methods of entrance class instance will be invoked as following order:

```
onLoad->Listener if you register a event listener->onUnload
```
A example here.
```php
use php\PluginBase;
use php\event\PrivateMessageEvent;

class Main extends PluginBase {
    public const TAG = "HelloPlugin";

    public function onLoad(): void {
        $this->handler->log(self::TAG, "Load");
    }
    
    public function onUnload(): void {
        $this->handler->log(self::TAG, "Unload");
    }
}
```
It will print
```
[Log/HelloPlugin] Load
[Log/HelloPlugin] Unload
```


#### 3.4.1 Log (日志)

```php
use php\PluginBase;
use php\event\PrivateMessageEvent;

class Main extends PluginBase {
    public const TAG = "HelloPlugin";

    public function onLoad(): void {
        //==============================================================
        $this->handler->log(self::TAG, "Load");
        $this->handler->debug(self::TAG, "Load");
        $this->handler->error(self::TAG, "Load");
        $this->handler->warning(self::TAG, "Load");
        $this->handler->dump(self::TAG, [
            "it" => "can",
            "print" => "a object"
        ]);
        //==============================================================
    }
    
    public function onUnload(): void {
        $this->handler->log(self::TAG, "Unload");
    }
}
```

#### 3.4.2 Register Event Listener (注册监听器)

```php
use php\PluginBase;
use php\event\PrivateMessageEvent;

class Main extends PluginBase {
    public const TAG = "HelloPlugin";

    public function onLoad(): void {
        $this->handler->log(self::TAG, "Load");
        //==============================================================
        $this->handler->on("privatemessage", "onPrivateMessage", $this);
        /**
         * @param string $method Method to response the event, when event appear, 		     * it would be invoked
         * @param object $obj Method is belong to this object instance.
         */
        // public function on(string $eventName, string $method, object $obj);
        //==============================================================
    }
    
    public function onUnload(): void {
        $this->handler->log(self::TAG, "Unload");
    }
}
```

When you use method `on`, you add this class instance `Main` as a event listener of `privatemessage` . When `privatemessage` happened, the method `onPrivateMessage` will be invoked.

#### 3.4.3 Handle events (处理事件)

```php
use php\PluginBase;
use php\event\PrivateMessageEvent;

class Main extends PluginBase {
    public const TAG = "HelloPlugin";

    public function onLoad(): void {
        $this->handler->log(self::TAG, "Load");
        $this->handler->on("privatemessage", "onPrivateMessage", $this);
        /**
         * @param string $method Method to response the event, when event appear, 		     * it would be invoked
         * @param object $obj Method is belong to this object instance.
         */
        // public function on(string $eventName, string $method, object $obj);
    }
    //==============================================================
    public function onPrivateMessage(PrivateMessageEvent $event): void {
        if ($event->message === "Hello") {
            $this->handler->preventAi(); // prevent Default AI to reply message
            $event->reply("Hello World"); // instead of your message
        }
    }
    //==============================================================
    
    public function onUnload(): void {
        $this->handler->log(self::TAG, "Unload");
    }
}
```

#### 3.4.6 Place or read data if you need

```php
use php\PluginBase;
use php\event\PrivateMessageEvent;

class Main extends PluginBase {
    public const TAG = "HelloPlugin";

    public function onLoad(): void {
        $this->handler->log(self::TAG, "Load");
        
        //==============================================================
       	$path = getcwd() . "/data/HelloPlugin/myconfig.json";
        $myconfig = file_get_contents($path);
        $myconfig = json_decode($myconfig, true);
        // and so on...
        //==============================================================
        
        $this->handler->on("privatemessage", "onPrivateMessage", $this);
        /**
         * @param string $method Method to response the event, when event appear, 		     * it would be invoked
         * @param object $obj Method is belong to this object instance.
         */
        // public function on(string $eventName, string $method, object $obj);
    }
    
    public function onPrivateMessage(PrivateMessageEvent $event): void {
        if ($event->message === "Hello") {
            $this->handler->preventAi(); // prevent Default AI to reply message
            $event->reply("Hello World"); // instead of your message
        }
    }
    
    public function onUnload(): void {
        $this->handler->log(self::TAG, "Unload");
    }
}
```

#### 3.4.5 More events

Includes `groupmessage`, `discussmessage`, `firendadd` etc. Please find in directory `pluginBase/php/event`.

### 3.5 Test

#### 3.5.1 eventUtil

```python
def genUser(userId: int, nickname: str, sex: str="female", age: int=0) -> User: ...;

def genPrivateMessage(user: User, message: str, _from: str="friend") -> Event: ...;
    """
    _from
    	You can give a "friend" or "group" here
    """
def genGroupMessage(user: User, groupId: int, message: str) -> Event: ...;
```

Some useful functions are listed here. Maybe more later.

#### 3.5.2 testUtil

When you write a test file, you should use the testUtil. In your test file, every test function like `test` below is called a case, a test file can has many cases.

```python
####### Please don't modify following ########
import sys, os
sys.path.append(os.path.abspath(os.path.dirname(__file__) + "/.."))
import testUtil.testUtil as testUtil
import testUtil.eventUtil as eventUtil
##############################################

@testUtil.test("<Please add a description for this test here>")
def test():
    # you can do something here like this
    user = eventUtil.genUser(123456789, "user")
    # don't forgot to return a `Event` object
    return eventUtil.genPrivateMessage(user, "Hello")

def main():
    # Please add your test here
    testUtil.run([test])
```

#### 3.5.3 Run your Test

```bash
python devtool.py test <testName> [...<more TestName>]
```