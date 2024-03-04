# Bloggability

The Bloggability repository consists of two major elements.
1. A PHP implementation of a REST API server that supports things like JWTs, Swagger, database access (examples using SQLite, MySQL, and IBM DB2 are included), and all the endpoints needed to implement a multi-user and multi-blog environment.
2. A TMS WEB Core implementation of a REST API client for the above server. This is essentially used to generate the blog website including all the UI for logging in, searching, account management, and so on.

A detailed blog post about this project was first published on the TMS Software Blog, which can be found at https://www.tmssoftware.com/site/blog.asp?post=1189.

## IMPORTANT
Before we get any further, please note that this repository isn't necessarily configured in the most secure way. Typically, the contents of this repository would be dropped into the root of a website, like where index.html might normally be found. This would mean that all of the underlying JSON, the "keys" and "database" folders, and numerous PHP scripts would be there as well. This is not great. Better to move all of those elsewhere, even just up one level - out of sight of the prying eyes of the web server. The .htaccess file includes rules to block such access if these files remain in this location, but this assumes that the contents of .htaccess will be properly applied. Which isn't always the case, particularly if the web server is not Apache, which is what is assumed here. And even with Apache, it is possible that the .htaccess file will be ignored and any secrets found in the JSON files could be exposed.

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

There's nothing particularly complicated or fancy about these tables or their contents. The idea has been to make everything as straightforward as possible, with an eye towards making it as easy to adapt to whatever circumstances might be needed.
- ID values tend to be text columns, likely containing a GUID string or similar. This provides several benefits but is not necessarily the most performant option.
- One-word table names, with the convention of using TABLE1_TABLE2 to store table relations, being mindful to set up foreign key constraints.
- Fields like 'updated_by', 'updated_at', 'created_by', created_at', etc., applied in a similar fashion to most of the tables.
- Primary keys (including compound primary keys) have been defined where it makes sense to define them.
- LOOKUP table used to store typical Key:Value sets used in many places, rather than separate tables for each
- Assuming that all timestamps are stored in the database using UTC. Always. The client can sort out the details.
- Also assuming that UTF-8 is used everywhere.

## Routing
The .htaccess file helps this along, directing traffic mainly for /rss and /api (going to routes.php and onward to the PHP REST API server), /docs (going to Swagger), and then everything else to index.html (the TMS WEB Core client app), being careful to pass along any query parameters at the same time. 

## PHP REST API
The REST API is implemented via bloggability.php with a little help from routes.php. Routes.php is just used to call the endpoints defined in bloggability.php whenever a URL is encountered that starts with /rss- or /api/. This ensures that the REST API endpoints get sent to the bloggability.php script and are then handled accordingly. For testing purposes, everything that can be passed through to bloggability.php can also be passed using the command-line, including logging in and accessing the RSS feeds. This is intended to help make it easier to try out various features at they're built, but could also be used as a way to help with automating various processes should the need arise. There are also a collection of other PHP scripts included to help with various administrative functions, like creating daily JWT secrets, sending notifications of various events, and viewing various bits of data that might be of interest, particularly during initial deployment activities.

## Swagger
To test the PHP REST API, Swagger has been set up for this project. Unfortunately, due to issues with the zircote/swagger-php library that were not immediately resolvable, the swagger.json file is generated externally rather than from directly within the bloggability.php script. This means that it doesn't have the same kind of access to things like the JSON configuration used by the script. To get around this, and to make it easier to update the Swagger docs, a separate swagger.php script is provided. This runs the vendor/bin/openapi command with the necessary parameters to generate the swagger.json file that is needed and then reads in the configuration JSON file that the bloggability.php script uses (bloggability.json) and updates swagger.json as needed. To display the Swagger content, a basic index.html page is provided, normally accessed from the /docs URL, that then refers to the generated swagger.json. This also has a minor tweak from the version on the Swagger UI website to disable the TryItNow buttons. 

