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




function error ($msg)
{
    echo "<!DOCTYPE html><html><head><title>TEXPILE</title><style>.error {color: red;}</style></head><body>
<h1>TEXPILE</h1>
<h2>Error</h2><div class='error'>" . $msg . "</div>
<h2>Usage</h2>
Send a <code>multipart/form-data</code> request to this webserver. The folloing fields are recognized:
<dl>
<dt><code>document</code> <strong>(required)</strong></dt>
<dd>the tex-file to be compiled</dd>
<dt><code>filename</code></dt>
<dd>the file name of the pdf file</dd>
</dl>
<h3>Examples</h3>
<h4>Using cURL</h4>
<pre>
curl -F filename=example.pdf -F document=@example.tex http://localhost:1234 > example.pdf
</pre>
<h4>Using PHP and cURL</h4>
<pre>
post = array (
    'document' => curl_file_create ('./example.tex')
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



# did they send a document?
if (!isset ($_FILES["document"])  || empty ($_FILES["document"]))
{
    error ("there is no document");
}


$latex = "/usr/bin/pdflatex";

$filename = "document.pdf";
if (isset ($_POST["filename"]) || empty ($_POST["filename"]))
$filename = $_POST["filename"];

$tmptex = tempnam("/tmp", "timetracking.tex");
while (file_exists ($tmptex.".tex") || file_exists ($tmptex.".pdf"))
    $tmptex = tempnam("/tmp", "timetracking.tex");

chdir (dirname ($tmptex));

if (!move_uploaded_file($_FILES["document"]["tmp_name"], $tmptex.".tex"))
{
    error ("Sorry, there was an error uploading your file.");
}





exec ($latex . ' -interaction=nonstopmode ' . $tmptex.".tex", $output, $result);

if ($result != 0)
{
    error ("<pre><" . implode ("\n", $output) . "</pre>");
}
else
{
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="'.$filename.'"');
    header('Content-Length: ' . filesize($tmptex.".pdf"));
    readfile ($tmptex.".pdf");
    exit;
}





?>
