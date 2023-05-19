<?php

namespace hstanleycrow\EasyPHPArticleExtractor;

class ExtractContainerBySampleText
{
    private simple_html_dom $htmlContent;
    private array $sampleTexts;

    public function __construct(simple_html_dom $htmlContent, array $sampleTexts)
    {
        $this->htmlContent = $htmlContent;
        $this->sampleTexts = $sampleTexts;
    }

    public function extract(): string
    {
        $htmlElements = $this->htmlContent->find('*');
        /**
         * Voy a hacer un loop para pasar por varios de ls textos elegidos, y solo cuando el contenedor coincida con al menos 2 textos, se va a elegir
         * */
        $htmlContentContainer = "";
        $htmlContentContainer1 = 1;
        $htmlContentContainer2 = 2;
        $total_chosenTexts = count($this->sampleTexts);
        $evaluatedTexts = 0;
        while (($htmlContentContainer1 <> $htmlContentContainer2) && $evaluatedTexts < $total_chosenTexts) :
            $postText = $this->sampleTexts[$evaluatedTexts];
            $itemFound = false;
            #$postText = $postText; # dejar este si se quiere usar el texto extraido
            $foundHtmlTag = "";
            $foundElementID = "";
            $lastElementID = "";
            $foundCSSClass = "";
            foreach ($htmlElements as $htmlElement) :
                if (in_array($htmlElement->tag, ArticleExtractorSetup::CONSIDERED_TAGS)) :
                    $htmlElement->plaintext = TextCleaner::cleanPlainText($htmlElement->plaintext);
                    if ($this->sampleTextInHtmlElementText($htmlElement->plaintext, $postText)) :
                        $foundHtmlTag = $htmlElement->tag;
                        $foundElementID = $htmlElement->id;
                        $foundCSSClass = $htmlElement->class;
                        if ($this->hasCssClasses($cssClasses = explode(" ", $foundCSSClass))) :
                            $htmlContentContainer = $this->configCssClass($htmlContentContainer, $cssClasses);
                            $itemFound = true;
                            foreach ($cssClasses as $cssClass) :
                                if ($this->classIsInImportantClasses($cssClass)) :
                                    $htmlContentContainer = "." . $cssClass;
                                    $lastElementID = ""; # Si se encuentra uno de los contenedores importantes, se limpia la variable ultimo_id para evitar que sea este el contenedores que tome;
                                    break;
                                endif;
                            endforeach;
                        else :
                            if ($this->classIsInImportantClasses($foundCSSClass)) :
                                $itemFound = true;
                                $htmlContentContainer = "." . $foundCSSClass;
                                break;
                            endif;
                        endif;
                        if ($foundElementID <> "") :
                            $lastElementID = $foundElementID;
                            $itemFound = true;
                        endif;
                    endif;
                endif;
                if ($itemFound)
                    break; # Si ya se encontro el oontenedor con el texto se sale del loop
            endforeach;

            if ($this->containerNotFound($htmlContentContainer, $lastElementID, $foundHtmlTag)) :
                $htmlContentContainer = $this->chooseContainer($htmlContentContainer, $lastElementID, $foundCSSClass);
            endif;
            if ($htmlContentContainer1 == 1 && !empty($htmlContentContainer)) :
                $htmlContentContainer1 = $htmlContentContainer;
            else :
                if ($htmlContentContainer2 == 2 && !empty($htmlContentContainer)) :
                    $htmlContentContainer2 = $htmlContentContainer;
                endif;
            endif;
            $evaluatedTexts++;
        endwhile;
        return $htmlContentContainer;
    }

    private function sampleTextInHtmlElementText(string $htmlText, string $evaluatedText): bool
    {
        return (mb_stripos(trim($htmlText), trim($evaluatedText)) !== false);
    }
    private function hasCssClasses(array $cssClasses): bool
    {
        return count($cssClasses) > 1;
    }
    private function configCssClass(string $htmlContentContainer, array $cssClasses): string
    {
        $htmlContentContainer = "." . implode(" ", $cssClasses);
        $htmlContentContainer = str_replace(" ", ".", $htmlContentContainer);
        return $htmlContentContainer;
    }
    private function classIsInImportantClasses(string $cssClass): bool
    {
        return (in_array($cssClass, ArticleExtractorSetup::IMPORTANT_CLASSES));
    }
    private function containerNotFound(string $htmlContentContainer, string $lastElementID, string $foundHtmlTag): bool
    {
        return (($htmlContentContainer == "" || $lastElementID <> "") && $foundHtmlTag <> 'article');
    }
    private function chooseContainer(string $htmlContentContainer, string $lastElementID, string $foundCSSClass): string
    {
        if ($lastElementID <> "") :
            $htmlContentContainer = "#" . $lastElementID;
        else :
            if ($foundCSSClass <> "") :
                if (count($cssClasses = explode(" ", $foundCSSClass)) > 1) :
                    $htmlContentContainer = "." . $cssClasses[0];
                else :
                    $htmlContentContainer = "." . $foundCSSClass;
                endif;
            endif;
        endif;
        return $htmlContentContainer;
    }
}
