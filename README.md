![Blackhole](https://user-images.githubusercontent.com/5648875/33234047-8bd21c26-d1e5-11e7-80d3-aa98f22235c6.png)

The **Blackhole** Plugin is for [Grav CMS](http://github.com/getgrav/grav).

## Description

Why Blackhole? Grav is a space term, so I think this plugin should follow suit. Time stops at the event horizon of a black hole, which is exactly what this plugin does to your website. It freezes it in a state. By Increasing **grav**ity to infinity you get a **static** black hole, or in this case you generate a **static** html copy of your **Grav** website.

*Pagination not currently supported.*

## Installation

### GPM Installation

The simplest way to install this plugin is via the Grav Package Manager (GPM). From the root of your Grav install type:
`bin/gpm install blackhole`

### Manual Installation

If you can't use GPM you can manually install this plugin. Download the zip version of this repository and unzip it under `/your/site/grav/user/plugins`, then rename the folder to `blackhole`.

## Usage

Absolute URLs must be enabled in Grav System Configuration.

### Generate Command

The generate command can be used from the command line or directly in the Grav Admin Panel. Generate your static site. `generate` can also be written as `gen` or `g`.

- **Input URL** (required) - Enter the URL to your live Grav site.

```bash
bin/plugin blackhole generate http://localhost/grav
```

#### Options

- **Output URL** `--output-url` or `-d`
  The URL of your static site. This determines the domain used in the absolute path of your links.

  ```bash
  --output-url https://website.com
  ```

- **Output Path** `--output-path` or `-p`
  The directory to which your static site will be written (relative to Grav root).

  ```bash
  --output-path ../build
  ```

- **Routes** `--routes` or `-r`
  Limit generation to a select list of page routes.

  ```bash
  --routes home,about,about/contact
  ```

- **Simultaneous Limit** `--simultaneous` or `-s`
  Determine how many files will generate at the same time (default: 10).

  ```bash
  --simultaneous 10
  ```

- **Assets** `--assets` or `-a`
  Copy assets to the output path.

- **Force** `--force` or `-f`
  Overwrite previously generated files.

## Author

<table>
  <thead>
    <tr>
      <th valign="middle" align="center">
        <a href="https://barrymode.com"><img alt="BarryMode" src="https://avatars3.githubusercontent.com/u/5648875?v=2&s=160" width="80" height="80"></a>
      </th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td valign="middle" align="center">
        <a href="https://barrymode.com"><strong>BarryMode</strong></a><br>
        <a href="https://www.youtube.com/barrymode"><img src="https://cdn.jsdelivr.net/npm/simple-icons@latest/icons/youtube.svg" width="16" height="16"></a> <a href="https://github.com/barrymode"><img src="https://cdn.jsdelivr.net/npm/simple-icons@latest/icons/github.svg" width="16" height="16"></a> <a href="https://twitter.com/barrymode"><img src="https://cdn.jsdelivr.net/npm/simple-icons@latest/icons/twitter.svg" width="16" height="16"></a><br>
        <a href="https://www.paypal.me/BarryMode"><img src="https://img.shields.io/badge/Give%20Coffee--00653b.svg?style=social&labelColor=fff&logo=data:image/png;base64,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"></a>
      </td>
    </tr>
  </tbody>
</table>
