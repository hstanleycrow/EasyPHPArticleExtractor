<?php

namespace hstanleycrow\EasyPHPArticleExtractor;

include_once('simple_html_dom.php');
class ArticleTagCleaner
{
    private simple_html_dom $htmlContent;

    public function __construct(simple_html_dom $htmlContent)
    {
        $this->htmlContent = $htmlContent;
    }

    public function clean(): simple_html_dom
    {
        foreach (ArticleExtractorSetup::ELEMENTS_TO_CLEAN as $htmlElementToClear)
            $this->cleanHtmlElements($htmlElementToClear);
        $this->clearAuthorClass();
        $this->htmlContent->save();
        $this->htmlContent->load($this->htmlContent->save());
        return $this->htmlContent;
    }

    private function cleanHtmlElements(string $htmlElement): void
    {
        if ($this->htmlContent->find($htmlElement)) :
            foreach ($this->htmlContent->find($htmlElement) as $elementToClear) :
                $elementToClear->outertext = '';
            endforeach;
        endif;
    }

    private function clearAuthorClass(): void
    {
        if ($this->htmlContent->find("[class*='author']")) :
            foreach ($this->htmlContent->find("[class*='author']") as $elementToClear) :
                if ($elementToClear->tag <> "body" && $elementToClear->tag <> "main" && $elementToClear->tag <> "article")
                    $elementToClear->outertext = '';
            endforeach;
        endif;
    }
}
