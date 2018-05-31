#!/usr/bin/python2.7
# coding=utf-8
'''
Created on 2016年11月17日
 
@author: wotiger
'''
import shlex
import datetime
import subprocess
import time

TARGET_DIR = "/home/htdoc/jmjwx/html/jcshop2/upload/"


BACKUP_DIR = "/ucldata/backup/"

def execute_command(cmdstring, cwd=None, timeout=None, shell=False):
    """执行一个SHELL命令
       封装了subprocess的Popen方法, 支持超时判断，支持读取stdout和stderr
       参数:
        cwd: 运行命令时更改路径，如果被设定，子进程会直接先更改当前路径到cwd
        timeout: 超时时间，秒，支持小数，精度0.1秒
        shell: 是否通过shell运行
    Returns: return_code
    Raises:  Exception: 执行超时
    """
    if shell:
        cmdstring_list = cmdstring
    else:
        cmdstring_list = shlex.split(cmdstring)
    if timeout:
        end_time = datetime.datetime.now() + datetime.timedelta(seconds=timeout)
    
    #没有指定标准输出和错误输出的管道，因此会打印到屏幕上；
    sub = subprocess.Popen(cmdstring_list, cwd=cwd, stdin=subprocess.PIPE,shell=shell,bufsize=4096)
    
    #subprocess.poll()方法：检查子进程是否结束了，如果结束了，设定并返回码，放在subprocess.returncode变量中 
    while sub.poll() is None:
        time.sleep(0.1)
        if timeout:
            if end_time <= datetime.datetime.now():
                raise Exception("Timeout：%s"%cmdstring)
            
    return str(sub.returncode)
 
if __name__=="__main__":
    date_now = datetime.datetime.now().strftime("%Y%m%d%H%M%S")
    cmd = "7z a {0}upload_backup_{1}.7z {2}".format(BACKUP_DIR, date_now, TARGET_DIR)
    print execute_command(cmd)
