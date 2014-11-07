#/usr/bin/env python
#author Hito http://www.hitoy.org/

import os,sys,time,post,yahoo,urllib

"""
arguments:
-k:   key file name should be a path   defalut  key.txt
-t:   collection and pulish interval seconds       defalut  5 seconds
-c:   Special for get yahoo content, list content  10 ,20 or 30 default 20
-d:    For linux with -d , you can run this progarm as deamon
"""

logfile   = open("./pycltnd.log","a+")
arguments = sys.argv
keyfile="./key.txt"
count=20
interval = 10

if ( "-k" in arguments ):
    k = arguments.index("-k")+1
    filename = arguments[k]
    if os.path.exists(filename):
        keyfile = filename

if ( "-t" in arguments ):
    t = arguments.index("-t")+1
    try:
        interval = int(arguments[t])
    except:
        interval = 10
    print interval

if ( "-c" in arguments ):
    c = arguments.index("-c")+1
    try:
        count = arguments[c]
    except:
        count=20

#run as deamon 
if ( "-d" in arguments ):
    try:
        pid = os.fork()
        #parent
        if pid:exit()
        os.setsid()
        os.umask(0)
        os.chdir("/")
        os.dup2(logfile.fileno(),0)
        os.dup2(logfile.fileno(),1)
        os.dup2(logfile.fileno(),2)
    except:
        print "Your System are not support to run as deamon"





"""main"""
try:
    keyfd=open(keyfile,'r')
except:
    print "Can not open keyfile %s"%keyfile

while True:
    key = keyfd.readline().strip()
    if len(key) == 0: break
    yahoourl="http://search.yahoo.com/search?p=%s&n=%s"%(key,count)
    YaCo=yahoo.Yahoo(yahoourl)
    post.POST("http://localhost/",{"title":key,"post_content":YaCo.filter()})
    time.sleep(interval)
close(keyfile)
close(logfile)

