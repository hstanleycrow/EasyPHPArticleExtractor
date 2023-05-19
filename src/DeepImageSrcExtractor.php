<?php

namespace hstanleycrow\EasyPHPArticleExtractor;

class DeepImageSrcExtractor
{
    const SIMPLE_PROPERTIES_LIST = [
        'nitro-lazy-src',
        'data-dt-lazy-src',
        'data-ezsrc',
        'data-pin-media',
        'data-img-url',
    ];
    const COMPLEX_PROPERTIES_LIST = [
        'data-srcset',
        'data-src',
    ];

    private bool $imageFound;

    public function get(simple_html_dom $image): string
    {
        $this->imageFound = false;
        $src = "";
        $src = $this->findWithSimplePropertiesList($image);
        if (!$this->imageFound)
            $src = $this->findWithComplexPropertiesList($image);
        if (!$this->imageFound)
            $src = $this->findWithSrcSet($image);
        return $src;
    }

    private function findWithSimplePropertiesList(simple_html_dom $image): ?string
    {
        $src = "";
        foreach (self::SIMPLE_PROPERTIES_LIST as $property) :
            $src = $image->{$property};
            if (!empty($src)) :
                $this->imageFound = true;
                break;
            endif;
        endforeach;
        return $src;
    }
    private function findWithComplexPropertiesList(simple_html_dom $image): ?string
    {
        $src = "";
        foreach (self::COMPLEX_PROPERTIES_LIST as $property) :
            $src = $image->{$property};
            if (stripos($src, " "))
                list($src, $garbage) = explode(" ", $src);
            if (!empty($src)) :
                $this->imageFound = true;
                break;
            endif;
        endforeach;
        return $src;
    }
    private function findWithSrcSet(simple_html_dom $image): ?string
    {
        $src = $image->{'srcset'}; //srcset has many scr for each different device size
        $srcList = array();
        if (stripos($src, " "))
            $srcList = explode(" ", $src);
        $pos = 0;
        foreach ($srcList as $temp) :
            if (stripos($temp, "://") === false)
                unset($srcList[$pos]);
            $pos++;
        endforeach;
        $src = array_pop($srcList);
        if (!empty($src)) :
            $this->imageFound = true;
            return $src;
        endif;
        return "";
    }
}
