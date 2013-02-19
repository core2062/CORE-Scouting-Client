#CORE Scouting Database Client

---------

The client for the CORE Scouting Database and interacts with the CORE Scouting Database Server to provide a useful UI for submitting and viewing scouting data. It is purely browser-based (written in languages that compile into JS, HTML, and CSS) and because of it's separation from the server, it can be easily modified by teams to provide new functionality that their scouting systems may require.

##Using the Client
A precompiled client is already made for you at `public/index.html`. If you don't want to work on developing the client, you can just delete the entire `src` directory and put the contents of the static directory in you server.

##Compiling The Client On Your Own
If you want to edit the source files for the client, you will need to recompile it into an HTML file.

To compile the client, `cd` into the directory that the client is in and then use [roots](http://roots.cx) to compile it:
```bash
$ sudo apt-get update
$ sudo apt-get install nodejs npm
$ sudo npm install -g roots
$ cd web/CORE-Scouting-Client
$ roots watch
```
*you don't need to install npm or roots if you already have them and you should `cd` into whatever dir you have the repo in... this is just an example*

This will open the client on [http://localhost:1111](http://localhost:1111)