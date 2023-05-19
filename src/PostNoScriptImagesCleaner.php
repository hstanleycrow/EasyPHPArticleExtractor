<?php

namespace hstanleycrow\EasyPHPArticleExtractor;

class PostNoScriptImagesCleaner extends AbstractProcessArticleContent
{
    public function __construct(simple_html_dom_node $mainContent)
    {
        parent::__construct($mainContent);
    }

    public function execute(): simple_html_dom_node
    {
        if ($this->mainContent->find('noscript')) :
            $i = 0;
            foreach ($this->mainContent->find('noscript') as $tmphtml) :
                if ($tmphtml->find('img')) :
                    $tmphtml->outertext = $tmphtml->find('img', 0)->outertext;
                endif;
            endforeach;
        endif;
        #$this->mainContent->load($this->mainContent->save());
        return $this->mainContent;
    }
}
