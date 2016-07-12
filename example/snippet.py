#!/usr/bin/python
import requests

# send the document to TEXPILE
r = requests.post('http://localhost:1234', files={'project': open('example.tex', 'rb')})

# store the results
with open("/tmp/exmaple.pdf", 'wb') as result:
    result.write(r.content)
