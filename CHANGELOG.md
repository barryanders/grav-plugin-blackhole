# v0.7.1
## 6/19/2017

1. [](#improved)
    * Minor optimizations of the file generation method

2. [](#bugfix)
    * Fixed mkdir errors [#12](https://github.com/itssohma/grav-plugin-blackhole/issues/12)

# v0.7.0
## 6/18/2017

1. [](#new)
    * Feature: only render pages that have changed/been modified. [#12](https://github.com/itssohma/grav-plugin-blackhole/issues/12)

# v0.6.0
## 6/18/2017

1. [](#new)
    * Portal function solves [#10](https://github.com/itssohma/grav-plugin-blackhole/issues/10)

## 6/1/2017

1. [](#bugfix)
    * Fixed [#7](https://github.com/itssohma/grav-plugin-blackhole/issues/7) and [#9](https://github.com/itssohma/grav-plugin-blackhole/issues/9)

# v0.5.0
## 4/9/2017

1. [](#improved)
    * The website argument was reimplemented to consider all scenarios

# v0.4.1
## 4/7/2017

1. [](#improved)
    * Added error messages
    * Changed version history
2. [](#bugfix)
    * Website must be defined ([#4](https://github.com/itssohma/grav-plugin-blackhole/issues/4))

# v0.4.0
## 4/6/2017

1. [](#new)
    * Destination can be set in the admin plugin

# v0.3.1
## 4/6/2017

1. [](#bugfix)
    * Hotfix for writing content to the wrong place

# v0.3.0
## 4/6/2017

1. [](#improved)
    * Removed hacky JS shit
    * Added `g` as an alt short form of generate
2. [](#bugfix)
    * Fixed link error to pages query ([#3](https://github.com/itssohma/grav-plugin-blackhole/issues/3))

# v0.2.3
## 11/12/2016

1. [](#bugfix)
    * Fixed directory path issue for unix style ([#1](https://github.com/itssohma/grav-plugin-blackhole/issues/1))
    * Fixed every page being generated as the home page ([#2](https://github.com/itssohma/grav-plugin-blackhole/issues/2))

# v0.2.2
##  10/07/2016

1. [](#bugfix)
    * Hotfix for writing content to page

# v0.2.1
##  10/07/2016

1. [](#improved)
    * Function for curl
    * Minor syntax
2. [](#bugfix)
    * Hotfix for homepage route

# v0.2.0
##  10/07/2016

1. [](#improved)
    * A proper rewrite of the routes API call

# v0.1.0
##  10/06/2016

1. [](#new)
    * Use ?pages=all to bypass impossible call grav->['pages'] in CLI
    * Loop and curl to generate the pages
    * Named Blackhole to go with Grav
    * ChangeLog, Blueprints, README