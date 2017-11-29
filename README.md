# Blackhole Plugin
[![](https://img.shields.io/badge/paypal-donate-blue.svg)](https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=barrymode%40protonmail%2ecom&lc=EN&item_name=BarryMode&item_number=Donation&currency_code=USD)

The **Blackhole** Plugin is for [Grav CMS](http://github.com/getgrav/grav).

## Description

Why Blackhole? Grav is a space term, so I think this plugin should follow suit. Time stops at the event horizon of a black hole, which is exactly what this plugin does to your website. It freezes it in a state. By Increasing **grav**ity to infinity you get a **static** black hole, or in this case you generate a **static** html copy of your **Grav** website.

*Currently, Blackhole only supports pages.*

## Installation

### GPM Installation

The simplest way to install this plugin is via the Grav Package Manager (GPM). From the root of your Grav install type:
`bin/gpm install blackhole`

### Manual Installation

If you can't use GPM you can manually install this plugin. Download the zip version of this repository and unzip it under `/your/site/grav/user/plugins`, then rename the folder to `blackhole`.

## Usage

### Generate Command

Generate your static site. `generate` can also be written as `gen` or `g`.

- **Input URL** (required) - Set the URL of your live Grav site.

```bash
bin/plugin blackhole generate http://localhost/grav
```

#### Options

- **Output URL** `--output-url` or `-d`
  Set the URL of your static site. This determines the domain used in the absolute path of your links.

  ```bash
  --output-url https://website.com
  ```

- **Output Path** `--output-path` or `-p`
  Set the directory to which your static site will be written (relative to Grav root).

  ```bash
  --output-path ../build
  ```

- **Routes** `--routes` or `-r`
  Define a list of routes to only generate certain pages. Accepts a comma-separated list.

  ```bash
  --routes home,about,about/contact
  ```

- **Force** `--force` or `-f`
  Overwrite previously generated files.

## Author

| [![BarryMode](https://avatars3.githubusercontent.com/u/5648875?v=2&s=70)](https://twitter.com/barrymode "Follow @BarryMode on Twitter") |
|---|
| [BarryMode](https://barrymode.github.io) |
