![Laravel Tablar Crud Generator](https://banners.beyondco.de/Laravel%20Tablar%20Crud%20Generator.png?theme=light&packageManager=composer+require&packageName=takielias%2Ftablar-crud-generator&pattern=topography&style=style_1&description=It%27s+a+simple+CRUD+generator+based+on+Laravel+Tablar+&md=1&showWatermark=0&fontSize=125px&images=https%3A%2F%2Flaravel.com%2Fimg%2Flogomark.min.svg)


[![Latest Version](https://img.shields.io/packagist/v/takielias/tablar-crud-generator?color=blue&label=release&style=for-the-badge)](https://packagist.org/packages/takielias/tablar)
[![Stars](https://img.shields.io/github/stars/takielias/tablar-crud-generator?color=rgb%2806%20189%20248%29&label=stars&style=for-the-badge)](https://packagist.org/packages/takielias/tablar)
[![Total Downloads](https://img.shields.io/packagist/dt/takielias/tablar.svg?color=rgb%28249%20115%2022%29&style=for-the-badge)](https://packagist.org/packages/takielias/tablar)
[![Forks](https://img.shields.io/github/forks/takielias/tablar-crud-generator?color=rgb%28134%20115%2022%29&style=for-the-badge)](https://packagist.org/packages/takielias/tablar)
[![Issues](https://img.shields.io/github/issues/takielias/tablar-crud-generator?color=rgb%28134%20239%20128%29&style=for-the-badge)](https://packagist.org/packages/takielias/tablar)
[![Linkedin](https://img.shields.io/badge/-LinkedIn-black.svg?logo=linkedin&color=rgba(235%2068%2050)&style=for-the-badge)](https://linkedin.com/in/takielias)

<a href="https://www.buymeacoffee.com/takielias" target="_blank"> <img align="left" src="https://cdn.buymeacoffee.com/buttons/v2/default-yellow.png" height="50" width="210" alt="takielias" /></a>

<br/>
<br/>

### Laravel Tablar Admin Dashboard https://github.com/takielias/tablar

**Inspired** by `ibex/crud-generator`

- Will create **Model** with Eloquent relations
- Will create **Controller** with all resources
- Will create **views** in Bootstrap 5.*

```
We have replaced the laravelcollective/html package with konekt/html. This update ensures compatibility with PHP 8.1+ and Laravel 10 & 11, while preserving the same functionality as the original package.
```
## Requirements
    Laravel >= 9.*
    PHP >= 8.1

## Installation
1 - Install
```
composer require takielias/tablar-crud-generator --dev
```
2- Publish the default package's config
```
php artisan vendor:publish --tag=crud
```

## Usage
```
php artisan make:crud {table_name}

php artisan make:crud products
```

Add a route in `web.php`
```
Route::resource('products', 'ProductController');
```
Route name in plural slug case.

#### Options
 - Custom Route
```
php artisan make:crud {table_name} --route={route_name}
```

 - Custom Crud name: the name of the scaffold.
```
php artisan make:crud {table_name} --crud-name={crud_name}

# For example:
# php artisan make:crud emergencies —-crud-name=Emergencies
```

 - Custom language: specify the language that should be used by the inflector (french, norwegian-bokmal, portuguese, spanish or turkish)
```
php artisan make:crud {table_name} --lang={lang}
# For example:
# php artisan make:crud incidencies --lang=spanish
```


## Example

<img width="855" alt="tablar-crud-generator-light" src="https://user-images.githubusercontent.com/38932580/197386382-562d6e3a-055a-42b8-8524-df76f70aa051.png">

![tablar-crud-generator-dark](https://user-images.githubusercontent.com/38932580/197386398-b9541389-5d63-4bcd-87f2-c4aa5d49072d.png)

## Contact

Taki Elias - [@takiele](https://twitter.com/takiele) - [https://ebuz.xyz](https://ebuz.xyz) - taki.elias@gmail.com

<a href="https://www.buymeacoffee.com/takielias" target="_blank">
<img align="left" src="https://cdn.buymeacoffee.com/buttons/v2/default-yellow.png" height="50" width="210" alt="takielias" /></a>

<!-- MARKDOWN LINKS & IMAGES -->
<!-- https://www.markdownguide.org/basic-syntax/#reference-style-links -->

[contributors-shield]: https://img.shields.io/github/contributors/takielias/tablar-crud-generator.svg?style=flat-square

[contributors-url]: https://github.com/takielias/tablar-crud-generator/graphs/contributors

[forks-shield]: https://img.shields.io/github/forks/takielias/tablar-crud-generator.svg?style=flat-square

[forks-url]: https://github.com/takielias/tablar-crud-generator/network/members

[stars-shield]: https://img.shields.io/github/stars/takielias/tablar-crud-generator.svg?style=flat-square

[stars-url]: https://github.com/takielias/tablar-crud-generator/stargazers

[issues-shield]: https://img.shields.io/github/issues/takielias/tablar-crud-generator.svg?style=flat-square

[issues-url]: https://github.com/takielias/tablar-crud-generator/issues

[license-shield]: https://img.shields.io/github/license/takielias/tablar-crud-generator.svg?style=flat-square

[license-url]: https://github.com/takielias/tablar-crud-generator/blob/master/LICENSE.txt

[linkedin-shield]: https://img.shields.io/badge/-LinkedIn-black.svg?style=flat-square&logo=linkedin&colorB=555

[linkedin-url]: https://linkedin.com/in/takielias

[product-screenshot]: images/screenshot.png

[ico-version]: https://img.shields.io/packagist/v/takielias/tablar-crud-generator.svg?style=flat-square

[ico-downloads]: https://img.shields.io/packagist/dt/takielias/tablar-crud-generator.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/takielias/tablar-crud-generator

[link-downloads]: https://packagist.org/packages/takielias/tablar-crud-generator

[link-author]: https://github.com/takielias
