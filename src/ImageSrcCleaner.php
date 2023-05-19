<?php

namespace hstanleycrow\EasyPHPArticleExtractor;

class ImageSrcCleaner
{
    public function clean(string $src): string
    {
        if ($this->hasWebpExt($src) !== FALSE) :
            $src = $this->cleanWebpExt($src);
        endif;
        $src = $this->cleanSrc($src);
        return $src;
    }
    private function hasWebpExt(string $src): bool
    {
        return stripos($src, ".jpg.webp");
    }
    private function cleanWebpExt(string $src): string
    {
        if (stripos($src, ".jpg.webp") !== FALSE) :
            $src = str_replace(".webp", "", $src);
        endif;
        #si la imagen termina en .png.webp
        if (stripos($src, ".png.webp") !== FALSE) :
            $src = str_replace(".webp", "", $src);
        endif;
        #si la imagen termina en .webp pero no tiene .png o .jpg antes
        if (stripos($src, ".webp") !== FALSE) :
            $src = str_replace(".webp", ".jpg", $src);
            if (!@getimagesize($src)) :
                $src = str_replace(".jpg", ".png", $src);
                if (!@getimagesize($src)) :
                    $src = "";
                endif;
            endif;
        endif;
        return $src;
    }
    private function cleanSrc(string $src): string
    {
        if (stripos($src, ".jpg") !== false) :
            list($src, $garbage) = explode(".jpg", $src);
            $src .=  ".jpg";
        endif;
        if (stripos($src, ".jpeg") !== false) :
            list($src, $garbage) = explode(".jpeg", $src);
            $src .=  ".jpeg";
        endif;
        #echo $src;die();
        if (stripos($src, ".png") !== false) :
            list($src, $garbage) = explode(".png", $src);
            $src .= ".png";
        #echo $src;#die();
        endif;
        return $src;
    }
}
