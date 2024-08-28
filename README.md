## Overview
![CI](https://github.com/christopherarter/claire-demo/actions/workflows/ci.yml/badge.svg)

This (hopefully) follows the requirements of the assesment.

- Injects main business logic via Actions class.
- Stubs http requests made natively.

### Testing

To test, run:
```bash
php artisan test
```

### Static Analaysis:

You can run static analysis with:
```bash
composer analyze
```

This will run LaraStan on level 5.

### Formatting

You can format the code with:
```bash
composer pint
```