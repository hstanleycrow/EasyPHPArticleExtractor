<?php

namespace hstanleycrow\EasyPHPArticleExtractor;

#use simple_html_dom_node;

class ProcessArticleContent extends AbstractProcessArticleContent
{
    private bool $removeInternalLinks;
    private string $url;

    public function __construct(simple_html_dom_node $mainContent, bool $removeInternalLinks = true, string $url = "")
    {
        parent::__construct($mainContent);
        if ($removeInternalLinks && empty($url))
            new \Exception("If you want to remove internal links, you must to call this class with the URL parameter");
        $this->removeInternalLinks = $removeInternalLinks;
        $this->url = $url;
    }

    public function process(): string
    {
        $this->mainContent = (new PostNoScriptImagesCleaner($this->mainContent))->execute();
        $this->mainContent = (new PostTweetSeparator($this->mainContent))->execute();
        if ($this->removeInternalLinks)
            $this->mainContent = (new PostLinksCleaner($this->mainContent, $this->url))->execute();
        $this->mainContent = (new ProcessVideoContent($this->mainContent))->execute();
        $this->mainContent = (new ProcessImageContent($this->mainContent, $this->url))->execute();
        #$this->mainContent->load($this->mainContent->save());
        return $this->mainContent->innertext;
    }
}
