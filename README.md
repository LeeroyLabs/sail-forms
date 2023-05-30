# Forms module for SailCMS

This is the official form package for SailCMS. You will be able to create and manage your forms with this module.

## Installing

```bash
php sail install:official leeroy/sail-forms
```

This will install the package using composer and then update your composer file to autoload the package.

If you wish to install it manually, you and perform the following

```bash
composer require leeroy/sail-forms
```

After that, you can add `Leeroy\\Forms` to the modules section of the sailcms property of your composer.json file. It should look something like this:

```json
"sailcms": {
  "containers": ["Spec"],
  "modules": [
    "Leeroy\\Forms"
  ],
  "search": {}
}
```

## Configuration

[Insert configuration]