#! /usr/bin/env python  
#coding=utf-8  
## {{{ Recipe 193736 (r1): Clean up a directory tree   
""" removeall.py: 
 
   Clean up a directory tree from root. 
   The directory need not be empty. 
   The starting directory is not deleted. 
   Written by: Anand B Pillai <abpillai@lycos.com> """  
  
import sys, os  
  
ERROR_STR= """Error removing %(path)s, %(error)s """  
  
def rmgeneric(path, __func__):  
  
    try:  
        __func__(path)  
        print 'Removed ', path  
    except OSError, (errno, strerror):  
        print ERROR_STR % {'path' : path, 'error': strerror }  
              
def removeall(path):  
  
    if not os.path.isdir(path):  
        return  
      
    files=os.listdir(path)  
  
    for x in files:  
        fullpath=os.path.join(path, x)  
        if os.path.isfile(fullpath):  
            f=os.remove  
            rmgeneric(fullpath, f)  
        elif os.path.isdir(fullpath):  
            removeall(fullpath)  
            f=os.rmdir  
            rmgeneric(fullpath, f)  
## End of recipe 193736 }}}
def readonly_handler(func, path, execinfo): 
    os.chmod(path, 128) #or os.chmod(path, stat.S_IWRITE) from "stat" module
    func(path)

def overwrite(src, dest):
    if(not os.path.exists(src)):
        print(src, "does not exist, so nothing may be copied.")
        return

    if(os.path.exists(dest)):  
        shutil.rmtree(dest, onerror=readonly_handler)

    shutil.copytree(src, dest)
    print(dest, "overwritten with data from", src)
    print("")

'''
Delete dest directory & copy src direcotry
'''
import os
import shutil
import pdb

SRC_DIR = u"D:\\JMALL\\Share\\05_nyso\\abc"
DEST_DIR = u"D:/JMALL/Share/05_nyso/nyso2jcshop"

for filename in os.listdir(SRC_DIR):
    src_target = os.path.join(SRC_DIR, filename)
    dest_target = os.path.join(DEST_DIR, filename)
    overwrite(src_target, dest_target)
    # if os.path.isdir(src_target):
    #     if os.path.isdir(dest_target):
    #         print dest_target
    #         shutil.rmtree (dest_target)
    #     pdb.set_trace()
    #     shutil.copytree(src_target, dest_target)


