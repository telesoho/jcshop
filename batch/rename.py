'''
Rename
'''
import os

DIRNAME = "/ucldata/ftp_data/upload"

for filename in os.listdir(DIRNAME):
    fname = os.path.join(DIRNAME, filename)
    if os.path.isdir(fname):
        for pic_filename in os.listdir(fname):
            if pic_filename.find("%") >= 0:
                old_name = os.path.join(fname, pic_filename)
                new_name = os.path.join(fname, pic_filename.replace("%", "_P_"))
                os.rename(old_name, new_name)
                print new_name

