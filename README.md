# SuluSnippetManagerBundle
The SuluSnippetManagerBundle adds configurable snippet-based navigation items to the Sulu Admin interface. This allows you to organize snippet management by type and permissions within the native Sulu admin UI.

## Features
- Custom navigation entries for different snippet types
- Optional nested menus (e.g. under a main “Configuration” menu)
- Independent permission handling per menu item

# Installation
## Install the bundle via composer:

```
composer require perspeqtive/sulu-snippet-manager-bundle
```

## Enable the bundle

Register it in your config/bundles.php:

```
return [
// ...
PERSPEQTIVE\SuluSnippetManagerBundle\SuluSnippetManagerBundle::class => ['all' => true],
];
```

# Configuration
Create a configuration file at config/packages/perspeqtive_sulu_snippet_manager.yaml. Here you define how and where your snippet navigation items appear in the Sulu Admin.

Example configuration:
```
sulu_snippet_manager:
    navigation:
        configuration:
            navigation_title: "Configuration" 
            order: 39
            icon: "su-news"
            type: configuration
            children:
                settings:
                    navigation_title: "Settings"
                    type: "settings"
                    order: 0
                    icon: "su-settings"
                account:
                    navigation_title: "Account Settings"
                    type: "account"
                    order: 1
                    icon: "su-account"
        services:
            navigation_title: "Services"
            type: "services"
            order: 41
            icon: "su-services"
```

## Configuration keys explained:

| config item      |         required          | description                                                                                                                               |
|:-----------------|:-------------------------:|:------------------------------------------------------------------------------------------------------------------------------------------|
| navigation_title |            yes            | Label shown in the admin menu                                                                                                             |
| order            |            yes            | Sort order position                                                                                                                       |
| icon             |            no             | Sulu icon name (e.g. su-settings)                                                                                                         |
| type             |            yes            | The sulu snippet type, when it is not a nested parent config entry. When it is a parent entry, it should be a unique identifier           |
| children         |            no             | Nested menu items — parent items with children act as groups without detail views, parents without children behave like normal list views |


## Permissions
Each snippet automatically receives its own permission key. These permissions are independent from the global Snippet permissions in Sulu.

You can assign user roles to control access (view, add, edit, delete) to each snippet separately.

Users without the required permission won’t see the corresponding menu entry in the admin.
