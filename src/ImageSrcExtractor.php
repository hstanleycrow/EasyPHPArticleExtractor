<?php

namespace hstanleycrow\EasyPHPArticleExtractor;

class ImageSrcExtractor
{
    private string $url;

    public function get(string $src, simple_html_dom $img, string $url): string
    {
        $this->url = $url;
        if ($this->textHasQuestionMark($src))
            $src = $this->cleanQuestionMarkOnURL($src);
        $src = $this->fixRelativeImageURL($src);
        if ($this->isInvalidFormat($src, $img)) :
            $src = $this->clearSrc();
        endif;
        if ($this->isEmptySrc($src)) :
            $src = (new DeepImageSrcExtractor())->get($img); // si no encuentra el SRC, se hace una busqueda mas profunda
        endif;
        $src = (new ImageSrcCleaner())->clean($src);
        return $src;
    }
    private function textHasQuestionMark(string $text): bool
    {
        return strpos($text, "?");
    }
    private function cleanQuestionMarkOnURL(string $src): string
    {
        list($src, $garbage) = explode("?", $src);
        unset($garbage);
        return $src;
    }
    private function fixRelativeImageURL(string $src): string
    {
        $urlScheme = parse_url($this->url, PHP_URL_SCHEME);
        $urlHost = parse_url($this->url, PHP_URL_HOST);
        $imgHost = parse_url($src, PHP_URL_HOST);
        if (($urlHost <> $imgHost) && $imgHost == "")
            $src = $urlScheme . "://" . $urlHost . str_replace(" ", "%20", $src);
        return $src;
    }
    private function isInvalidFormat(string $src, $img): bool
    {
        return ((stripos($src, ".svg") !== FALSE)
            || (stripos($src, ".file") !== FALSE)
            || (stripos($src, "data:")  !== false))
            || (((stripos($src, ".jpg")  === false)
                && (stripos($src, ".jpeg")  === false)
                && (stripos($src, ".png")  === false)
                && (stripos($src, ".webp")  === false))
                || !empty($img->{'srcset'}));
    }
    private function clearSrc(): string
    {
        return "";
    }
    private function isEmptySrc(string $src): bool
    {
        return empty($src);
    }
}
