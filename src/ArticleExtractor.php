<?php

namespace hstanleycrow\EasyPHPArticleExtractor;

use hstanleycrow\EasyPHPArticleExtractor\ArticleMainContainerExtractor;

include_once('simple_html_dom.php');
class ArticleExtractor
{
    #const LARGO_PARRAFO = 100;
    private string $url;
    private bool $removeInternalLinks;
    private simple_html_dom $htmlContent;
    private $postTitle;
    private $plainText;
    private $postContainer;
    private $mainContent;


    public function __construct(string $url, bool $removeInternalLinks = true, ?string $postContainer = "")
    {
        $this->url = $url;
        $this->removeInternalLinks = $removeInternalLinks;
        $this->postContainer = $postContainer ?? "";
    }

    public function article(): string
    {
        if ($this->simpleHtmlDomInit() !== false) :
            $this->postTitle = (new ArticleTitleExtractor($this->htmlContent))->extract();
            $this->htmlContent = (new ArticleTagCleaner($this->htmlContent))->clean();
            $this->plainText = (new ArticlePlainTextExtractor($this->htmlContent))->extract();
            if (empty($this->postContainer))
                $this->postContainer = (new ArticleMainContainerExtractor())->extract($this->htmlContent, $this->plainText);
            #return $this->postContainer;
            // obtenemos el articulo a partir del contenedor 
            $this->mainContent = $this->htmlContent->find($this->postContainer, 0);
            try {
                $article = (new ProcessArticleContent($this->mainContent, $this->removeInternalLinks, $this->url))->process();
                return $article;
            } catch (\Exception $e) {
                return $e->getMessage();
            }
        endif;
        return "";
    }

    private function simpleHtmlDomInit(): bool
    {
        ini_set('user_agent', $_SERVER['HTTP_USER_AGENT']);
        $this->htmlContent = new simple_html_dom();
        #die('antes de extraer');
        $simpleHtmlDomOptions = array(
            'http' => array(
                'method' =>   "GET",
                'header' =>    "Accept-language: es-SV,en-US;q=0.7,en;q=0.3\r\n" .
                    "Cookie: tms_VisitorID=475i1zn25v\r\n",
                'user_agent' =>    $_SERVER['HTTP_USER_AGENT']
            )
        );
        $context = stream_context_create($simpleHtmlDomOptions);
        $this->htmlContent->load_file($this->url, false, $context);
        return ($this->htmlContent === false) ? false : true;
    }
    public function title(): string
    {
        return $this->postTitle;
    }
    public function plainText(): string
    {
        return $this->plainText;
    }
}
