# PHP IP Address
Library providing immutable value objects to represent an arbitrary single but valid IP bytes in version 4 or 6.
 A super-type for automatic construction of the appropriate version and of course to program against is provided as
 well.

## Installation
Open a terminal, enter your project directory and execute the following command to add this package to your dependencies:

```
$ composer require fleshgrinder/ip-bytes
```

This command requires you to have Composer installed globally, as explained in the
 [installation chapter](https://getcomposer.org/doc/00-intro.md) of the Composer documentation.

## Usage
The implementation is inspired by the [Rust standard implementation](https://doc.rust-lang.org/std/net/enum.IpAddr.html)
 of an Internet Protocol bytes. Most available libraries and standard implementations provide implementations with too
 much behavior that is inappropriate for a simple IP bytes value object. For instance Javaʼs `InetAddress`
 implementation is hardwired together with hostnames and 

## License
[![MIT License](https://upload.wikimedia.org/wikipedia/commons/thumb/c/c3/License_icon-mit.svg/48px-License_icon-mit.svg.png)](https://opensource.org/licenses/MIT)
