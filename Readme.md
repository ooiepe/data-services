# EPE Ocean Data Services

EPE Ocean Data Services provides developers with an easy way to aggregate and access station information and data from a number of popular oceanographic web services.  This service was developed to support the educational visualization tools of the Ocean Observatories Initiative (OOI) as part of its education portal.

This package currently supports the following web services:

* NDBC SOS
* CO-OPS SOS
* NERRS - under development
* USGS - under development
* OOI - under development

These services can be accessed directly from the EPE server (tbd).  In addition, developers can download the source code and install the service on their own server.  If you find this service useful, please let us know.  We appreciate hearing about bugs and feature requests.  If you customize the codebase to include additional features or data sources, plase send us a pull-request.

## Installation

1. Install composer:  `curl -sS https://getcomposer.org/installer | php`
2. Compile composer: `php composer.phar install`
3. Copy `config-default.php` to `config.php` and update with your database and admin user info
4. Load `/util/schema.sql` into your database
5. Point your web browser to the location of your installed files.  You should see the service's homepage.
6. Add /admin to the URL and log in with the username and password you added to the config file.
7. Run through all of the load steps to populate your database

## Time-Series Service

The service includes two major components: 

1. A catalog service that aggregrates station information from the included data providers, allowing developers to query stations of interest using geographic, temporal and other search parameters.
2. A data service that provides a common way to access time-series data from all providers by standardizing the request URLs and response file formats.  In addition, this service also standardizes returned units, when possible (not yet implemented).

Instructions on how to access data from these services can be found by pointing your browser to the root directory of your installation.

## Glider Data Service

Under development

