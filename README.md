# Blackhole Plugin

The **Blackhole** Plugin is for [Grav CMS](http://github.com/getgrav/grav).

## Description

Why Blackhole? Grav is a space term, so I think this plugin should follow suit. Time stops at the event horizon of a black hole, which is exactly what this plugin does to your website. It freezes it in a state. By Increasing **grav**ity to infinity you get a **static** black hole, or in this case you generate a **static** html copy of your **Grav** website.

*Currently, Blackhole only supports pages.*

## Installation

### GPM Installation (NOT SUPPORTED YET)

The simplest way to install this plugin is via the Grav Package Manager (GPM). From the root of your Grav install type:
`bin/gpm install blackhole`

### Manual Installation

If you can't use GPM you can manually install this plugin. Download the zip version of this repository and unzip it under `/your/site/grav/user/plugins`, then rename the folder to `blackhole`.

## Usage

To generate your static site, run the following command.

- `generate` can also be written as `gen` or `g`.
- Replace `website.com` with your domain name.
- Optional: replace `static` with your desired output directory, which is relative to the Grav root directory. Alternatively, set the path from within the admin plugin or do nothing to generate in the Grav root directory.

```bash
bin/plugin blackhole generate website.com static
```

## Author

| [![twitter/barryanders](https://avatars3.githubusercontent.com/u/5648875?v=2&s=70)](http://twitter.com/barryanders "Follow @barryanders on Twitter") |
|---|
| [Barry Anders](https://barryanders.github.io/) |
