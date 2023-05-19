<?php

namespace hstanleycrow\EasyPHPArticleExtractor;

class ProcessImageContent extends AbstractProcessArticleContent
{
    private string $url;
    private bool $hasNonFreeImages;
    private array $imageList;

    public function __construct(simple_html_dom_node $mainContent, string $url)
    {
        parent::__construct($mainContent);
        $this->url = $url;
    }

    public function execute(): simple_html_dom_node
    {
        if ($this->mainContentHasImages()) :
            $i = 0;
            $recopiledImageList = array(); # con este array verificamos que las imagenes no se dupliquen
            foreach ($this->mainContent->find('img') as $img) :
                $src = $this->mainContent->find('img', $i)->src;
                #$src = $this->findRealSrc($src, $img);
                $src = (new ImageSrcExtractor())->get($src, $img, $this->url);
                $altText = $this->mainContent->find('img', $i)->alt;
                if (!in_array($src, $recopiledImageList)) :
                    if ($this->isValidImageToList($src)) :
                        array_push($recopiledImageList, $src);
                        $newImg = '<p><img src="' . $src . '" alt="' . $altText . '"></p>';
                        if ($img->parent->tag == "a") :
                            $img->parent->outertext = $newImg;
                        else :
                            $this->mainContent->find('img', $i)->outertext = $newImg;
                            $this->hasNonFreeImages = false;
                        endif;
                    else :
                        $this->mainContent->find('img', $i)->outertext = "";
                        $this->hasNonFreeImages = true;
                    endif;
                endif;
                $i++;
            endforeach;
            #echo "<pre>"; print_r($recopiledImageList);die();
            $this->imageList = $recopiledImageList;
        #$this->featuredImage = $this::_getImage($html); // 170523 lo esetoy quitando por que esta funcionalidad debe ir en otra parte
        #$this->mainContent->save();
        #$html->save();
        endif;
        return $this->mainContent;
    }
    private function mainContentHasImages(): bool
    {
        return (bool)($this->mainContent->find('image'));
    }

    private function isValidImageToList(string $src): bool
    {
        return ((stripos($src, ".jpg") !== false) || (stripos($src, ".jpeg") !== false) || (stripos($src, ".png") !== false) && $this->isFreeImage($src));
    }

    private function isFreeImage(string $src): bool
    {
        $default_opts = array(
            'http' => array(
                'method' => "GET",
                'header' => "Referer: http://www.fakesite.com/hotlink-check/",
                'user_agent' =>    $_SERVER['HTTP_USER_AGENT']
            )
        );
        stream_context_set_default($default_opts);
        $headers = get_headers($src, 1);
        return (preg_match('/200 OK$/', $headers[0]));
    }

    /**
     * Get the value of hasNonFreeImages
     */
    public function hasNonFreeImages()
    {
        return $this->hasNonFreeImages;
    }

    /**
     * Get the value of imageList
     */
    public function imageList()
    {
        return $this->imageList;
    }
}
