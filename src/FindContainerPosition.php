<?php

namespace hstanleycrow\EasyPHPArticleExtractor;

include_once('simple_html_dom.php');
class FindContainerPosition
{
    const TAGS_TO_IGNORE = ['script', "head", "noscript", "a", "strong", "em", "img", "form", "label"];
    private simple_html_dom $htmlContent;
    private string $itemSearch;
    private string $itemType;
    private bool $itemPositionAboveTheFold;

    public function __construct(simple_html_dom $htmlContent, $itemSearch, $itemType = "tag")
    {
        $this->htmlContent = $htmlContent;
        $this->itemSearch = $itemSearch;
        $this->itemType = $itemType;
        $this->itemPositionAboveTheFold = false;
        $this->find();
    }
    private function find(): bool
    {
        $itemPosition = 0;
        if ($this->htmlContent->find("body", 0)) :
            $htmlElementsInsideBody = $this->htmlContent->find("body", 0)->find('*');
            $htmlElementsInsideBodyTotal = 0;
            foreach ($htmlElementsInsideBody as $element) :
                if (!in_array($element->tag, self::TAGS_TO_IGNORE))
                    $htmlElementsInsideBodyTotal++;
            endforeach;
            foreach ($htmlElementsInsideBody as $element) :
                if (!in_array($element->tag, self::TAGS_TO_IGNORE)) :
                    if ($this->itemType == "tag") :
                        if ($element->tag == $this->itemSearch)
                            break;
                    endif;
                    if ($this->itemType == "id") :
                        if ($element->id == $this->itemSearch)
                            break;
                    endif;
                    if ($this->itemType == "class") :
                        if (stripos($element->class, $this->itemSearch) !== false)
                            break;
                    endif;
                    $itemPosition++;
                endif;
            endforeach;
            $this->itemPositionAboveTheFold = false;
            $heightPosition = round(($itemPosition / $htmlElementsInsideBodyTotal * 100), 0);
            if ($heightPosition <= 60)
                $this->itemPositionAboveTheFold = true;
        endif;
        return $this->itemPositionAboveTheFold;
    }
    public function isAboveTheFold(): bool
    {
        return $this->itemPositionAboveTheFold;
    }
}
