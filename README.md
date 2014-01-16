#MyPopup
[![Latest Stable Version](https://poser.pugx.org/stefanotorresi/my-popup/v/stable.png)](https://packagist.org/packages/stefanotorresi/my-popup)
[![Latest Unstable Version](https://poser.pugx.org/stefanotorresi/my-popup/v/unstable.png)](https://packagist.org/packages/stefanotorresi/my-popup)
[![Build Status](https://travis-ci.org/stefanotorresi/MyPopup.png?branch=master)](https://travis-ci.org/stefanotorresi/MyPopup)
[![Coverage Status](https://coveralls.io/repos/stefanotorresi/MyPopup/badge.png?branch=master)](https://coveralls.io/r/stefanotorresi/MyPopup?branch=master)

A very simple Zend Framework 2 module to handle cookie timed popups.

It basically appends a view template to the response (ala [ZendDeveloperTools](//github.com/zendframework/ZendDeveloperTools) toolbar) only once per any given seconds.

The actual javascript popup functionality is left to the user at the moment, but may come in later.

##Installation

Best way is via [Composer](//getcomposer.org): `composer require stefanotorresi/my-popup:0.*`
You can also either clone the repo via Git or download the tarball and unpack it manually (note: didn't actually test this... besides, use Composer!).

Once you have the package, just add the module `MyPopup` to your zf2 `application.config.php`.

##Usage

The module supports two configuration settings:

```php
[
    'MyPopup' => [
        'timeout' => 604800 // default is one week, in seconds
        'template' => 'my-popup/popup' // change this!
    ],
]
```

You will have to take care of registering the template you want to use in the zf2 `ViewManager` see (http://framework.zend.com/manual/2.2/en/modules/zend.view.quick-start.html#configuration).
