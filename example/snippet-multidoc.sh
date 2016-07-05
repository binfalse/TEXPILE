#!/bin/bash

# create 2 temporary file:
# * one for the zip'ed project
# * one for the produced pdf
zipfile=$(mktemp)
pdffile=$(mktemp)

# zip the multidocument pdf
rm $zipfile
pushd example-multidoc
zip -r $zipfile *
popd

# send the zip to TEXPILE and store the output in $pdffile
curl -F project=@$zipfile -F filename=example.tex http://localhost:1234 > $pdffile

# clean up
rm $zipfile

# notify the user
echo "your PDF is in $pdffile"
