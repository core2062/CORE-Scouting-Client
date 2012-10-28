#CORE Scouting Database Client

The client for the CORE Scouting Database and interacts with the CORE Scouting Database Server to provide a useful UI for submitting and viewing scouting data. It is purely browser-based (written in languages that compile into JS, HTML, and CSS) and because of it's seperation from the server, it can be easily modified by teams to provide new functionality that their scouting systems may require.

Directory Structure:

To compile the client, `cd` into the directory that the client is in and then use nodefront to compile it:
```bash
$ sudo apt-get update
$ sudo apt-get install nodejs npm
$ npm install -g nodefront
$ cd web/CORE-Scouting-Client
$ nodefront compile -o static
```
*you don't need to reinstall npm or nodefront if you already have them and you should `cd` into whatever dir you have the repo in... this is just an example*

This will produce a compiled client (one HTML file) at `static/index.html`

NOTE: the client is written with [semantic-jade](https://github.com/2062/semantic-jade) so until everything is renamed in semantic-jade and the `.sj` file extension is used, you will need to install semantic-jade manually (replacing your jade installation), if you want to compile the client on your own.