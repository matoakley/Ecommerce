# Upgrading to v1.1.5

- Remove email templates from application/views/templates/emails/*.html and move template to application/views/templates/email_default.html. Other email templates now come from ecommerce module unless they need to be overridden.

# Upgrading to v1.1.4 is quite a hefty task

- Run SQLs
- Run SKU update script from tools controller
- Delete views/tools as this is now generic
- Add google product feed category to config
- Check that email templates are not linking directly to products, they should be using the cached product_name in the Sales Order Item
- Check bootstrap file against Tilley and Green as the base_url and errors settings must be set