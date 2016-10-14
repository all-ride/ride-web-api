# Ride: API Web Integration

API browser for a Ride web application.

To activate, add the routes from _config/routes.api.json_ to your configuration.

For example, in _application/config/routes.json_, you can set:

```json
{
    "routes": [
        {
            "path": "/admin/documentation/api",
            "file": "config/routes.api.json"
        },
    ]
}
```

## Related Modules

- [ride/app](https://github.com/all-ride/ride-app)
- [ride/app-api](https://github.com/all-ride/ride-app-api)
- [ride/lib-api](https://github.com/all-ride/ride-lib-api)
- [ride/lib-http](https://github.com/all-ride/ride-lib-http)
- [ride/web](https://github.com/all-ride/ride-web)
- [ride/web-base](https://github.com/all-ride/ride-web-base)
- [ride/web-documentation](https://github.com/all-ride/ride-web-documentation)

## Installation

You can use [Composer](http://getcomposer.org) to install this application.

```
composer require ride/web-api
```
