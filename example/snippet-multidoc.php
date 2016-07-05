<?php

# create two variables:
# * one for the zip'ed project
# * one for the produced pdf
$zipfile = tempnam ("/tmp", "TEXPILE");
$pdffile = tempnam ("/tmp", "TEXPILE");

$basepath = realpath("example-multidoc");

# create a zip file of the project
$zip = new ZipArchive();
$zip->open($zipfile, ZIPARCHIVE::CREATE);


$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($basepath),
    RecursiveIteratorIterator::LEAVES_ONLY
);

foreach ($files as $name => $file)
	// directories will be added automatically
	if (!$file->isDir())
	{
		// add file to archive with it's relative path from $basepath
		$filePath = $file->getRealPath();
		$zip->addFile($filePath, substr($filePath, strlen($basepath) + 1));
	}
$zip->close();
/*
		echo $zipfile;
		exit (1);*/

// send the tex document
$post = array (
    'project' => curl_file_create ($zipfile),
    'filename' => 'example.tex'
);

// let's assume the port 1234 on localhost is forwarded into the docker container 
$ch = curl_init ("http://localhost:1234");
curl_setopt ($ch, CURLOPT_POST,1);
curl_setopt ($ch, CURLOPT_POSTFIELDS, $post);
curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);

// execute and get back the result
$result=curl_exec ($ch);
curl_close ($ch);

// echo $result;

// store $result as pdf
file_put_contents ($pdffile, $result);

echo $pdffile;
?>
