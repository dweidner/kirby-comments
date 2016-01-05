# Kirby Comments (beta)

[Kirby](https://github.com/getkirby/kirby) is a wonderful Content Management System which is entirely file-based. [Write](http://getkirby.com/docs/content/text) a blog post in your favourite Markdown editor, [add](http://getkirby.com/docs/content/adding-content#fields) some field data and [save](http://getkirby.com/docs/content/adding-content#a-folder-for-every-page) it to the file system. Done!

While Kirby excels in many disciplines it is lacking an integrated solution for user-generated comments. This **plugin** tries to fill this gap. It uses the database capabilities of the [Kirby Toolkit](https://github.com/getkirby/toolkit) which builds the foundation of the entire system.

**Overview of implemented features (so far):**

- Use a MySQL or SQLite database to save your comments
- A simple installation wizard helps you getting started
- Allow your users to use Markdown in their comments
- Fully customizable templates
- Comment fields leverage Kirby‘s expressive Field API (e.g. `$comment->authorEmail()->obfuscate()`)
- Easy and customizable comment form validation
- Spam protection (Request Throttling, Honeypot, Required Reading Time)
- Basic Access Management via Roles and Capabilities
- Support for threaded comments
- Optional Akismet integration
- Optional Panel integration via custom field

## Installation

You can fetch the latest version of the plugin as [direct download](https://github.com/dweidner/kirby-comments/archive/master.zip) from Github or via git using the command line.

### Requirements
Kirby runs on PHP 5.3+, Apache or Nginx. Due to bugs in the implementation of the Kirby Toolkit  this plugin requires Kirby 2.1 or newer.

### Download the Plugin

**Using Git**:
Probably the most comfortable way to add the plugin to your current project is using *Git*.

```sh
git submodule add https://github.com/dweidner/kirby-comments.git site/plugins/comments
git submodule update --init --recursive
```

**Manual Download (Alternative)**:
If you want avoid using the command line you can grap and unzip the archive from the following url https://github.com/dweidner/kirby-comments/archive/master.zip. Once done, simply move the folder to the `site/plugins` folder of your project and continue with the installation wizard.

### Run the Installation Wizard
The Kirby Comments plugin ships with a custom installation wizard to make the installation process easy as a pie. Open your favourite browser and visit the the following url: https://example-blog.com/plugins/comments/install. The wizard will help you to setup the required database, import existing comments from a CVS file and install the custom field.

## Usage
Once you have finished the installation process you can use the plugin in your theme. Just add the following lines at the bottom of your template (e.g. in `sites/templates/article.php`):

```php
// Excerpt of site/templates/article.php
<article role="article">
	<h1><?php echo $page->title()->html(); ?></h1>
	...
	<footer>
		<?php commentForm(); ?>
		<?php comments(); ?>
	</footer>
</article>
```

You are free to customize the generated markup of your comments. Just create a file called `comment.php` in `site/snippets/comments`. Have a look into the default template used at [`site/plugins/comments/resources/snippets`](https://github.com/dweidner/kirby-comments/blob/master/resources/snippets/comment.php):

## Issues and Support
If you have any problems running this plugin, please [open an issue](https://github.com/dweidner/kirby-comments/issues/new) on Github or contact me directly via [email](http://danielweidner.de/kontakt/). You can also find me on Twitter ([@danielweidner](https://twitter.com/danielweidner) if that is your communication platform of choice.

## Contributing
Have you found one of those nasty bugs slumbering in this plugin? Just fork the project and create a [pull requests](https://github.com/dweider/kirby-comments/compare/) and I will be happy to integrate you contribution into the project.

If you have a great idea for new features but not the time to fork and implement it yourself, just drop me a line or [open an issue](https://github.com/dweidner/kirby-comments/issues/new) here on Github.

## FAQ:
> **“Why is the plugin not using a file-based approach?”**

Well, a totaly valid question. First of all using the database capabilities shipping with Kirby seems to be the most reasonable solution as it applies functionality already available to plugin developers. At the time of writing Kirby did not provide a convenient way to save, read and query custom markdown-formatted contents next to pages.

I have been thinking of alternative ways, like a JSON-formatted file which combining comments of a certain page into a single page (see @vladstudio’s [solution](https://github.com/vladstudio/vladstudio-kirby-comments)), but always returned to a database driven approach as it was way easier to implement. If you have a good idea about how to implement a clean solution I will be very happy to add this as a further feature to the plugin.

# License
[MIT License](http://www.opensource.org/licenses/mit-license.php), 2015 [Daniel Weidner](http://danielweidner.de)
