<p align="center">
  <a href="https://roots.io/">
    <img alt="Roots" src="https://cdn.roots.io/app/uploads/logo-roots.svg" height="100">
  </a>
</p>

<p align="center">
  <a href="https://packagist.org/packages/roots/wordpress"><img alt="Packagist Downloads" src="https://img.shields.io/packagist/dt/roots/wordpress?label=downloads&colorB=2b3072&colorA=525ddc&style=flat-square"></a>
  <a href="https://packagist.org/packages/roots/wordpress"><img alt="WordPress Version" src="https://img.shields.io/packagist/v/roots/wordpress.svg?label=wordpress&colorB=2b3072&colorA=525ddc&style=flat-square"></a>
  <a href="https://twitter.com/rootswp"><img alt="Follow Roots" src="https://img.shields.io/badge/follow%20@rootswp-1da1f2?logo=twitter&logoColor=ffffff&message=&style=flat-square"></a>
  <a href="https://github.com/sponsors/roots"><img src="https://img.shields.io/badge/sponsor%20roots-525ddc?logo=github&style=flat-square&logoColor=ffffff&message=" alt="Sponsor Roots"></a>
</p>

<p align="center">Meta-package for installing WordPress via Composer</p>

<p align="center">
  <a href="https://roots.io/composer-wordpress-resources/">Website</a> &nbsp;&nbsp; <a href="https://packagist.org/packages/roots/wordpress">Packages</a> &nbsp;&nbsp; <a href="https://github.com/roots/wordpress/releases">Releases</a> &nbsp;&nbsp; <a href="https://discourse.roots.io/">Community</a>
</p>

## Overview

`roots/wordpress` is a meta-package that provides WordPress core installation via Composer. It's part of the Roots WordPress packaging ecosystem, which includes several packages to give you flexibility in how WordPress is installed:

Package|Description|Content
--|--|--
[`roots/wordpress`](https://github.com/roots/wordpress)|Meta-package (this package)|Installs `roots/wordpress-no-content`
[`roots/wordpress-full`](https://github.com/roots/wordpress-full)|Full WordPress build|✅ Core<br>✅ Official themes<br>✅ Akismet & Hello Dolly<br>✅ Beta & RC releases
[`roots/wordpress-no-content`](https://github.com/roots/wordpress-no-content)|Minimal WordPress build|✅ Core only<br>❌ No themes or plugins<br>❌ No beta releases
[`roots/wordpress-packager`](https://github.com/roots/wordpress-packager)|Build tooling|Creates the package releases

## Support us

We're dedicated to pushing modern WordPress development forward through our open source projects, and we need your support to keep building. You can support our work by purchasing [Radicle](https://roots.io/radicle/), our recommended WordPress stack, or by [sponsoring us on GitHub](https://github.com/sponsors/roots). Every contribution directly helps us create better tools for the WordPress ecosystem.

## Requirements

A [WordPress Core Installer](https://packagist.org/?query=wordpress%20core%20installer&type=composer-plugin) package is required to handle installation path.

> [!tip]
> Roots project provides a core installer, `roots/wordpress-core-installer`.  
> See the [usage docs](https://github.com/roots/wordpress-core-installer#readme).

## Getting Started

```console
composer require roots/wordpress
```

For more detailed information and examples, see the [Composer with WordPress Resources introduction](https://roots.io/composer-wordpress-resources/).

> [!tip]
> Looking for WordPress plugins and themes as Composer packages?
> [WP Packages](https://wp-packages.org/) provides all WordPress.org plugins and themes as a Composer repository.

## Community

Keep track of development and community news.

- Join us on Discord by [sponsoring us on GitHub](https://github.com/sponsors/roots)
- Join us on [Roots Discourse](https://discourse.roots.io/)
- Follow [@rootswp on Twitter](https://twitter.com/rootswp)
- Follow the [Roots Blog](https://roots.io/blog/)
- Subscribe to the [Roots Newsletter](https://roots.io/subscribe/)
