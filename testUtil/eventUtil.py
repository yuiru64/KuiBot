import typing as typing

User = typing.Dict
Event = typing.Dict

def genUser(userId: int, nickname: str, sex: str="female", age: int=0) -> User:
    return {
        "user_id": userId,
        "nickname": nickname,
        "sex": sex,
        "age": age
    }

def genPrivateMessage(user: User, message: str, _from: str="friend") -> Event:
    return {
        "post_type": "message",
        "message_type": "private",
        "sub_type": _from,
        "user_id": user["user_id"],
        "message": message,
        "sender": user
    }

def genGroupMessage(user: User, groupId: int, message: str) -> Event:
    return {
        "post_type": "message",
        "message_type": "group",
        "sub_type": "normal",
        "group_id": groupId,
        "user_id": user["user_id"],
        "message": message,
        "sender": user
    }

def genTick() -> Event:
    return {
        "post_type": "meta_event",
        "meta_event_type": "heartbeat"
    }