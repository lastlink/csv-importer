# Firefly III CSV Importer

<!-- MarkdownTOC autolink="true" -->

- [Introduction](#introduction)
	- [Purpose](#purpose)
	- [Features](#features)
	- [Who's it for?](#whos-it-for)
- [Installation](#installation)
	- [Upgrade](#upgrade)
- [Usage](#usage)
- [Known issues and problems](#known-issues-and-problems)
- [Other stuff](#other-stuff)
	- [Contribute](#contribute)
	- [Versioning](#versioning)
	- [License](#license)
	- [Contact](#contact)
	- [Donate](#donate)
	- [Badges](#badges)

<!-- /MarkdownTOC -->

## Introduction

This is a tool to import CSV files into [Firefly III](https://github.com/firefly-iii/firefly-iii). It works by using a personal access token to access your Firefly III installation's API. It will then create transactions based upon the CSV files you upload.

At this moment, the tool is in beta, which means that it may not work as expected. Please bear with me and open all the issues and PR's you like. I greatly appreciate your input!

### Purpose

Use this tool to (automatically) import your bank's CSV files into Firefly III. If you're a bit of a developer, feel free to use this code to generate your own import tool.

### Features

* This tool will let you download or generate a configuration file, so the next import will go faster.

### Who's it for?

Anybody who uses Firefly III and wants to automatically import files.

## Installation

You can use this tool in several ways.

1. [Install it on your server using composer](https://firefly-iii.gitbook.io/firefly-iii-csv-importer/installing-and-running/self-hosted).
2. [Use the Docker-image](#https://firefly-iii.gitbook.io/firefly-iii-csv-importer/installing-and-running/docker).

Generally speaking, it's easiest to use and install this tool the same way as you use Firefly III. And although it features an excellent web-interface, you can also use the command line to import your data.

### Upgrade

There are [upgrade instructions](https://firefly-iii.gitbook.io/firefly-iii-csv-importer/upgrading/upgrade) for boths methods of installation.

## Usage

The [full usage instructions](https://firefly-iii.gitbook.io/firefly-iii-csv-importer/) can be found in the documentation. Basically, this is the workflow.

1. [Set up and configure your Personal Access Token and Firefly III URL](https://firefly-iii.gitbook.io/firefly-iii-csv-importer/installing-and-running/configure).
2. [Upload your CSV file](https://firefly-iii.gitbook.io/firefly-iii-csv-importer/importing-data/upload).
3. [Tell the importer what your CSV file looks like. Date format, data types, etc](https://firefly-iii.gitbook.io/firefly-iii-csv-importer/importing-data/configure).
4. [Set each column's role and data type](https://firefly-iii.gitbook.io/firefly-iii-csv-importer/importing-data/roles).
5. [Map values in the CSV file to existing values in your database](https://firefly-iii.gitbook.io/firefly-iii-csv-importer/importing-data/map).
6. [Enjoy the result in Firefly III](https://github.com/firefly-iii/firefly-iii).

## Known issues and problems

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

### Versioning

The Firefly III CSV Importer uses [SemVer](https://semver.org/) for versioning. For the versions available, see [the tags](https://github.com/firefly-iii/csv-importer/tags) on this repository.

### License

This work [is licensed](https://github.com/firefly-iii/csv-importer/blob/master/LICENSE) under the [GNU Affero General Public License v3](https://www.gnu.org/licenses/agpl-3.0.html).

### Contact

You can contact me at [thegrumpydictator@gmail.com](mailto:thegrumpydictator@gmail.com), you may open an issue or contact me through the various social media pages there are: [reddit](https://www.reddit.com/r/FireflyIII/) and [Twitter](https://twitter.com/Firefly_III).

### Donate

If you like this tool and if it helps you save lots of money, why not send me a dime for every dollar saved!

OK that was a joke. You can donate using [PayPal](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=44UKUT455HUFA) or [Patreon](https://www.patreon.com/jc5).

I am very proud to be a part of the **[GitHub Sponsors Program](https://github.com/sponsors/JC5)**. Use their program if you can; they'll double your donation!

Thank you for considering donating to Firefly III, and the CSV Importer.

### Badges

[![Scrutinizer](https://img.shields.io/scrutinizer/g/firefly-iii/csv-importer.svg?style=flat-square)](https://scrutinizer-ci.com/g/firefly-iii/csv-importer/)
[![Requires PHP7.3](https://img.shields.io/badge/php-7.3-red.svg?style=flat-square)](https://secure.php.net/downloads.php)
