# v0.11.0
## 3/12/2018

1. [](#new)
    * Copy assets option searches for standard web assets and copies them into the output path (you may specify file types)

# v0.10.0
## 3/11/2018

1. [](#bugfix)
    * Fixed bug in RollingCurl where `prunePendingRequestQueue` was limiting the generation count ([#24](https://github.com/barrymode/grav-plugin-blackhole/issues/24))
    * Fixed bug with the simultaneous limit where manual entry was returning an error

# v0.9.2
## 11/29/2017

1. [](#new)
    * Set how many files will be generated simultaneously

# v0.9.1
## 10/23/2017

1. [](#bugfix)
    * Fixed bug where only the home page was generated when -r wasn't set

# v0.9.0
## 10/23/2017

1. [](#new)
    * Force option allows for users to overwrite previously generated files
    * Routes option allows the user to choose which pages get generated
1. [](#improved)
    * Separate the admin plugin settings from the CLI

# v0.8.4
## 9/1/2017

1. [](#bugfix)
    * Reverted to previous route method because collections are inadequate

# v0.8.3
## 8/18/2017

1. [](#improved)
    * Remove modular pages from routes ([#18](https://github.com/barrymode/grav-plugin-blackhole/pull/18))
2. [](#bugfix)
    * Fixed output-url ([#19](https://github.com/barrymode/grav-plugin-blackhole/pull/19))

# v0.8.2
## 8/1/2017

1. [](#bugfix)
    * Fixed issue where generating path was blank in the CL

# v0.8.1
## 8/1/2017

1. [](#new)
    * Added timer
2. [](#bugfix)
    * Fixed memory growth problem ([#16](https://github.com/barrymode/grav-plugin-blackhole/issues/16))

# v0.8.0
## 7/30/2017

1. [](#improved)
    * Lightning fast generation with asynchronous curl

# v0.7.2
## 7/25/2017

1. [](#improved)
    * GitHub links updated

# v0.7.1
## 6/19/2017

1. [](#improved)
    * Minor optimizations of the file generation method
2. [](#bugfix)
    * Fixed mkdir errors ([#12](https://github.com/barrymode/grav-plugin-blackhole/issues/12))

# v0.7.0
## 6/18/2017

1. [](#new)
    * Feature: only render pages that have changed/been modified ([#12](https://github.com/barrymode/grav-plugin-blackhole/issues/12))

# v0.6.0
## 6/18/2017

1. [](#new)
    * Portal function solves ([#10](https://github.com/barrymode/grav-plugin-blackhole/issues/10))

# v0.5.1
## 6/1/2017

1. [](#bugfix)
    * Fixed ([#7](https://github.com/barrymode/grav-plugin-blackhole/issues/7) and [#9](https://github.com/barrymode/grav-plugin-blackhole/issues/9))

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
    * Website must be defined ([#4](https://github.com/barrymode/grav-plugin-blackhole/issues/4))

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
    * Fixed link error to pages query ([#3](https://github.com/barrymode/grav-plugin-blackhole/issues/3))

# v0.2.3
## 11/12/2016

1. [](#bugfix)
    * Fixed directory path issue for unix style ([#1](https://github.com/barrymode/grav-plugin-blackhole/issues/1))
    * Fixed every page being generated as the home page ([#2](https://github.com/barrymode/grav-plugin-blackhole/issues/2))

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