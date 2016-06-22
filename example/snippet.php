<?php

// send the tex document
$post = array (
    'project' => curl_file_create ('./example.tex')
);

// let's assume the port 1234 on localhost is forwarded into the docker container 
$ch = curl_init ("http://localhost:1234");
curl_setopt ($ch, CURLOPT_POST,1);
curl_setopt ($ch, CURLOPT_POSTFIELDS, $post);
curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);

// execute and get back the result
$result=curl_exec ($ch);
curl_close ($ch);

// store $result as pdf
file_put_contents ('/tmp/exmaple.pdf', $result);

?>
