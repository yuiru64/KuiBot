class Log:
    RED = 31
    GREEN = 32
    YELLOW = 33
    BLUE = 34
    PURPLE = 35
    CYAN = 36
    WHITE = 37
    CLEAR = 0
    _ascii: bool = True

    @classmethod
    def text(cls, msg: str, color: int):
        if cls._ascii:
            msg = "\033[{}m{}".format(color, msg)
        return msg

    @classmethod
    def print(cls, tag: str, msg: str, color: int):
        if cls._ascii:
            msg = "\033[{}m[{}] {}".format(color, tag, msg) + Log.text("", Log.CLEAR)
        print(msg)

    @classmethod
    def log(cls, tag: str, msg: str):
        Log.print("Log/" + tag, msg, cls.BLUE)

    @classmethod
    def warning(cls, tag: str, msg: str):
        Log.print("Warning/" + tag, msg, cls.YELLOW)

    @classmethod
    def error(cls, tag: str, msg: str):
        Log.print("Error/" + tag, msg, cls.RED)

    @classmethod
    def debug(cls, tag: str, msg: str):
        Log.print("Debug/" + tag, msg, cls.CYAN)
