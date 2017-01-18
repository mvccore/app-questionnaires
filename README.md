# MvcCore - Application - Questionnaires & Statistics

# Main Features
- questionnaires with anonymous statistics
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
- all packing ways are possible to use:
	- PHAR
	- PHP
		- strict package
		- strict hdd
		- preserve package (currently used for packed app in result dir)
		- preserve hdd
- XML files for questionnaires and their questions are excluded from index.php result package,
  to define any other questionnaires and questions in future, but rest of the application is portable, 
  it means everything else is contained in index.php result file.

## Instalation
```shell
composer require mvccore/app-questionnaires
composer update
cd development
composer update
```

## Pack result application
```shell
make
```
