####### Please don't modify following #############################
import sys, os
sys.path.append(os.path.abspath(os.path.dirname(__file__) + "/.."))
###################################################################

import testUtil.testUtil as testUtil
import testUtil.eventUtil as eventUtil

user1 = eventUtil.genUser(111, "111")
user2 = eventUtil.genUser(222, "222")
groupId = 123456789

@testUtil.test("Game Start")
def start(dispatch):
    dispatch(
        eventUtil.genGroupMessage(user2, groupId, "# 创建你画我猜"),
        eventUtil.genGroupMessage(user1, groupId, "# 加入你画我猜"),
        eventUtil.genGroupMessage(user2, groupId, "# 加入你画我猜"),
        eventUtil.genGroupMessage(user2, groupId, "# 开始你画我猜"),
    )

@testUtil.test("Game run")
def tick(dispatch):
    dispatch(eventUtil.genTick())

@testUtil.test("Game send")
def send(dispatch):
    dispatch(eventUtil.genPrivateMessage(user1, "apple"))

def main():
    testUtil.run([
        tick
    ])