#CORE Scouting Database Client

The client for the CORE Scouting Database and interacts with the CORE Scouting Database Server to provide a useful UI for submitting and viewing scouting data. It is purely browser-based (written in languages that compile into JS, HTML, and CSS) and because of it's separation from the server, it can be easily modified by teams to provide new functionality that their scouting systems may require.

##Directory Structure:
- **src**: all the source files used to build the client
  - **coffee**: CoffeeScript source (compiles into JS)
  - **stylus**: Stylus source (compiles into CSS)
      - **static**: a symlink to the `./static` directory, because stylus inlines some of the smaller images
  - **svg**: Jade source files which compile into SVG
- **static**: files ready to be served to the browser (including the compiled client)

##Using the Client
A precompiled client is already made for you at `static/index.html`. If you don't want to work on developing the client, you can just delete the entire `src` directory and put the contents of the static directory in you server.

## Compiling The Client On Your Own
If you want to edit the source files for the client, you will need to recompile it into an HTML file.

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