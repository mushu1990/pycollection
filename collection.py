#!/usr/bin/env python
import urllib2
import urllib

class Collection:
    __referer = "http://www.google.com/"
    __useragent = "Mozilla/5.0 (Windows NT 6.3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/33.0.1750.149 Safari/537.36"
    
    def __init__(self,url,referer=__referer,ua=__useragent):
        self.__ua=ua
        self.__referer=referer
        if self.__get_content(url):
            self.content=self.__get_content(url)
        else:
            self.content=""

    def __get_content(self,url):
        header={"Accept": "text/plain","Connection":"close","User-Agent":self.__ua,"Referer":self.__referer}
        req = urllib2.Request(url,headers=header)
        try:
            page = urllib2.urlopen(req)
            return page.read()
        except:
            return False
        


if __name__ == "__main__":
    print "This is not a Direct execut program"
