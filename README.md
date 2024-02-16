# Bloggable

The Bloggable repository contains two components.
1. A PHP implementation of a REST API server that supports things like JWTs, Swagger, database access (SQLite by default) and all the ednpoints needed to implement a multi-user multi-blog configuration.
2. A TMS WEB Core implementation of a client. This is essentially a Delphi client that is used to generate the blog website including all the UI for logging in, searching, account management, and so on.

# Database
In this project, the underlying database uses SQLite. In a production environment with a lot of users, naturally this would likely not be the most robust option. It was chosen here primarily as it is one of the simplest options, where it can be created and used directly without having to install or otherwise manage the database. The 'database' folder contains the 'createdb-sqlite.sql' script, which includes all the DDL and initial INSERT statements to get this up and running with a minimal amount of effort. Other databases may be used of course. The tables we're interest in using for this project include the following.
- AUTHORS
- WEBBLOGS
- BLOGS
- ANALYTICS
- NOTIFICATIONS
- SETTINGS
- BLOGS
- ACCOUNTS
- WEBLOG_ACCOUNTS
- ACCOUNT_AUTHORS

There's nothing particularly complicated or fancy about the tables or their contents. The idea has been to make things as straightforward as possible, with an idea towards making it as easy to adapt to whatever circumstances might be needed.
- GUID columns for any kind of ID values, meaning that we can insert new records without having to do any kind of autoincrement step.
- One-word table names, with the convention of using TABLE1_TABLE2 to store table relations, being mindful to setup foreign key definitions.
- Fields like 'modified_by', 'modified_at', 'created_by', and 'created_at' applied in some fashion to most of the tables.
- Primary keys (including compound primary keys) defined where it makes sense to define them.

## Key Dependencies
For the server side, this includes a PHP project that implements a REST API. Documentation is implemented with Swagger, so there are some dependencies that come as a result of that.
- [zircote/swagger-php](https://github.com/zircote/swagger-php) - Swagger PHP library

For the client side, As with any modern web application, other JavaScript libraries/dependencies have been used in this project. Most of the time, this is handled via a CDN link (usually JSDelivr) in the Project.html file. In some cases, for performance or other reasons, they may be included directly.
- [TMS WEB Core](https://www.tmssoftware.com/site/tmswebcore.asp) - This is a TMS WEB Core project, after all
- [Bootstrap](https://getbootstrap.com/) - No introduction needed
- [Tabulator](https://www.tabulator.info) - Fantastic pure JavaScript web data tables
- [Font Awesome](https://www.fontawesome.com) - The very best icons
- [Luxon](https://moment.github.io/luxon/#/) - For handling date/time conversions
- [FlatPickr](https://flatpickr.js.org) - Main UI date pickers
- [PanZoom](https://github.com/timmywil/panzoom) - Used when viewing photos/posters/backgrounds
- [InteractJS](https://interactjs.io/) - Dragging and resizing UI elements of all kinds
- [Simplebar](https://github.com/Grsmto/simplebar) - Used to create the custom hexagonal scrollbars
- [D3](https://d3js.org/) - Used here to draw audio waveforms
- [FileSaver](https://moment.github.io/luxon/#/?id=luxon) - For downloading HexaGong projects

## Additional Notes
While this project is currently under active development, feel free to give it a try and post any issues you encounter.  Or start a discussion if you would like to help steer the project in a particular direction.  Early days yet, so a good time to have your voice heard.  As the project unfolds, additional resources will be made available, including platform binaries, more documentation, demos, and so on.

## Repository Information 
[![Count Lines of Code](https://github.com/500Foods/Template/actions/workflows/main.yml/badge.svg)](https://github.com/500Foods/Template/actions/workflows/main.yml)
<!--CLOC-START -->
```
Last updated at 2024-02-16 22:39:00 UTC
-------------------------------------------------------------------------------
Language                     files          blank        comment           code
-------------------------------------------------------------------------------
SQL                              1             39             40            176
PHP                              2             28             45             78
Markdown                         1              9              2             61
YAML                             2              8             13             35
HTML                             2              0              0             32
Pascal                           2             10              2             28
Delphi Form                      1              0              0             13
-------------------------------------------------------------------------------
SUM:                            11             94            102            423
-------------------------------------------------------------------------------
5 Files (without source code) were skipped
```
<!--CLOC-END-->

## Sponsor / Donate / Support
If you find this work interesting, helpful, or valuable, or that it has saved you time, money, or both, please consider directly supporting these efforts financially via [GitHub Sponsors](https://github.com/sponsors/500Foods) or donating via [Buy Me a Pizza](https://www.buymeacoffee.com/andrewsimard500). Also, check out these other [GitHub Repositories](https://github.com/500Foods?tab=repositories&q=&sort=stargazers) that may interest you.

## More TMS WEB Core and TMS XData Content
If you're interested in other TMS WEB Core and TMS XData content, follow along on 𝕏 at [@WebCoreAndMore](https://x.com/WebCoreAndMore), join our 𝕏 [Web Core and More Community](https://twitter.com/i/communities/1683267402384183296), or check out the [TMS Software Blog](https://www.tmssoftware.com/site/blog.asp).
