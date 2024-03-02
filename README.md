# Bloggable

The Bloggable repository consists of two major elements.
1. A PHP implementation of a REST API server that supports things like JWTs, Swagger, database access (examples using SQLite, MySQL, and IBM DB2 are included), and all the endpoints needed to implement a multi-user and multi-blog environment.
2. A TMS WEB Core implementation of a REST API client for the above server. This is essentially used to generate the blog website including all the UI for logging in, searching, account management, and so on.

## IMPORTANT
Before we get any further, please note that this repository isn't necessarily configured in the most secure way. Typically, the contents of this repository would be dropped into the root of your website, like where index.html might normally be found. This would mean that all of the underlying JSON, the "keys" and "database" folders, and numerous PHP scripts would be there as well. This is not great. Better to move all of those elsewhere, even just up one level - out of sight of the prying eyes of your web server. The .htaccess file includes rules to block such access if you don't do this, but this assumes that the .htaccess will be properly used by your web server. Which isn't always the case.

## Database
The 'database' folder contains everything needed to create the database in SQLite, MySQL, or IBM DB2 using one of the 'createdb-X' SQL scripts. Additionally, Bash scripts have been added which will run these scripts and produce something similar in terms of output. Here are the tables we're interested in. Note that table names have been defined as all uppercase, and field names have been defined as all lowercase. Just because. Note also that the IBM DB2 connection in PHP sets an option to always return lowercase for field names.

- ACCOUNT
- LOOKUP
- AUTHOR
- AUTHOR_ACCOUNT
- WEBLOG
- WEBLOG_ACCOUNT
- BLOG
- COMMENT
- ACTION
- OPTION
- NOTIFY
- HISTORY
- TOKEN

There's nothing particularly complicated or fancy about the tables or their contents. The idea has been to make things as straightforward as possible, with an idea towards making it as easy to adapt to whatever circumstances might be needed.
- ID values tend to be text columns, likely containing a GUID string or similar. This provides several benefits, but is not necessarily the most performant option.
- One-word table names, with the convention of using TABLE1_TABLE2 to store table relations, being mindful to set up foreign key definitions.
- Fields like 'updated_by', 'updated_at', 'created_by', created_at', etc., applied in some fashion to most of the tables.
- Primary keys (including compound primary keys) have been defined where it makes sense to define them.
- LOOKUP table used to store typical Key:Value sets used in many places, rather than separate tables for each

## Routing
The .htaccess file helps this along, directing traffic mainly for /api (going to routes.php), /docs (going to swagger), and then everything else to index.html (the TMS WEB Core client app), being careful to pass along any query parameters at the same time. The routes.php script is primarily concerned with calling the REST API (found in bloggable.php) along with whatever parameters came along with the URL.

## PHP REST API
The REST API is implemented via bloggable.php with a little help from routes.php. Routes.php is just used to call the endpoints defined in bloggable.php whenever a URL is encountered that starts with /api/. This ensures that the REST API endpoints get sent to the bloggable.php script. 

## Swagger
To test this REST API, Swagger has been setup for this project. Unfortunately, due to issues with the zircote/swagger-php library that I could not resolve, the swagger.json file is generated externally rather than from directly within the bloggable.php script. This means that it doesn't have the same kind of access to things like the JSON configuration used by the script. To get around this, and to make it easier to update the Swagger docs, a separate swagger.php script is provided. This runs the vendor/bin/openapi command with the necessary parameters to generate the swagger.json file that we're after, and then reads in the configuration JSON file that the bloggable.php script uses (bloggable.json) and updates swagger.json as needed. To display the Swagger content, a basic index.html page is provided, normally accessed from the /docs URL, that then refers to the generated swagger.json. This also has a minor tweak from the version on the Swagger UI website to disable the TryItNow buttons. 

## TMS WEB Core
The client app is implemented in TMS WEB Core, a Delphi tool that takes care of transpiling Delphi (Pascal) projects into 100% HTML/CSS/JS code. In this project, we're using the REST API to retrieve everything needed to present a typical blog-style website. The blogs themselves are of course stored in a database that the REST API provides access to. The TMS WEB Core app provides the structure of the page and the rest of the client UI experience for editing and managing blogs, accounts, authors, security and the rest of it.

## Key Dependencies
For the server side, this includes a PHP project that implements a REST API. Documentation is implemented with Swagger, so some dependencies come as a result of that.
- [zircote/swagger-php](https://github.com/zircote/swagger-php) - Swagger PHP library

For the client side, As with any modern web application, other JavaScript libraries/dependencies have been used in this project. Most of the time, this is handled via a CDN link (usually JSDelivr) in the Project.html file. In some cases, for performance or other reasons, they may be included directly.
- [TMS WEB Core](https://www.tmssoftware.com/site/tmswebcore.asp) - This is a TMS WEB Core project, after all
- [Bootstrap](https://getbootstrap.com/) - No introduction needed
- [Tabulator](https://www.tabulator.info) - Fantastic pure JavaScript web data tables
- [Font Awesome](https://www.fontawesome.com) - The very best icons
- [Luxon](https://moment.github.io/luxon/#/) - For handling date/time conversions
- [FlatPickr](https://flatpickr.js.org) - Date picker component
- [PanZoom](https://github.com/timmywil/panzoom) - Used when viewing images for panning and zooming
- [InteractJS](https://interactjs.io/) - Dragging and resizing UI elements of all kinds
- [Simplebar](https://github.com/Grsmto/simplebar) - Used for custom scrollbars
- [D3](https://d3js.org/) - Used for drawing charts
- [FileSaver](https://moment.github.io/luxon/#/?id=luxon) - For downloading files

## Additional Notes
While this project is currently under active development, feel free to give it a try and post any issues you encounter.  Or start a discussion if you would like to help steer the project in a particular direction.  Early days yet, so a good time to have your voice heard.  As the project unfolds, additional resources will be made available, including platform binaries, more documentation, demos, and so on.

## Repository Information 
[![Count Lines of Code](https://github.com/500Foods/Template/actions/workflows/main.yml/badge.svg)](https://github.com/500Foods/Template/actions/workflows/main.yml)
<!--CLOC-START -->
```
Last updated at 2024-03-02 09:16:37 UTC
-------------------------------------------------------------------------------
Language                     files          blank        comment           code
-------------------------------------------------------------------------------
PHP                              7            146            169            491
SQL                              1             82             40            349
Markdown                         1             15              2             77
HTML                             3              0              0             63
JSON                             2              0              0             52
YAML                             2              8             13             35
Bourne Shell                     1              9              8             32
Pascal                           2             10              2             28
Delphi Form                      1              0              0             13
-------------------------------------------------------------------------------
SUM:                            20            270            234           1140
-------------------------------------------------------------------------------
6 Files (without source code) were skipped
```
<!--CLOC-END-->

## Sponsor / Donate / Support
If you find this work interesting, helpful, or valuable, or that it has saved you time, money, or both, please consider directly supporting these efforts financially via [GitHub Sponsors](https://github.com/sponsors/500Foods) or donating via [Buy Me a Pizza](https://www.buymeacoffee.com/andrewsimard500). Also, check out these other [GitHub Repositories](https://github.com/500Foods?tab=repositories&q=&sort=stargazers) that may interest you.

## More TMS WEB Core and TMS XData Content
If you're interested in other TMS WEB Core and TMS XData content, follow along on 𝕏 at [@WebCoreAndMore](https://x.com/WebCoreAndMore), join our 𝕏 [Web Core and More Community](https://twitter.com/i/communities/1683267402384183296), or check out the [TMS Software Blog](https://www.tmssoftware.com/site/blog.asp).
