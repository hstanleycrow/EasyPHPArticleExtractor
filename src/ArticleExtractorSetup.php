<?php

namespace hstanleycrow\EasyPHPArticleExtractor;

class ArticleExtractorSetup
{
    const PARAGRAPH_MIN_LENGHT = 100;
    const BLOCK_MIN_LENGHT = 50;
    const MIN_LINES_TO_READ = 5;
    const MIN_TEXT_LINE_TO_READ = 100;
    const IGNORED_TAGS = ["body", "p", "html", "ul", "li", "script", "link", "a", "h1", "h2", "h3", "h4", "h5", "h6", "footer", "img", "pre", "strong", "figure", "code", "head", "meta", "noscript", "iframe", "span", "i", "form", "label"];
    const CONSIDERED_TAGS = ["div", "section", "article"];
    const IMPORTANT_CLASSES = ['entry-content', 'post-entry', 'blog-post-content', 'post-content', 'article-body', 'post', 'blog-content'];
    const ELEMENTS_TO_CLEAN = ["style", "svg", "footer", "aside", "comment", "noscript", "unknown", "figcaption", "nav", '.pagenav', '#pagenav', "[class*='navbar']", "[class*='topnav']", 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'ul', 'ol', 'li', 'input', 'select', 'button', '.elementor-widget-alert', "[class*='tooltip']", "[id='sidebar']", "[id='disqus_thread']", 'div[style=display:none;]', 'div[style=display: none;]', "[class*='social']", "[class*='aside']", '.published', '.entry-date', "[id*='breadcrumb']", "[class*='breadcrumb']", '.author-block', '.sr-only', '.socialite-widget'];
    const TWEET_CONTAINER = 'blockquote.twitter-tweet';
}
