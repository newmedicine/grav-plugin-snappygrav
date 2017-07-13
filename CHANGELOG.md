# v1.5.0
## xx/07/2017

1. [](#bugfix)
    * Update copyright period of the LICENSE file
    * Update blueprints.yaml
    * Update languages.yaml
    * Fixed settings for wkhtmltopdf installed on the server [#19](https://github.com/iusvar/grav-plugin-snappygrav/issues/19)
1. [](#improved)
    * Improved page collection selection
    * Delete unnecessary redefinitions of variables within some cycles
    * README.md cleaning
1. [](#new)
    * Added functionality for the [knowledge-base](https://github.com/Perlkonig/grav-theme-knowledge-base) theme. See also [#10](https://github.com/iusvar/grav-plugin-snappygrav/issues/10)
    * Added the ability to print the current page [branch](https://github.com/iusvar/grav-plugin-snappygrav/issues/17) in top-down mode
    * Now the document is created on time without prior saving on the server
    * Added Nonce features

# v1.4.2
## 23/05/2017

1. [](#bugfix)
    * Replaced break with exit
    * Improved the README
    * Uploading dependencies automatically delayed

# v1.4.1
## 05/05/2017

1. [](#bugfix)
    * Updated blueprints and languages

# v1.4.0
## 05/05/2017

1. [](#bugfix)
    * Check if wkhtmltopdf-i386 is executable
    * Better later than ever: removed the instance that builds the `snappy` object from the `foreach` cycle 
1. [](#improved)
    * Added breadcrumbs [#16](https://github.com/iusvar/grav-plugin-snappygrav/issues/16)

# v1.3.1-rc.1
## 04/02/2017

1. [](#bugfix)
    * Added `composer.json` for required libraries [#13](https://github.com/iusvar/grav-plugin-snappygrav/issues/13)
    * Added `.gitattributes`
1. [](#improved)
    * Updated `README.md`

# v1.3.0
## 03/30/2017

1. [](#new)
    * Added Česky translations [#11](https://github.com/iusvar/grav-plugin-snappygrav/pull/11) (Thanks to [@rbukovansky](https://github.com/rbukovansky) for the PR)
    * Added toggle buttons for preface option [#12](https://github.com/iusvar/grav-plugin-snappygrav/issues/12)
    
# v1.2.0
## 03/28/2017

1. [](#new)
    * Simplified connection with the creation of a function TWIG
    * Added admin translations
    * Added english and italian translations
1. [](#bugfix)
    * Bugfix plugin [#6](https://github.com/iusvar/grav-plugin-snappygrav/issues/6), [#7](https://github.com/iusvar/grav-plugin-snappygrav/issues/7) and [#10](https://github.com/iusvar/grav-plugin-snappygrav/issues/10)

# v1.1.1
## 10/30/2015

1. [](#bugfix)
    * Bugfix in blueprints.yaml [#5](https://github.com/iusvar/grav-plugin-snappygrav/pull/5)
    * Updated snappygrav.yaml
    * Added built_in_css in Settings Defaults

# v1.1.0
## 10/28/2015

1. [](#new)
    * Add the ability to a have custom template, with CSS [#3](https://github.com/iusvar/grav-plugin-snappygrav/pull/3) & [#4](https://github.com/iusvar/grav-plugin-snappygrav/pull/4)
    * Ability to print all website as a PDF (usefull for RTFM website) [#4](https://github.com/iusvar/grav-plugin-snappygrav/pull/4)
1. [](#improved)
    * Cleanup & delete debug... [#3](https://github.com/iusvar/grav-plugin-snappygrav/pull/3) & [#4](https://github.com/iusvar/grav-plugin-snappygrav/pull/4)
    * Updated `README.md` file with information to print all website as a PDF [#4](https://github.com/iusvar/grav-plugin-snappygrav/pull/4)
    * Modified the description of the plugin in `blueprints.yaml`
1. [](#bugfix)
    * Fixed the default zoom in `blueprints.yaml`

# v1.0.3
## 10/03/2015

1. [](#bugfix) 
    * Bugfix in blueprints.yaml

# v1.0.2
## 10/02/2015

1. [](#bugfix) 
    * Bugfix in CHANGELOG.md

# v1.0.1
## 10/01/2015

1. [](#improved)
    * Improved blueprints for Grav Admin plugin
    * Improved `README.md` file with more information
1. [](#bugfix) 
    * Bugfix in snappygrav.php
    * Bugfix in snappygrav.yaml

# v1.0.0
## 09/04/2015

1. [](#new)
    * Initial release.