## TMS WEB Core
The client app is implemented in TMS WEB Core, a Delphi tool that takes care of transpiling Delphi (Pascal) projects into 100% HTML/CSS/JS code. In this project, we're using the REST API to retrieve everything needed to present a typical blog-style website. The blogs themselves are of course stored in the database that the REST API provides access to. The TMS WEB Core app provides the structure of the page and the rest of the client UI experience for editing and managing blogs, accounts, authors, security, and the rest of it.

## Key Dependencies - Server
For the server side, this includes a PHP project that implements a REST API. Documentation is implemented with Swagger, so some dependencies come as a result of that. For the most part, every effort has been made to reduce the need for additional dependencies.
- [zircote/swagger-php](https://github.com/zircote/swagger-php) - Swagger PHP library
  
While some of these are no longer used, it might be helpful to know what is in the composer library in case something was overlooked in any of the related materials for this project. Symfony in particular is used by the Swagger PHP library mentioned above, but as this isn't used in the same way now, most of the symfony dependencies are no longer required. But if the issues there were resolved, these were all that were needed to have the library generate the Swagger JSON file from within the PHP script itself.
```
$ composer show
doctrine/annotations               2.0.1   Docblock Annotations Parser
doctrine/lexer                     3.0.1   PHP Doctrine Lexer parser library that can be used in Top-Down, Recursive Descent Parsers.
firebase/php-jwt                   v6.10.0 A simple library to encode and decode JSON Web Tokens (JWT) in PHP. Should conform to the current spec.
myclabs/deep-copy                  1.11.1  Create deep copies (clones) of your objects
nikic/php-parser                   v5.0.0  A PHP parser written in PHP
phar-io/manifest                   2.0.3   Component for reading phar.io manifest information from a PHP Archive (PHAR)
phar-io/version                    3.2.1   Library for handling version information and constraints
phpmailer/phpmailer                v6.9.1  PHPMailer is a full-featured email creation and transfer class for PHP
phpunit/php-code-coverage          11.0.0  Library that provides collection, processing, and rendering functionality for PHP code coverage information.
phpunit/php-file-iterator          5.0.0   FilterIterator implementation that filters files based on a list of suffixes.
phpunit/php-invoker                5.0.0   Invoke callables with a timeout
phpunit/php-text-template          4.0.0   Simple template engine.
phpunit/php-timer                  7.0.0   Utility class for timing
phpunit/phpunit                    11.0.3  The PHP Unit Testing framework.
psr/cache                          3.0.0   Common interface for caching libraries
psr/container                      2.0.2   Common Container Interface (PHP FIG PSR-11)
psr/event-dispatcher               1.0.0   Standard interfaces for event handling.
psr/log                            3.0.0   Common interface for logging libraries
psr/simple-cache                   3.0.0   Common interfaces for simple caching
sebastian/cli-parser               3.0.0   Library for parsing CLI options
sebastian/code-unit                3.0.0   Collection of value objects that represent the PHP code units
sebastian/code-unit-reverse-lookup 4.0.0   Looks up which function or method a line of code belongs to
sebastian/comparator               6.0.0   Provides the functionality to compare PHP values for equality
sebastian/complexity               4.0.0   Library for calculating the complexity of PHP code units
sebastian/diff                     6.0.0   Diff implementation
sebastian/environment              7.0.0   Provides functionality to handle HHVM/PHP environments
sebastian/exporter                 6.0.0   Provides the functionality to export PHP variables for visualization
sebastian/global-state             7.0.0   Snapshotting of global state
sebastian/lines-of-code            3.0.0   Library for counting the lines of code in PHP source code
sebastian/object-enumerator        6.0.0   Traverses array structures and object graphs to enumerate all referenced objects
sebastian/object-reflector         4.0.0   Allows reflection of object attributes, including inherited and non-public ones
sebastian/recursion-context        6.0.0   Provides functionality to recursively process PHP variables
sebastian/type                     5.0.0   Collection of value objects that represent the types of the PHP type system
sebastian/version                  5.0.0   Library that helps with managing the version number of Git-hosted PHP projects
symfony/cache                      v7.0.3  Provides extended PSR-6, PSR-16 (and tags) implementations
symfony/cache-contracts            v3.4.0  Generic abstractions related to caching
symfony/config                     v7.0.3  Helps you find, load, combine, autofill and validate configuration values of any kind
symfony/console                    v7.0.3  Eases the creation of beautiful and testable command line interfaces
symfony/dependency-injection       v7.0.3  Allows you to standardize and centralize the way objects are constructed in your application
symfony/deprecation-contracts      v3.4.0  A generic function and convention to trigger deprecation notices
symfony/error-handler              v7.0.3  Provides tools to manage errors and ease debugging PHP code
symfony/event-dispatcher           v7.0.3  Provides tools that allow your application components to communicate with each other by dispatching events and listening to them
symfony/event-dispatcher-contracts v3.4.0  Generic abstractions related to dispatching event
symfony/expression-language        v7.0.3  Provides an engine that can compile and evaluate expressions
symfony/filesystem                 v7.0.3  Provides basic utilities for the filesystem
symfony/finder                     v7.0.0  Finds files and directories via an intuitive fluent interface
symfony/http-foundation            v7.0.3  Defines an object-oriented layer for the HTTP specification
symfony/http-kernel                v7.0.3  Provides a structured process for converting a Request into a Response
symfony/polyfill-ctype             v1.29.0 Symfony polyfill for ctype functions
symfony/polyfill-intl-grapheme     v1.29.0 Symfony polyfill for intl's grapheme_* functions
symfony/polyfill-intl-normalizer   v1.29.0 Symfony polyfill for intl's Normalizer class and related functions
symfony/polyfill-mbstring          v1.29.0 Symfony polyfill for the Mbstring extension
symfony/polyfill-php80             v1.29.0 Symfony polyfill backporting some PHP 8.0+ features to lower PHP versions
symfony/polyfill-php83             v1.29.0 Symfony polyfill backporting some PHP 8.3+ features to lower PHP versions
symfony/service-contracts          v3.4.1  Generic abstractions related to writing services
symfony/string                     v7.0.3  Provides an object-oriented API to strings and deals with bytes, UTF-8 code points and grapheme clusters in a unified way
symfony/var-dumper                 v7.0.3  Provides mechanisms for walking through any arbitrary PHP variable
symfony/var-exporter               v7.0.3  Allows exporting any serializable PHP data structure to plain PHP code
symfony/yaml                       v7.0.3  Loads and dumps YAML files
theseer/tokenizer                  1.2.2   A small library for converting tokenized PHP source code into XML and potentially other formats
zircote/swagger-php                4.7.16  swagger-php - Generate interactive documentation for your RESTful API using phpdoc annotations
```

## Key Dependencies - Client
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
Last updated at 2024-03-04 08:56:15 UTC
-------------------------------------------------------------------------------
Language                     files          blank        comment           code
-------------------------------------------------------------------------------
PHP                              9            430            359           1364
SQL                              3            222            148           1111
Markdown                         1             17              2            146
HTML                             3              0              0             63
JSON                             2              4              0             63
Bourne Shell                     7             14              8             44
YAML                             2              8             13             35
Pascal                           2             10              2             28
Delphi Form                      1              0              0             13
-------------------------------------------------------------------------------
SUM:                            30            705            532           2867
-------------------------------------------------------------------------------
6 Files (without source code) were skipped
```
<!--CLOC-END-->

## Sponsor / Donate / Support
If you find this work interesting, helpful, or valuable, or that it has saved you time, money, or both, please consider directly supporting these efforts financially via [GitHub Sponsors](https://github.com/sponsors/500Foods) or donating via [Buy Me a Pizza](https://www.buymeacoffee.com/andrewsimard500). Also, check out these other [GitHub Repositories](https://github.com/500Foods?tab=repositories&q=&sort=stargazers) that may interest you.

## More TMS WEB Core and TMS XData Content
If you're interested in other TMS WEB Core and TMS XData content, follow along on 𝕏 at [@WebCoreAndMore](https://x.com/WebCoreAndMore), join our 𝕏 [Web Core and More Community](https://twitter.com/i/communities/1683267402384183296), or check out the [TMS Software Blog](https://www.tmssoftware.com/site/blog.asp).
