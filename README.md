
## Getting Started

### Installation
You can install this package via composer
```
composer require arsoft/module
```
for installing specific version, you can use this example
```
composer require arsoft/module:v1.1.1
```

### Usage

#### initiate module
you can initiate module using this command
```php
// initiate backend module
php artisan armodule:init-backend

// initiate frontend module
php artisan armodule:init-frontend
```
you can use one or all of them, according to your needs

#### configuration
after initiate module, copy this line of code to **config/app.php** inside **providers** array
```php
'providers' => [
  . . .
  // for backend module
    App\ModuleBackend\moduleBackendServiceProvider::class
    
  // for frontend module
    App\ModuleFrontend\moduleFrontendServiceProvider::class
  ...
];
```
*NB : you can use one or all of them, based on what module you are using

#### generate backend module
to generate backend module, use this line of code
```php
// generate backend module
php artisan armodule:make-backend ParentModule/ChildModule
```
this command will generate following directory inside **app/ModuleBackend/** directory if success
```
--/ParentModule
----/ChildModule
------/Controllers
------/Models
------/Providers
------/Routes
```

#### generate frontend module
to generate frontend module, use this line of code
```php
// generate backend module
php artisan armodule:make-frontend ParentModule/ChildModule
```
this command will generate following directory inside **app/ModuleFrontend/** directory if success
```
--/ParentModule
----/ChildModule
------/Providers
------/Routes
------/Views
```
