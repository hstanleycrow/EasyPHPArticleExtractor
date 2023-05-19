<?php

namespace hstanleycrow\EasyPHPArticleExtractor;

class TextCleaner
{
    public static function cleanPlainText(string $plainText): string
    {
        $plainText = trim($plainText);
        $plainText = str_replace("&nbsp;", " ", $plainText);
        $plainText = str_replace("&#039;", "'", $plainText);
        $plainText = str_replace("&rsquo;", "'", $plainText);
        $plainText = preg_replace('/[[:^print:]]\"/', '', $plainText);
        return $plainText;
    }
    public static function cleanHTMLText(string $htmlText): string
    {
        $htmlText = htmlentities($htmlText, ENT_QUOTES, "UTF-8");
        $htmlText = str_replace("&nbsp;", " ", $htmlText);
        $htmlText = str_replace("&#039;", "'", $htmlText);
        $htmlText = str_replace("&rsquo;", "'", $htmlText);
        return $htmlText;
    }
}
