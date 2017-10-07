# MvcCore - Application - Questionnaires & Statistics

[![Latest Stable Version](https://img.shields.io/badge/Stable-v4.2.0-brightgreen.svg?style=plastic)](https://github.com/mvccore/app-questionnaires/releases)
[![License](https://img.shields.io/badge/Licence-BSD-brightgreen.svg?style=plastic)](https://github.com/mvccore/app-questionnaires/blob/master/LICENCE.md)
[![Packager Build](https://img.shields.io/badge/Packager%20Build-passing-brightgreen.svg?style=plastic)](https://github.com/mvccore/packager)
![PHP Version](https://img.shields.io/badge/PHP->=5.3-brightgreen.svg?style=plastic)

## Demo
- [**Questionnaire Form**](http://ankety.tomflidr.cz/questions/common-it-knowledge-en)
- [**Questionnaire Statistics**](http://ankety.tomflidr.cz/results/common-it-knowledge-en)

## Features
- [**MvcCore**](https://github.com/mvccore/mvccore) application managing questionnaires with anonymous statistics
- all questionnaires and questions readed from XML (dynamic forms completing by XML content)
- all answers stored in mysql/mssql database in 3 tables
- each questionnaire shoud have any number of questions
- possible answer types:
  - single line text
  - multiline text
  - number/float
  - checkbox - yes/no
  - checkbox - yes/no with additional single line text
  - checkbox group
  - radio buttons
  - connections for options
- each question has it's advanced configuration for answer(s) in custom XML file
- each questionnaire has it's own statistics for each question/answer(s)
  - rendered with [**Ext.JS 6.0.0-gpl graphs API**](http://examples.sencha.com/extjs/6.0.2/examples/kitchensink/?charts=true#all)
- questionnaire forms and statistic results have desktop and mobile version
- result application **currently packed in preserve package mode**, 4 packing configurations included in `./.packager/`
- packed with [**Packager library - mvccore/packager**](https://github.com/mvccore/packager)), all packing ways possible:
  - **PHAR file**
    - standard PHAR package with whole devel directory content
  - **PHP file**
    - **strict package**
      - everything is contained in result `index.php`
      - only `.htaccess` or `web.config` are necessary to use mod_rewrite
    - **preserve package**
      - result `index.php` file contains PHP files, 
        PHTML templates but no CSS/JS/fonts or images
      - all wrapped file system functions are looking inside 
        package first, then they try to read data from HDD
	  - currently used for packed app in result directory
    - **preserve hdd**
      - result `index.php` file contains PHP files, 
        PHTML templates but no CSS/JS/fonts or images
      - all wrapped file system functions are looking on HDD first, 
        then they try to read data from package inself
    - **strict hdd**
      - result `index.php` file contains only PHP files, 
        but PHTML templates, all CSS/JS/fonts and images are on HDD
      - no PHP file system function is wrapped
- XML files for questionnaires and their questions are excluded from index.php result package,
  to define any other questionnaires and questions in future, but rest of the application is portable, 
  it means everything else is contained in index.php result file.

## Instalation
```shell
# load example
composer create-project mvccore/app-questionnaires

# go to project development directory
cd app-questionnaires/development

# update dependencies for app development sources
composer update
```

## Build

### 1. Prepare application
- go to `app-questionnaires/development`
- clear everything in `./Var/Tmp/`
- change `$app->Run();` to `$app->Run(1);` in `./index.php`
- visit all aplication routes where are different JS/CSS bundles 
  groups to generate `./Var/Tmp/` content for result app
- run build process

### 2. Build

#### Linux:
```shell
# go to project root directory
cd app-questionnaires
# run build process into single PHP file
sh make.sh
```

#### Windows:
```shell
# go to project root directory
cd app-questionnaires
# run build process into single PHP file
make.cmd
```

#### Browser:
```shell
# visit script `make-php.php` in your project root directory:
http://localhost/app-questionnaires/make-php.php
# now run your result in:
http://localhost/app-questionnaires/release/
```
