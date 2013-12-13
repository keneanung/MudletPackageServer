MudletPackageServer
===================

This is a simple server for a mudlet package repository. It is ment to help creating centralized points for the packages, deliver updates and list available packages.

**Currently the server is in early development status!**

System requirements
===================
Web server with php >= 5.2.1
MySql

Installation
============

To install the server, simply put the repository.php into a folder on your server. The server needs a database called "mudlet-repository" and a table called "packages" with fields "name", "version" and "description". The database should provide select privileges to everyone.

Usage
=====
To offer packages over the server, simply create a new dataset with the package name, version and description. Then drop the actual package in the same folder as the repository.php. The name of the file has to be the same as the package name in the database. Multiple Packages with the same name on one server are not supported.
