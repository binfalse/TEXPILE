#!/usr/bin/python
import requests
import os
import zipfile
import tempfile

# first we need to zip the project
def zipdir(path, z):
    for root, dirs, files in os.walk(path):
        for file in files:
            filename = os.path.join(root, file)
            z.write(filename, filename.replace ("example-multidoc/",""))

tmp = tempfile.NamedTemporaryFile(delete=False)
z = zipfile.ZipFile(tmp, 'w', zipfile.ZIP_DEFLATED)
zipdir('example-multidoc/', z)
z.close()


# send the project to TEXPILE
r = requests.post('http://localhost:1234', files={'project': open(tmp.name, 'rb')}, data={"filename": "example.tex"})

# clean up
os.unlink (tmp.name)

# store the results
with open("/tmp/example.pdf", 'wb') as result:
    result.write(r.content)
