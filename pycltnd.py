#!/usr/bin/env python
#author Hito http://www.hitoy.org/

import os,sys,time,post,yahoo,urllib

"""
arguments:
-u:    POST url, needed
-k:   key file name should be a path   defalut  key.txt
-t:   collection and pulish interval seconds       defalut  5 seconds
-c:   Special for get yahoo content, list content  10 ,20 or 30 default 20
-d:    For linux with -d , you can run this progarm as deamon
"""

logfile   = open("./pycltnd.log","a+")
arguments = sys.argv
keyfile= open("./key.txt","r")
count=20
interval = 10
"""
if ( "-u" in arguments ):
    u = arguments.index("-u")+1
    url = arguments[t]
else:
    print "Must Input a Target URL -t url"
    sys.exit()
"""
if ( "-k" in arguments ):
    k = arguments.index("-k")+1
    filename = arguments[k]
    if os.path.exists(filename):
        keyfile.close()
        keyfile = open(filename,"r")

if ( "-t" in arguments ):
    t = arguments.index("-t")+1
    try:
        interval = int(arguments[t])
    except:
        interval = 10

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
    except:
        print "Your System are not support to run as deamon"
    
    if pid:sys.exit()
    os.setsid()
    os.umask(0)
    os.chdir("/")
    os.dup2(logfile.fileno(),0)
    os.dup2(logfile.fileno(),1)
    os.dup2(logfile.fileno(),2)
    os.close(logfile.fileno())
"""main"""
while True:
    try:
        key = keyfile.readline().strip()
        if len(key) == 0: break
        yahoourl="https://search.yahoo.com/search?p=%s&n=%s"%(urllib.quote(key),count)
        YaCo=yahoo.Yahoo(yahoourl)
        post_content= YaCo.filter()
        if ( post_content and len(post_content) > 10 ):
                    result=post.POST("http://www.raymondmill.com/es/post/main.php?action=save&secret=yht123hito",{"post_title":key,"post_content":post_content})
                    sys.stdout.write(("[%s] - %s - %s\n")%(time.ctime(),key,result))
        else:
            sys.stdout.write(("[%s] - %s - %s\n")%(time.ctime(),key,"Collection Failure"))
            
        sys.stdout.flush()
        time.sleep(interval)
    except KeyboardInterrupt,e:
        break
    
#close
keyfile.close()
