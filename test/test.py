####### Please don't modify following ########
import sys, os
sys.path.append(os.path.abspath(os.path.dirname(__file__) + "/.."))
##############################################

import testUtil.testUtil as testUtil
import testUtil.eventUtil as eventUtil

@testUtil.test("第一次测试")
def test1(dispatch):
    user = eventUtil.genUser(123456789, "Kwai")
    dispatch(eventUtil.genPrivateMessage(user, "去欣园"))

def main():
    testUtil.run([test1])