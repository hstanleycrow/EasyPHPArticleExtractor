<?php

namespace hstanleycrow\EasyPHPArticleExtractor;

class PostLinksCleaner extends AbstractProcessArticleContent
{
    private string $url;
    public function __construct(simple_html_dom_node $mainContent, string $url)
    {
        parent::__construct($mainContent);
        $this->url = $url;
    }

    public function execute(): simple_html_dom_node
    {
        if ($this->mainContent->find('a')) :
            $i = 0;
            foreach ($this->mainContent->find('a') as $a) :
                #Si el enlace tiene una imagen dentro, se deja solo la imagen y se remueve el enlace
                if ($a->find('img')) :
                    $newElement = $a->find('img', 0);
                    $a = $newElement;
                    $this->mainContent->find('a')[$i]->outertext = $newElement;
                else :
                    $urlHost = parse_url($a->href, PHP_URL_HOST);
                    $urlPost = parse_url($this->url, PHP_URL_HOST);
                    if (empty($urlHost)) :
                        $this->mainContent->find('a', $i)->outertext = $this->mainContent->find('a', $i)->plaintext;
                    else :
                        if ($urlPost == $urlHost)
                            $this->mainContent->find('a', $i)->outertext = $this->mainContent->find('a', $i)->plaintext;
                    endif;
                    if ($a->find('svg')) :
                        foreach ($a->find('svg') as $svg) :
                            $svg->outertext = '';
                        endforeach;
                    endif;
                endif;
                $i++;
            endforeach;
        endif;
        $this->mainContent->save();
        #$this->mainContent->load($this->mainContent->save());
        return $this->mainContent;
    }
}
