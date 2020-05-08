[![Current release](https://img.shields.io/github/release/vaites/php-apache-tika.svg)](https://github.com/vaites/php-apache-tika/releases/latest)
[![Package at Packagist](https://img.shields.io/packagist/dt/vaites/php-apache-tika.svg)](https://packagist.org/packages/vaites/php-apache-tika)
[![Build status](https://travis-ci.org/vaites/php-apache-tika.svg?branch=master)](https://travis-ci.org/vaites/php-apache-tika)
[![Code coverage](https://img.shields.io/codecov/c/github/vaites/php-apache-tika.svg)](https://codecov.io/github/vaites/php-apache-tika)
[![Code quality](https://img.shields.io/scrutinizer/quality/g/vaites/php-apache-tika.svg)](https://scrutinizer-ci.com/g/vaites/php-apache-tika/)
[![Code insight](https://img.shields.io/sensiolabs/i/ec066502-0fde-4455-9fc3-8e9fe6867834.svg)](https://insight.sensiolabs.com/projects/ec066502-0fde-4455-9fc3-8e9fe6867834)
[![License](https://img.shields.io/github/license/vaites/php-apache-tika.svg?color=%23999999)](https://github.com/vaites/php-apache-tika/blob/master/LICENSE)

# PHP Apache Tika

This tool provides [Apache Tika](https://tika.apache.org) bindings for PHP, allowing to extract text and metadata 
from documents, images and other formats. 

The following modes are supported:
* **App mode**: run app JAR via command line interface
* **Server mode**: make HTTP requests to [JSR 311 network server](https://cwiki.apache.org/confluence/display/TIKA/TikaServer)

Server mode is recommended because is 5 times faster, but some shared hosts don't allow run processes in background.

Although the library contains a list of supported versions, any version of Apache Tika should be compatible as long as
backward compatibility is maintained by Tika team. Therefore, it is not necessary to wait for an update of the library 
to work with the new versions of the tool.

## Features

* Simple class interface to Apache Tika features:
    * Text and HTML extraction
    * Metadata extraction
    * OCR recognition
* Standarized metadata for documents
* Support for local and remote resources
* No heavyweight library dependencies
* Compatible with Apache Tika 1.7 or greater
    * Tested up to 1.24.1

## Requirements

* PHP 5.4 or greater
    * [Multibyte String support](http://php.net/manual/en/book.mbstring.php)
    * [cURL extension](http://php.net/manual/en/book.curl.php)
* Apache Tika 1.7 or greater
* Oracle Java or OpenJDK 
    * Java 6 for Tika up to 1.9
    * Java 7 for Tika 1.10 or greater
* [Tesseract](https://github.com/tesseract-ocr/tesseract) (optional for OCR recognition)
    
## Installation

Install using Composer:

```php
composer require vaites/php-apache-tika
```

If you want to use OCR you must install [Tesseract](https://github.com/tesseract-ocr/tesseract):

* **Fedora/CentOS**: `sudo yum install tesseract` (use dnf instead of yum on Fedora 22 or greater)
* **Debian/Ubuntu**: `sudo apt-get install tesseract-ocr`
* **Mac OS X**: `brew install tesseract` (using [Homebrew](http://brew.sh))

The library assumes `tesseract` binary is in path, so you can compile it yourself or install using any other method. 

## Usage

Start Apache Tika server with [caution](http://www.openwall.com/lists/oss-security/2015/08/13/5):

```bash
java -jar tika-server-x.xx.jar
```

If you are using JRE instead of JDK, you must run if you have Java 9 or greater:

```bash
java --add-modules java.se.ee -jar tika-server-x.xx.jar
```

Instantiate the class, checking if JAR exists or server is running:

```php
$client = \Vaites\ApacheTika\Client::make('localhost', 9998);           // server mode (default)
$client = \Vaites\ApacheTika\Client::make('/path/to/tika-app.jar');     // app mode 
```

If you want to use dependency injection, serialize the class or just delay the check:

```php
$client = \Vaites\ApacheTika\Client::prepare('localhost', 9998);
$client = \Vaites\ApacheTika\Client::prepare('/path/to/tika-app.jar'); 
```

You can use an URL too:

```php
$client = \Vaites\ApacheTika\Client::make('http://localhost:9998');
$client = \Vaites\ApacheTika\Client::prepare('http://localhost:9998');
```

Use the class to extract text from documents:

```php
$language = $client->getLanguage('/path/to/your/document');
$metadata = $client->getMetadata('/path/to/your/document');

$html = $client->getHTML('/path/to/your/document');
$text = $client->getText('/path/to/your/document');
```

Or use to extract text from images:

```php
$client = \Vaites\ApacheTika\Client::make($host, $port);
$metadata = $client->getMetadata('/path/to/your/image');

$text = $client->getText('/path/to/your/image');
```
    
You can use an URL instead of a file path and the library will download the file and pass it to Apache Tika. There's 
**no need** to add `-enableUnsecureFeatures -enableFileUrl` to command line when starting the server, as described 
[here](https://wiki.apache.org/tika/TikaJAXRS#Specifying_a_URL_Instead_of_Putting_Bytes).

### Methods

Here are the full list of available methods

#### Common

Tika file related methods:

```php
$client->getMetadata($file);
$client->getRecursiveMetadata($file, 'text');
$client->getLanguage($file);
$client->getMIME($file);
$client->getHTML($file);
$client->getText($file);
$client->getMainText($file);
```
    
Other Tika related methods:

```php
$client->getSupportedMIMETypes();
$client->getAvailableDetectors();
$client->getAvailableParsers();
$client->getVersion();
```

Encoding methods:
```php
$client->getEncoding();
$client->setEncoding('UTF-8');
```
    
Supported versions related methods:

```php
$client->getSupportedVersions();
$client->isVersionSupported($version);
```

Set/get a callback for sequential read of response:

```php
$client->setCallback($callback);
$client->getCallback();
```
    
Set/get the chunk size for secuential read:

```php
$client->setChunkSize($size);
$client->getChunkSize();
```
    
Enable/disable the internal remote file downloader:

```php
$client->setDownloadRemote(true);
$client->getDownloadRemote();
```

#### Command line client
    
Set/get JAR/Java paths (only CLI mode):

```php
$client->setPath($path);
$client->getPath();

$client->setJava($java);
$client->getJava();
```

#### Web client
    
Set/get host properties

```php
$client->setHost($host);
$client->getHost();

$client->setPort($port);
$client->getPort();

$client->setUrl($url);
$client->getUrl();

$client->setRetries($retries);
$client->getRetries();
```
    
Set/get [cURL client options](http://php.net/manual/en/function.curl-setopt.php)

```php
$client->setOptions($options);
$client->getOptions();
$client->setOption($option, $value);
$client->getOption($option);
```

Set/get cURL client common options:

```php
$client->setTimeout($seconds);
$client->getTimeout();
```

## Troubleshooting

### Empty responses or unexpected results

This library is only a _proxy_ so if you get an empy responses or unexpected results the most common cause is Tika 
itself. A simple test is using the GUI to check the response:

1. Run the Tika app without arguments: `java -jar tika-app-x.xx.jar` 
2. Drop your file or select it using _File -> Open_
3. Wait until the metadata appears
4. Get the text or HTML using _View_ menu

If the results are the same, you must take a look into [Tika's Jira](https://issues.apache.org/jira/projects/TIKA/issues)
and open an issue if necessary.

### Encoding

By default the returned text is encoded with UTF-8 but there are some issues with the encoding when using the app mode.
The `Client::setEncoding()` method allows to set the expected encoding (this will be fixed in the upcoming 1.0 release). 

## Tests

Tests are designed to **cover all features for all supported versions** of Apache Tika in app mode and server mode. 
There are a few samples to test against:

* **sample1**: document metadata and text extraction
* **sample2**: image metadata 
* **sample3**: text recognition
* **sample4**: unsupported media
* **sample5**: huge text for callbacks 
* **sample6**: remote calls 
* **sample7**: text encoding

## Known issues

There are some issues found during tests, not related with this library:

* 1.9 version running Java 7 on server mode throws random error 500 (*Unexpected RuntimeException*)
* 1.14 version on server mode throws random errors (*Expected ';', got ','*) when parsing image metadata
* Tesseract slows down document parsing as described in [TIKA-2359](https://issues.apache.org/jira/browse/TIKA-2359)
    
## Integrations

- [Symfony2 Bundle](https://github.com/welcoMattic/ApacheTikaBundle)
