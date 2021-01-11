# Phiz
A fork of the non-zephir version of the
Phalcon Framework

[Current Phalcon v5.0.x github link](https://github.com/phalcon/phalcon)
Phalcon 5 (or Phiz) isn't Zephir.

Phiz is a fork of the still evolving Phalcon framework in PHP. 
The Phalcon framework is still intent on setting interface and framework standards.

Phalcon 5 is non-zephir version Phalcon 4, and still appears to be incomplete.

The only major change at this time, is to change the Phalcon root name space to Phiz.
I have done this to make it easy to run Phiz (Phalcon5) in a PHP 7.4 environment 
in which the Phalcon 4 framework is still installed, so that
executing Phiz classes are not confused with extension installed Phalcon classes.

Renaming makes it possible to compare test results in a similar execution environment.

Making Phiz PHP 7.4 compatible makes it impossible to use any of the new code features
of 8.0 which would break backwards compatibility to the 7.2 - 7.4 series.
I am only going to bother testing on latest 7.4. This seems practical, since 8.0 series 
is still new with minor teething and adoption issues.

Most of the Phalcon classes written in Zephir, are strictly translatable back to interpreted PHP.
Those which have issues are the ORM Models query functions, for PHQL, and the Volt template engine.
Both of these use C-compiled back end parsers to optimize their efficiency.
The functions of these parsers cannot be easily transformed to PHP.

These PHQL and Volt engine parsers could be PHP C-extensions by themselves.
Without them those dependent parts of the Phalcon framework need to be reimplemented,
perhaps with less ambitious function and generality. I prefered the direct PHP template
engine over using Volt, and my use of PHQL model finding was basic, so I am not out to find
or make another PHP framework to employ.

The Phalcon framework has real design and runtime efficiency benefits, by being a
C-compiled amalgam of php-like code parsed by zephir and compiled as C-code, 
and C-coded parsers for database ORM
Query language - PHQL, and the Volt template engine.

The PHQL component dependency can be replaced for most common model finding usage.
The model class is modified to allow dependency
injection of classes providing model-find service. 
Currently bypass PHQL compile step using a new ModelFinder class.
The Phiz has set up this to have a working basis for this.

The code isn't up to release status yet. There is plenty of code coverage testing to do, 
and missing or broken code still to find.

Unfortunate that PHP 8.0 release came with news that zephir isn't going to be
further developed for PHP 8.0 extension integration.

It seems that phalcon 4.1 will be be last major release of zephir-compiled phalcon.

C-extensions for PHP are not going to go away with the PHP 8.0 release.
No amount of Just in Time  - JIT - optimization, gives as much performance increase as
C-coded intensive computations, and extensions for using other optimized system libraries.
PHP uses several installed by default for database access, string encoding, image processing,
 XML and so on.

It is clear that an adapted PHP version of the Phalcon framework, like Phiz, will have
increase system resource usage, in time and memory.

However once the PHP - extension "Opcache" is enabled, performance times to
show a basic page are around 5 ms on my setup. What will be missed from Phalcon,
without zephir, is the built in PHQL and Volt engine template compilation.

### PHP Resource usage


Here is Phalcon C-extension utilized conditions on my old PC development machine.
They are internal timings captured in PHP script execution until the final part Html 
formatting before response send back, displayed in page "footer".

Comparison between these - the Phiz framework between php 7.4 and 8.0, use 
exactly the same file locations, mediated by php-fpm and php80-fpm sockets from
two different nginx host configuration files.

The Phalcon framework with php 7.4 uses earlier branch of the site in a different 
directory.

The page has minimal routing alternatives setup, no database access,
 no authorization check, and displays a small amount of text,
with resource use in the page footer text. The timings below are all directly 
cut and pasted from my browser to this document, and give "ball-park" figures,
relative to this PC workstation.


OPcache off, by default, on development systems, because its optimization operations
 degrade the usability of xdebug code stepping and breakpoints. 
Opcache disabled is a comparison of handicapped performance, 
and non-cached resource use.

### PHP 7.4.13, using Phalcon 4.1 extension.

Setup 13.85 Handle 0.06 Render 2.55 Total 16.46 ms, Memory 1.09 MB 
Setup 9.78 Handle 0.04 Render 1.31 Total 11.13 ms, Memory 1.09 MB 

#### Opcache enabled --- 
Setup 6.47 Handle 0.08 Render 1.34 Total 7.89 ms, Memory 0.52 MB 

Total time ranges from 15 to 18 ms,   most of the time is "setup".

### PHP 8.0.1 , using Phiz 0.1 nginx  - php80-fpm
Setup 25.90 Handle 2.21 Render 5.23 Total 33.33 ms, Memory 1.91 MB 
Setup 20.73 Handle 1.50 Render 2.86 Total 25.08 ms, Memory 1.91 MB 
Setup 24.34 Handle 2.14 Render 3.79 Total 30.27 ms, Memory 1.91 MB 
Setup 21.73 Handle 2.24 Render 4.04 Total 28.00 ms, Memory 1.91 MB 

#### 8.0.1 Opcache : Enable zend_extension=opcache
Setup 3.71 Handle 0.07 Render 0.63 Total 4.41 ms, Memory 0.68 MB  

I avoid trying to do statistics of multiple runs.
The figures bounce around a lot between 25-33 ms. The time and memory
resource use has nearly doubled from having Zephir-Phalcon-4 

### PHP 7.4.13, using Phiz 0.1 - Same files , nginx, different php-fpm
Setup 28.40 Handle 3.40 Render 6.37 Total 38.18 ms, Memory 1.95 MB 
Setup 23.14 Handle 1.59 Render 2.78 Total 27.51 ms, Memory 1.95 MB 
Setup 26.47 Handle 2.50 Render 4.51 Total 33.48 ms, Memory 1.95 MB 
Setup 22.33 Handle 1.57 Render 2.59 Total 26.49 ms, Memory 1.95 MB 
Setup 28.53 Handle 2.54 Render 4.92 Total 35.99 ms, Memory 1.95 MB 

#### Opcache

Setup 8.05 Handle 0.26 Render 1.67 Total 9.97 ms, Memory 0.68 MB

## From PHP 7.4, to 8.0
I get a small impression that Php 8.0.1 handles the "Setup" a little faster, 
Small reduction in memory use compared to version 7.4.13.
The zend opcache enhances this advantage.  Opcache is an amazing extension
which does what it supposed to do, to abolish first time setup costs 
of interpreting PHP files.

With zend_extension opcache enabled, 
execution is 5 times faster, and extra memory cost improves by 3.

To be able to run Phiz or Phalcon 4 or 5 requires 
installing the php extension for PSR interfaces - php-psr. I cannot tell one
PSR interface number from another at this point of time.

I hope that with some further work, the PHQL and Volt extensions compiled with 
Zephir, might be offered as independent C-extension enhanced functionality.


[PSR interface extension Implementation - ](https://github.com/jbboehr/php-psr)

