# Upgrading to v1.2.4

- Add AACL Submodule (Important)
- Remove sitemap module and add as git submodule from Matt's GitHub
- (If Sonic) Point index.php to new system location.

# Upgrading to v1.2.2

- It is important that you move the Admin catchall route from /modules/ecommerce/init.php into application/bootstrap.php, above the master catchall route. You'll find the admin catchall route commented out in the init.php file, please don't delete it. For an example of where to put it, see the /application/bootstrap.php file of Paddy & Scott's.

# Upgrading to v1.1.7

- Stockists
- Add cron trigger 'php /path/to/install/index.php --uri=/cron/index' every minute
- Geocoded addresses are now available. Add a cloudmade API key in config and enable 'geocoded_addresses' module.

# Upgrading to v1.1.5

- Remove email templates from application/views/templates/emails/*.html and move template to application/views/templates/email_default.html. Other email templates now come from ecommerce module unless they need to be overridden.

# Upgrading to v1.1.4 is quite a hefty task

- Run SQLs
- Run SKU update script from tools controller
- Delete views/tools as this is now generic and controller/tools.php unless there is any site specific code.
- Add google product feed category to config
- Check bootstrap file against Tilley and Green as the base_url and errors settings must be set