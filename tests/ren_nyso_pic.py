# coding=utf8
# the above tag defines encoding for this document and is for Python 2.x compatibility
import os
import pdb
import re

regex = r".*?_(\w+)"
source_dir = 'C:/prjs/jcshop/upload/nyso_pics'
for root, sub_dirs, files in os.walk(source_dir):
    for special_file in files:
        spcial_file_dir = os.path.join(root, special_file)
        print spcial_file_dir
        matches = re.search(regex, special_file, re.DOTALL)
        if matches:
            print os.path.join(root, matches.group(1))
            os.rename(spcial_file_dir, os.path.join(root, matches.group(1)))

        # 打开文件的两种方式
        # 1.文件以绝对路径方式
        # os.rename("", "")
        # with open(spcial_file_dir) as source_file:
        # # 2.文件以相对路径方式
        # # with open(r'dir_test/test.txt') as source_file:
        #     for line in source_file:
        #         # do something
        # # 移动文件
        # shutil.move(spcial_file_dir, target_dir)
        # logger.info(u'文件%s移动成功'% spcial_file_dir)

