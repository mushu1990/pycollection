#!/usr/bin/env python
from collection import Collection
import re
class Yahoo(Collection):
        
        def filter(self,ttag="h2",ctag="p"):
                
                if len(self.content) <= 0 : return
                
                pas = re.compile(r"<div class=\"res\">([\s\S]*?)<\/div><\/li>",re.I|re.M)
                h3 = re.compile(r"<h3>([\s\S]*?)<\/h3>",re.I|re.M)
                abstr = re.compile(r"<div\sclass=\"abstr\"[^>]*>([\s\S]*?)<\/div>",re.I|re.M)
                f = pas.findall(self.content)
                artice = ""
                for i in f:
                        title = h3.search(i).group(1)
                        content = abstr.search(i).group(1)
                        title = "<"+ttag+">"+re.sub('<[^>]+>','',title)+"</"+ttag+">\n"
                        content = "<"+ctag+">"+re.sub('<[^>]+>','',content)+"</"+ctag+">\n"
                        artice = artice + title + content
                if ( len(artice) < 5 or artice == "None"):
                        return False
                else:
                        return artice
                        
if __name__ == "__main__":
        print "Can not be excute!"
