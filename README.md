<h1 align="center">
  <br>
   Easy PHP Article Extractor
  <br>
</h1>
<p>This is a post article and news posts extractor library for PHP. This library detects where is the content in the HTML and reads the article content from the page, keeping all the useful HTML, suitable for translate and publish in other languages.

You can extract only the text too. The library can remove all the internal links to avoid those links into the content. It process the images too to remove the links. Talking about images, the library extract the images in all the HTML tags supported and ways to add one image into the content of the posts.

The library detects Youtube videos too to inject the URL of the video in the right place of the content, avoiding the manual action for this, and do the same with inserted Tweets.

For a project I have developed, I found many existing open source solutions, but each had unique failures for my project. You can use this libray with another library of mine for translate using the Google Translator API: <a href="https://github.com/hstanleycrow/EasyPHPGoogleTranslate" target="_blank">EasyPHPGoogleTranslate</a>.

Another use for this library is combinating it with another of my libraries that publish content from PHP to Wordpress: <a href="https://github.com/hstanleycrow/EasyPHPToWordpress" target="_blank">EasyPHPToWordpress</a>. In that way, you can extract, translate and publish to Wordpress. I have developed another library: <a href="https://github.com/hstanleycrow/EasyPHPOpenAI" target="_blank">EasyPHPOpenAI</a>, where you can use it to use OpenAI API into the content extracted.
</p>

<h4 align="center">Free PHP library to extract the main content from an article post or news post, including images and HTML</h4>

<p align="center">
  <a href="#how-to-use">How To Use</a> •
  <a href="#download">Download</a> •
  <a href="#license">License</a>
</p>


## How To Use

```bash
# Clone this repository
$ git clone https://github.com/hstanleycrow/EasyPHPArticleExtractor/

# install libraries
$ composer update
```
or 
```bash
# Install using composer
$ composer require hstanleycrow/easyphparticleextractor

### Using Examples
You only need to create an instance of the main class with the URL with the content to extract and you will to obtain the content with the HTML, in plain text and the title of the article.
PD: I use the library to extract the content with HTML, so the plain text is not my priority. In the other hand, the detection of the main content is very hard, so, sometimes it can extract weird content with the main post, but this library was developed to use the extracted content with an text editor, so, extract some garbage is not a problem for me, because in the editor the user can clean the content.

```php
$url = 'https://nftplazas.com/zed-run-airdrop/';
$articleExtractor = new ArticleExtractor($url);
$article = $articleExtractor->article();
$title = $articleExtractor->title();
$plaintext = $articleExtractor->plainText();

$url = 'https://www.seroundtable.com/google-search-algorithm-ranking-volatility-35414.html';
$article = $articleExtractor->article();
echo $articleExtractor->title() . PHP_EO

```

## Download

You can [download](https://github.com/hstanleycrow/EasyPHPArticleExtractor/) the latest version here.

## PHP Versions
I have tested this class only in this PHP versions. So, if you have an older version and do not work, let me know.
| PHP Version |
| ------------- |
| PHP 8.0 | 
| PHP 8.1 |
| PHP 8.2 |

## Support

<a href="https://www.buymeacoffee.com/haroldcrow" target="_blank"><img src="https://www.buymeacoffee.com/assets/img/custom_images/purple_img.png" alt="Buy Me A Coffee" style="height: 41px !important;width: 174px !important;box-shadow: 0px 3px 2px 0px rgba(190, 190, 190, 0.5) !important;-webkit-box-shadow: 0px 3px 2px 0px rgba(190, 190, 190, 0.5) !important;" ></a>

## License

MIT

---

> [www.hablemosdeseo.net](https://www.hablemosdeseo.net) &nbsp;&middot;&nbsp;
> GitHub [@hstanleycrow](https://github.com/hstanleycrow) &nbsp;&middot;&nbsp;
> Twitter [@harold_crow](https://twitter.com/harold_crow)

