<?php

namespace hstanleycrow\EasyPHPArticleExtractor;

include_once('simple_html_dom.php');
class ArticlePlainTextExtractor
{
    private simple_html_dom $htmlContent;

    public function __construct(simple_html_dom $htmlContent)
    {
        $this->htmlContent = $htmlContent;
    }

    public function extract(): string
    {
        $htmlElements = $this->htmlContent->find('body', 0);
        $paragraphs = "";
        $total_paragraphs_found = 0;
        foreach ($htmlElements as $htmlElement) :
            if ($this->isValidParagraph($htmlElement)) :
                $paragraphs = preg_replace('/\s\s+/', " ", $htmlElement->plaintext);
                $paragraphs .= trim(preg_replace('/[[:^print:]]\"/', "", $paragraphs)) . "<br/>";
                $total_paragraphs_found++;
            endif;
            if ($total_paragraphs_found >= 3) break;
        endforeach;
        return nl2br($paragraphs);
    }
    private function isValidParagraph(mixed $htmlElement): bool
    {
        return (isset($htmlElement->plaintext) && $htmlElement->plaintext <> null && $this->isParagraph($htmlElement->plaintext));
    }
    private function isParagraph(string $htmlElementText)
    {
        return ((strlen($htmlElementText) >= ArticleExtractorSetup::PARAGRAPH_MIN_LENGHT) && (stripos($htmlElementText, ".") !== false) && (count(explode(".", $htmlElementText)) > 1));
    }
}
