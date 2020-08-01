[![Packagist][packagist-shield]][packagist-uri]
[![License][license-shield]][license-uri]
[![Stargazers][stars-shield]][stars-url]
[![Donate][donate-shield]][donate-uri]

<!-- PROJECT LOGO -->
<br />
<p align="center">
  <a href="https://firefly-iii.org/">
    <img src="https://www.firefly-iii.org/static/img/logo-small-new.png" alt="Logo" width="96" height="96">
  </a>
</p>
  <h1 align="center">Firefly III CSV importer</h1>

  <p align="center">
    A tool to import CSV files into Firefly III
    <br />
    <a href="https://firefly-iii.gitbook.io/firefly-iii-csv-importer/"><strong>Explore the docs »</strong></a>
    <br />
    <br />
    <a href="https://demo.firefly-iii.org/">View Firefly III demo</a>
    ·
    <a href="https://github.com/firefly-iii/firefly-iii/issues">Report Bug</a>
    ·
    <a href="https://github.com/firefly-iii/firefly-iii/issues">Request Feature</a>
  </p>

<!-- MarkdownTOC autolink="true" -->

- [About the Firefly III CSV importer](#about-the-firefly-iii-csv-importer)
  - [Purpose](#purpose)
  - [Features](#features)
  - [Who's it for?](#whos-it-for)
- [Getting Started](#getting-started)
  - [Using the web interface](#using-the-web-interface)
  - [Using the auto-import](#using-the-auto-import)
- [Known import challenges](#known-import-challenges)
- [Other stuff](#other-stuff)
  - [Contribute](#contribute)
  - [Support the development of Firefly III](#support-the-development-of-firefly-iii)
  - [Contact](#contact)

<!-- /MarkdownTOC -->

## About the Firefly III CSV importer
This is a tool to import CSV files into [Firefly III](https://github.com/firefly-iii/firefly-iii). It works by using a personal access token to access your Firefly III installation's API. It will then create transactions based upon the CSV files you upload.

### Purpose

Use this tool to (automatically) import your bank's CSV files into Firefly III. If you're a bit of a developer, feel free to use this code to generate your own import tool.

### Features

* This tool will let you download or generate a configuration file, so the next import will go faster.

### Who's it for?

Anybody who uses Firefly III and wants to automatically import files.

## Getting Started

You can use this tool in several ways.

1. [Install it on your server using composer](https://firefly-iii.gitbook.io/firefly-iii-csv-importer/installing-and-running/self_hosted).
2. [Use the Docker-image](https://firefly-iii.gitbook.io/firefly-iii-csv-importer/installing-and-running/docker).

Generally speaking, it's easiest to use and install this tool the same way as you use Firefly III. And although it features an excellent web-interface, you can also use the command line to import your data. There are [upgrade instructions](https://firefly-iii.gitbook.io/firefly-iii-csv-importer/upgrading/upgrade) for both methods of installation.

The [full usage instructions](https://firefly-iii.gitbook.io/firefly-iii-csv-importer/) can be found in the documentation. Basically, this is the workflow:

### Using the web interface

1. [Set up and configure your Personal Access Token and Firefly III URL](https://firefly-iii.gitbook.io/firefly-iii-csv-importer/installing-and-running/configure).
2. [Upload your CSV file](https://firefly-iii.gitbook.io/firefly-iii-csv-importer/importing-data/upload).
3. [Tell the importer what your CSV file looks like.](https://firefly-iii.gitbook.io/firefly-iii-csv-importer/importing-data/configure).
4. [Set each column's role and data type](https://firefly-iii.gitbook.io/firefly-iii-csv-importer/importing-data/roles).
5. [Map values in the CSV file to existing values in your database](https://firefly-iii.gitbook.io/firefly-iii-csv-importer/importing-data/map).
6. [Enjoy the result in Firefly III](https://github.com/firefly-iii/firefly-iii).

### Using the auto-import

1. Import at least once so you'll have a configuration file.
2. Or, get a configuration file from [the repository](https://github.com/firefly-iii/import-configurations).
3. [Run the Docker inline import command](https://firefly-iii.gitbook.io/firefly-iii-csv-importer/importing-data/command_line)

## Known import challenges

Most people run into the same problems when importing data into Firefly III. Read more about those on the following pages:

1. [Issues with your Personal Access Token](https://firefly-iii.gitbook.io/firefly-iii-csv-importer/errors-and-trouble-shooting/token_errors)
1. [Often seen errors and issues](https://firefly-iii.gitbook.io/firefly-iii-csv-importer/errors-and-trouble-shooting/freq_errors).
2. [Frequently asked questions](https://firefly-iii.gitbook.io/firefly-iii-csv-importer/errors-and-trouble-shooting/freq_questions).
3. [My bank delivers bad CSV files, what do I do now?](https://firefly-iii.gitbook.io/firefly-iii-csv-importer/errors-and-trouble-shooting/bad_files)

## Other stuff

### Contribute

Your help is always welcome! Feel free to open issues, ask questions, talk about it and discuss this tool. You can also join [reddit](https://www.reddit.com/r/FireflyIII/) or follow me on [Twitter](https://twitter.com/Firefly_III).

Of course, there are some [contributing guidelines](https://github.com/firefly-iii/csv-importer/blob/master/.github/contributing.md) and a [code of conduct](https://github.com/firefly-iii/csv-importer/blob/master/.github/code_of_conduct.md), which I invite you to check out.

For all other contributions, see below.

### Support the development of Firefly III

If you like this tool and if it helps you save lots of money, why not send me a dime for every dollar saved!

OK that was a joke. You can donate using [PayPal](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=L62W7DVD5ETPC&source=url), [Patreon](https://www.patreon.com/jc5) or the [GitHub Sponsors Program](https://github.com/sponsors/JC5).

This work [is licensed](https://github.com/firefly-iii/csv-importer/blob/master/LICENSE) under the [GNU Affero General Public License v3](https://www.gnu.org/licenses/agpl-3.0.html).

### Contact

You can contact me at [james@firefly-iii.org](mailto:james@firefly-iii.org), you may open an issue or contact me through the various social media pages there are: [reddit](https://www.reddit.com/r/FireflyIII/) and [Twitter](https://twitter.com/Firefly_III).

[![Scrutinizer][scrutinizer-shield]][scrutinizer-uri]
[![Requires PHP7.3][php-shield]][php-uri]
[![Quality Gate Status](gate-shield)](gate-uri)

[scrutinizer-shield]: https://img.shields.io/scrutinizer/g/firefly-iii/csv-importer.svg?style=flat-square
[scrutinizer-uri]: https://scrutinizer-ci.com/g/firefly-iii/csv-importer/
[php-shield]: https://img.shields.io/badge/php-7.3-red.svg?style=flat-square
[php-uri]: https://secure.php.net/downloads.php
[packagist-shield]: https://img.shields.io/packagist/v/firefly-iii/csv-importer.svg?style=flat-square
[packagist-uri]: https://packagist.org/packages/firefly-iii/csv-importer
[license-shield]: https://img.shields.io/github/license/firefly-iii/csv-importer.svg?style=flat-square
[license-uri]: https://www.gnu.org/licenses/agpl-3.0.html
[stars-shield]: https://img.shields.io/github/stars/firefly-iii/csv-importer.svg?style=flat-square
[stars-url]: https://github.com/firefly-iii/csv-importer/stargazers
[donate-shield]: https://img.shields.io/badge/donate-%24%20%E2%82%AC-brightgreen?style=flat-square
[donate-uri]: #support
[gate-shield]: https://sonarcloud.io/api/project_badges/measure?project=firefly-iii_csv-importer&metric=alert_status
[gate-uri]: https://sonarcloud.io/dashboard?id=firefly-iii_csv-importer