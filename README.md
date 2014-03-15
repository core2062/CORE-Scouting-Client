#CORE Scouting Database Client

---------

The client for the CORE Scouting Database and interacts with the CORE Scouting Database Server to provide a useful UI for submitting and viewing scouting data. It is purely browser-based (written in languages that compile into JS, HTML, and CSS) and because of it's separation from the server, it can be easily modified by teams to provide new functionality that their scouting systems may require.

To compile the client, `cd` into the directory that the client is in and then use [roots](http://roots.cx) to compile it, first use your package manager to install nodejs and npm:
```bash
# For example, these are the commands for Ubuntu
$ sudo apt-get install nodejs npm
```
And then use 
```bash
$ sudo npm install -g roots
$ cd web/CORE-Scouting-Client
$ roots watch
```
*you don't need to install npm or roots if you already have them and you should `cd` into whatever dir you have the repo in... this is just an example*

This will open the client on [http://localhost:1111](http://localhost:1111)