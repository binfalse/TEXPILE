# TEXPILE

TEXPILE is an online compiler for Latex projects. The idea is that you jsut need to throw your tex project against a web interface and you'll get back the compiled PDF document.





## ORGANISATIONAL

* TEXPILE's code is hosted at GitHub: [github.com/binfalse/TEXPILE](https://github.com/binfalse/TEXPILE)
* TEXPILE is available as a Docker container from the Docker Hub: [hub.docker.com/r/binfalse/texpile/](https://hub.docker.com/r/binfalse/texpile/)
* Issues and feature requests are tracked on GitHub: [github.com/binfalse/TEXPILE/issues](https://github.com/binfalse/TEXPILE/issues) -- [create new issue](https://github.com/binfalse/TEXPILE/issues/new)





## DEPLOY

Thanks to Docker it is very easy to run TEXPILE. You can pull and run the image with a single command:

    docker run -it --rm -p 1234:80 binfalse/texpile

This will download the lates version of TEXPILE's image and run it on your machine.
TEXPILE's webserver will bind to your machine's port `1234` so you can access it with a web browser at http://localhost:1234

If you want to monitor TEXPILE you can send an HTTP GET request to http://localhost:1234/heartbeat and expect an `HTTP/1.1 200 OK` together with the content `up and running!` as a response.
For example:

    curl -v http://localhost:1234/heartbeat
    *   Trying ::1...
    * Connected to localhost (::1) port 1234 (#0)
    > GET /heartbeat HTTP/1.1
    > Host: localhost:1234
    > User-Agent: curl/7.47.0
    > Accept: */*
    >
    < HTTP/1.1 200 OK
    < Date: Tue, 05 Jul 2016 16:01:12 GMT
    < Server: Apache/2.4.10 (Debian) PHP/7.0.7
    < X-Powered-By: PHP/7.0.7
    < Content-Length: 16
    < Content-Type: text/html; charset=UTF-8
    <
    up and running!

If you're using Nagios' NRPE service you may find the following snippet useful to setup monitoring for TEXPILE:

    command[check_texpile]=/usr/lib/nagios/plugins/check_http -I 127.0.0.1 -p 1234 -u /heartbeat

TEXPILE can of course be setup to listen on the host's public IP address. Let's assume the host's IP address is `85.214.59.220` and let's use port `7777` in this example, then just start the container using the following command line:

    docker run -it --rm -p 85.214.59.220:7777:80 binfalse/texpile

You should then be able to connect to the tool at `http://85.214.59.220:7777/` (of course you may also use your server's domain name).





## EXAMPLE

As soon as the container is up and running you may point the browser to it and you should see a simple usage page.



## Compiling a standalone LaTeX file
To actually compile a single LaTeX file with the help of TEXPILE you can just send it using HTTP POST. The file is expected in the form-field parameter `project`. A simple commandline call with `cURL` would thus look like:

    curl -F project=@example.tex http://localhost:1234 > /tmp/exmaple.pdf

Here, the LaTeX file `example.tex` will be sent to TEXPILE running at `http://localhost:1234`. The resulting PDF will be written to `/tmp/exmaple.pdf`. This is obviously only successfull if `example.tex` is really standalone and doesn't require any images or bibliography files.

In the [example directory](example/) you'll find more examples for different programming languages:

* [Bash snippet using cURL](example/snippet.sh)
* [PHP snippet using cURL](example/snippet.php)

All examples will compile the [file `example/example.tex`](example/example.tex) using TEXPILE.



## Compiling a complex LaTeX project
To compile a LaTeX project that consists of multiple files you first need to bundle all necessary files in a ZIP archive. Then you can ship the ZIP container to TEXPILE, which expects the ZIP content as the form-field parameter `project` and the LaTeX root document as the `filename` parameter. Using cURL a commandline call may look like:

    zip -r /tmp/zipfile.zip *
    curl -F project=@/tmp/zipfile.zip -F filename=example.tex http://localhost:1234 > /tmp/pdffile.pdf

This call will create a ZIP file in `/tmp/zipfile.zip` of the current directory. The ZIP container will then be sent to TEXPILE listening at `http://localhost:1234`. Additionally, the call tells TEXPILE that the file `example.tex` of the ZIP container is suppossed to be the root document of the project. The resulting PDF document will be stored as `/tmp/pdffile.pdf`.

In the [example directory](example/) you'll find more examples for different programming languages:

* [Bash snippet using cURL](example/snippet.sh)
* [PHP snippet using cURL](example/snippet.php)

All examples will compile the [project in `example/example-multidoc/`](example/example-multidoc/) using TEXPILE.




## Compilation Problems
Of course, it may happen that TEXPILE isn't able to compile a certain document or project. In that case, it will return an HTTP status code other than the usual `HTTP/1.1 200 OK` (e.g. `HTTP/1.1 400 Bad Request` if you forgot to send the `filename` of root document in you zipp'ed project, or `HTTP/1.1 500 Internal Server Error` if something's wrong on the server side).

In addition, TEXPILE will return an HTML page giving more information on the error together with the output of the `pdflatex` tool, if available.

Give it a try and send some non-tex documents to TEXPILE to see it complaining :)




## CONTRIBUTE!

TEXPILE is free software and I am always super-happy when people contribute to open tools! Thus, go ahead and

* **Send comments, issues, and feature requests** by [creating a new ticket](https://github.com/binfalse/TEXPILE/issues/new)
* **Spread the tool:** Tell your friends, share your thoughts in your blog, etc.
* **Propose new modifications:** fork the repo -- add your changes -- send a pull request

No matter if it's actual code extensions, more examples, bug fixes, typo corrections, artwork/icons: **Everything is welcome!!**





## LICENSE

TEXPILE is licensed under the terms of the **GNU General Public License version 3**.
For more information please consult the [LICENSE file](LICENSE).
