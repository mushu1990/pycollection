#!/usr/bin/env python
import urllib2,urllib

def POST(url,data):
    formdata=urllib.urlencode(data)
    f = urllib2.urlopen(url, formdata)
    content = f.read()
    return content


if __name__ == "__main__":
    print "Can not be excute"
