#!/usr/bin/python2.7
# coding=utf-8
import os
import time
import sys
import sys, getopt


def remove_old_files(path, during=7*86400):
    """
    删除指定时间范围内的文件
    """
    now = time.time()

    for f in os.listdir(path):
        f = os.path.join(path, f)
        if os.stat(f).st_mtime < now - during:
           if os.path.isfile(f):
              os.remove(f)

def main(argv):
    target_dir = ''
    during = 7*86400
    try:
        opts, args = getopt.getopt(argv,"ht:d:",["target=","during="])
    except getopt.GetoptError:
        usage()
        sys.exit(2)

    for opt, arg in opts:
        if opt == '-h':
            usage()
            sys.exit()
        elif opt in ("-t", "--target"):
            target_dir = arg
        elif opt in ("-d", "--during"):
            during = int(arg)

    if target_dir == '':
        usage()
        sys.exit()
    else:
        remove_old_files(target_dir, during)

def usage():
    print 'Usage:remove_old_files.py -t <target_dir> -d <during_time> '

if __name__ == "__main__":
    if len(sys.argv) < 3:
        usage()
    else:
        main(sys.argv[1:])
