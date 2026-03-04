# Custom Citation

## Description

Plugin for Omeka Classic. Replaces the default item citation with a formatted citation in one of the following styles:

- **APA** — Author-date style by the American Psychological Association
- **Chicago / Turabian** — Style by the University of Chicago Press
- **Harvard** — Author-date style widely used in UK universities
- **IEEE** — Numbered reference style by the Institute of Electrical and Electronics Engineers
- **MLA** — Style by the Modern Language Association
- **Wikipedia** — Chicago-based style used in Wikipedia references

All styles include the item URL and the date of access. An optional setting allows the collection title to be appended to the citation.

## Features

- Robust name parsing supporting both "Last, First" and "First Last" input formats
- Nobility particles and prepositions handled correctly for Italian, English, French, Spanish, Arabic, German and Dutch names (e.g. *van*, *de*, *al-*, *von*, *della*)
- Name suffixes (Jr., Sr., II, III…) preserved correctly
- Per-style rules for multiple authors (et al., initials, ampersand, etc.)
- ISBN / ISSN automatically included when available (Wikipedia style)
- All interface strings translatable via Omeka's standard `__()` system
- Configuration page with per-style description and collapsible formatted example

## Citation format examples

All examples use: Lazaro Ludoviko Zamenhof, *Unua Libro*, Kelter, 1887.

| Style | Example |
|---|---|
| APA | Zamenhof, L. L. (1887). *Unua Libro*. Kelter. Retrieved March 4, 2026, from http://example.com |
| Chicago | Zamenhof, Lazaro Ludoviko. *Unua Libro*. Kelter, 1887. Accessed March 4, 2026. http://example.com |
| Harvard | Zamenhof, L. L. (1887) *Unua Libro*. Kelter. Available at: http://example.com (Accessed: 4 March 2026) |
| IEEE | [1] L. L. Zamenhof, *Unua Libro*, Kelter, 1887. Accessed: Mar. 4, 2026. [Online]. Available: http://example.com |
| MLA | Zamenhof, Lazaro Ludoviko. *Unua Libro*. Kelter, 1887. Accessed 4 Mar. 2026. http://example.com |
| Wikipedia | Zamenhof Lazaro Ludoviko (1887), *Unua Libro*, Kelter, accessed March 4th, 2026, http://example.com |

## Installation

1. Download and uncompress the archive
2. Rename the folder `CustomCitation`
3. Move it into your Omeka `plugins/` directory
4. Log in to your Omeka admin panel and go to **Plugins**
5. Click **Install** next to Custom Citation
6. Configure the plugin under **Plugins → Custom Citation → Configure**

## Configuration

| Option | Description |
|---|---|
| Citation style | Choose one of the seven available styles (including Omeka default) |
| Add Collection | If checked, the collection title is appended to the citation before the URL |

## Requirements

- Omeka Classic 3.1 or higher

## Troubleshooting

Please report issues on the [GitHub issues page](https://github.com/DBinaghi/CustomCitation/issues).

## Credit

Original idea by [@ebellempire](https://github.com/ebellempire), with [AstrodomeCitations](https://github.com/ebellempire/AstrodomeCitations).

## License

This plugin is published under the [GNU/GPL v3](https://www.gnu.org/licenses/gpl-3.0.html), approved by [FSF](https://www.fsf.org/) and [OSI](http://opensource.org/).

## Copyright

Copyright [Daniele Binaghi](https://github.com/DBinaghi), 2018–2026
