<?php
# Copyright 2016  Martin Scharm
#
# This file is part of TEXPILE.
# <https://github.com/binfalse/TEXPILE>
#
# TEXPILE is free software: you can redistribute it and/or modify it
# under the terms of the GNU General Public License as published by the Free
# Software Foundation, either version 3 of the License, or (at your option) any
# later version.
#
# TEXPILE is distributed in the hope that it will be useful, but WITHOUT
# ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
# FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

# You should have received a copy of the GNU General Public License along with
# TEXPILE. If not, see <http://www.gnu.org/licenses/>.

$LATEX = "/usr/bin/pdflatex";
$BIBTEX = "/usr/bin/bibtex";



function error ($msg, $errcode = "HTTP/1.1 400 Bad Request")
{
	header($errcode);
    echo "<!DOCTYPE html><html><head><title>TEXPILE</title><style>.error {color: red;}</style></head><body>
<h1>TEXPILE</h1>
<h2>Error</h2><div class='error'>" . $msg . "</div>
<h2>Usage</h2>
Send a <code>multipart/form-data</code> request to this webserver. The folloing fields are recognized:
<dl>
<dt><code>project</code> <strong>(required)</strong></dt>
<dd>Either a single tex-file to be compiled, or a zip file containing a whole tex project including all necessary files for pdflatex.</dd>
<dt><code>filename</code> (required if `project` is a zip)</dt>
<dd>If `project` is a zip container `filename` points to the file containing the root document of the project.</dd>
</dl>
<h3>Examples</h3>
<h4>Using cURL</h4>
<pre>
curl -F project=@example.tex http://localhost:1234 > example.pdf
</pre>
<h4>Using PHP and cURL</h4>
<pre>
post = array (
    'project' => curl_file_create ('./example.tex')
);

\$ch = curl_init('http://localhost:1234');
curl_setopt(\$ch, CURLOPT_POST, 1);
curl_setopt(\$ch, CURLOPT_POSTFIELDS, \$post);

\$result = curl_exec (\$ch);
curl_close (\$ch);
</pre>
<h2>More</h2>
<ul>
<li>There is more information available at <a href='https://github.com/binfalse/TEXPILE'>the GitHub repository of TEXPILE.</a></li>
<li>TEXPILE is available as a Docker container, visit <a href='https://hub.docker.com/r/binfalse/texpile/'>the TEXPILE repository at the Docker Hub.</a></li>
</ul>
</body></html>";
exit;
}

function executeCmd ($cmd, &$output)
{
	$output[] = "executing: " . $cmd;
	exec ($cmd, $out, $result);
	foreach ($out as $o)
		$output[] = $o;
	return $result;
}


# heartbeat for monitoring purposes
if (isset ($_SERVER['REQUEST_URI']) && strpos ($_SERVER['REQUEST_URI'], "heartbeat") !== false)
{
    echo "up and running!\n";
    exit;
}


# did they send a document?
if (!isset ($_FILES["project"])  || empty ($_FILES["project"]))
	error ("Did not receive a Tex project.");



$latex = "/usr/bin/pdflatex";


$tmptex = tempnam ("/tmp", "TEXPILE");
while (file_exists ($tmptex.".tex") || file_exists ($tmptex.".pdf") || file_exists ($tmptex.".zip"))
    $tmptex = tempnam("/tmp", "TEXPILE");

$path_parts = pathinfo ($tmptex);
$in = $path_parts['filename'] . ".tex";
$aux = $path_parts['filename'] . ".aux";
$out = $path_parts['filename'] . ".pdf";
chdir ($path_parts['dirname']);



if (!move_uploaded_file($_FILES["project"]["tmp_name"], $in))
	error ("Sorry, there was an error uploading your project: " . $_FILES["project"]["tmp_name"]);


// is that a zip container
$zipArchive = new ZipArchive(); 
$result = $zipArchive->open($in);
if ($result === TRUE)
{
	if (!isset ($_POST["filename"]) || empty ($_POST["filename"]))
		error ("Looks like you provided a ZIP archive of your project, but you didn't tell me which file contains the root element. Please use the `filename` field!");
	
	$filename = $_POST["filename"];
	while (substr ($filename, 0, 1) == '/')
		$filename = substr ($filename, 1);
	
	if (!$zipArchive->getFromName ($filename))
		error ("Your ZIP container doesn't contain a file named " . $filename . "!?");
	
	$dir = $tmptex.".zip";
	mkdir ($dir);
	$zipArchive->extractTo ($dir);
	$zipArchive->close ();
	
	$path_parts = pathinfo ($dir . '/' . $filename);
	if ($path_parts['extension'] != "tex")
		error ("Your root file '$filename' doesn't seem to be a tex file. It should end with '.tex'!");
	
	$in = $path_parts['filename'] . ".tex";
	$aux = $path_parts['filename'] . ".aux";
	$out = $path_parts['filename'] . ".pdf";
	chdir ($path_parts['dirname']);
}

if (!file_exists ($in))
	error ("Internal error: Couldn't find the tex file!", "HTTP/1.1 500 Internal Server Error");



$fulloutput = array ();
$result = executeCmd ('/usr/bin/latexmk -bibtex -g -gg -f -cd -ps- -pdf -pdflatex="pdflatex --interaction=nonstopmode -shell-escape %O %S" "' . $in . '"', $fulloutput);


if ($result != 0 || !file_exists ($out))
{
    error ("<pre>return code was $result -- here is the output:\n" . implode ("<br />\n", $fulloutput) . "</pre>");
}
else
{
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="document.pdf"');
    header('Content-Length: ' . filesize($out));
    readfile ($out);
    exit;
}

# cleanup
# function adapted from http://php.net/manual/en/function.rmdir.php#92050
function rm ($dir)
{
	if (!file_exists($dir))
		return true;
	
	if (!is_dir($dir) || is_link($dir))
		return unlink($dir);
	
	foreach (scandir ($dir) as $item)
	{
		if ($item == '.' || $item == '..')
			continue;
		if (!rm ($dir . "/" . $item))
		{
			chmod ($dir . "/" . $item, 0777);
			if (!rm ($dir . "/" . $item))
				return false;
		};
	}
	return rmdir($dir);
}

# get rid of all the temporary files
# TODO: there are some more temporary files
executeCmd ('/usr/bin/latexmk -C "' . $in . '"', $fulloutput);
rm ($tmptex.".tex");
rm ($tmptex.".aux");
rm ($tmptex.".pdf");
rm ($tmptex.".zip");

?>
