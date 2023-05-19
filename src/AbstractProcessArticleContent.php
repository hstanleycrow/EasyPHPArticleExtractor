<?php

namespace hstanleycrow\EasyPHPArticleExtractor;

class AbstractProcessArticleContent
{
    protected simple_html_dom_node $mainContent;

    public function __construct(simple_html_dom_node $mainContent)
    {
        $this->mainContent = $mainContent;
    }
}
