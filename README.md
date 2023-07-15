<p align="center">
<img src="https://github.com/DeGraciaMathieu/php-smelly-code-detector/blob/master/arts/robot.png" width="250">
</p>

[![testing](https://github.com/DeGraciaMathieu/php-line-length-detector/actions/workflows/testing.yml/badge.svg)](https://github.com/DeGraciaMathieu/php-line-length-detector/actions/workflows/testing.yml)
![Packagist Version](https://img.shields.io/packagist/v/degraciamathieu/php-line-length-detector)
![Packagist PHP Version](https://img.shields.io/packagist/dependency-v/degraciamathieu/php-line-length-detector/php)

# php-line-length-detector

A simple way to analyze the line length of your PHP files.

# Installation

```
Requires >= PHP 8.1
```

## Phar
This tool is distributed as a [PHP Archive (PHAR)](https://www.php.net/phar):

```
wget https://github.com/DeGraciaMathieu/php-line-length-detector/raw/master/builds/php-line-length-detector
```

```
php php-line-length-detector --version
```

## Composer
Alternately, you can directly use composer :

```
composer require degraciamathieu/php-line-length-detector --dev
```
# Usage

```
php php-line-length-detector inspect {path}
```

```
$ php php-line-length-detector inspect app

❀ PHP Line Lenght Detector ❀
+-------------+--------------+--------------+
| total lines | largest line | average line |
+-------------+--------------+--------------+
| 1068        | 197          | 37           |
+-------------+--------------+--------------+
+--------+------------+---------+
| length | occurrence | percent |
+--------+------------+---------+
| > 160  | 2          | 0 %     |
| > 120  | 5          | 0 %     |
| > 80   | 29         | 2 %     |
| > 60   | 111        | 10 %    |
| > 30   | 618        | 57 %    |
+--------+------------+---------+
```
You can configure thresholds with the `--thresholds=` option (default : 160,120,80,60,30) :
```
$ php php-line-length-detector inspect app --thresholds=120,60

❀ PHP Line Lenght Detector ❀
+-------------+--------------+--------------+
| total lines | largest line | average line |
+-------------+--------------+--------------+
| 1068        | 197          | 37           |
+-------------+--------------+--------------+
+--------+------------+---------+
| length | occurrence | percent |
+--------+------------+---------+
| > 120  | 5          | 0 %     |
| > 60   | 111        | 10 %    |
+--------+------------+---------+
```
