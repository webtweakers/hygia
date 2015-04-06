# Hygia

Hygia is a testing framework for web-applications that allows you to configure the end-points through XML configuration files and define not only the input and output parameters, but also validators and plug-ins.

Hygia allows you to configure the end-points of your web application through XML configuration files and define not only the input and output parameters, but also validators and plugins. Hygia can run test unattended and automated and will create reporting files that can be reviewed at any time. It can also send email(s) in case of errors or unexpected responses.

Hygia is written in PHP, but can test any application that is accessible through a network. It can test both front-end and back-end tiers of applications. Hygia basically mimics the behavior of a browser and thus acts as a client application, perfectly fit for testing server applications of any size.

Custom written plugins allow manipulation of the input and output data and provides the ability to construct flows. Validators can verify the response of the server in several ways. Validators exists for schema's, dtd's, connection and transfer times, size validators, hashes, etc.


## Usage

A manual can be found in the hygia/docs/manual directory. It's available in several formats, although - at the moment - not completely finished. Feel free to contribute! ;)

The class documentation can be found in the hygia/docs/api directory. A starting point is hygia/docs/api-documentation.html
The documentation is extracted from the source code with Doxygen.

Install Hygia on a web server running a recent version of PHP and set up a virtual host with the document root pointing to hygia/www. Then you can start an example test suite by going to http://hygia/?mod=examples
