In this course you will see:
- custom user login/logout/profile

- Products:
  - CRUD Products with form builder
  - fields validation
  - soft delete
  - vich uploader
  - datatable plugin
  - EntityLifeCycle for auto generate createdAt and updatedAt values

- Users:
  - CRUD Users without form builder
  - fields validation
  - soft delete
  - vich uploader
  - custom table with pagination
  - EntityLifeCycle for auto generate createdAt and updatedAt values

- home page
- fix user roles authorization in controllers and views and listenerException
- webpack encore
- fixtures and fakers
- profiler for debugging
- entity relationships
- password hashing

# Used Commands:
- composer require symfony/maker-bundle --dev
- composer require symfony/profiler-pack --dev
- composer require symfony/debug-bundle --dev
- composer require symfony/orm-pack
- composer require symfony/security-bundle
- composer require twig
- composer require doctrine/doctrine-fixtures-bundle --dev
- composer require fzaninotto/faker --dev
- composer require symfony/asset
- composer require symfony/webpack-encore-bundle
- composer require vich/uploader-bundle
- composer require omines/datatables-bundle
- composer require nelmio/cors-bundle
- composer require symfony/expression-language

## authentication management:
- php bin/console make:auth
- php bin/console make:user
- php bin/console make:security:form-login

## database management:
- php bin/console doctrine:database:drop --force
- php bin/console doctrine:database:create
- php bin/console make:entity --regenerate
- php bin/console make:migration
- php bin/console doctrine:migrations:migrate
- php bin/console make:fixture
- php bin/console doctrine:fixtures:load

## webpack:
- npm install bootstrap @popperjs/core
- npm install sass-loader@^16.0.1 sass --save-dev

## other commands:
- php bin/console make:controller
- php bin/console make:validator
- php bin/console make:listener

- php bin/console cache:clear
- php bin/console debug:route

- php bin/console assets:install


# References:
https://medium.com/@rcsofttech85/implementing-soft-delete-in-symfony-with-doctrine-d3dddf3a38af

https://stackoverflow.com/questions/65971771/javascript-files-imported-by-webpack-encore-doesnt-work

https://medium.com/@andrii.sukhoi/automatic-values-for-created-at-updated-at-in-doctrine-symfony-6-fb625f17478

https://omines.github.io/datatables-bundle
