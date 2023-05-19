<?php

namespace hstanleycrow\EasyPHPArticleExtractor;

class ArticleMainContainerExtractor
{
    private simple_html_dom $htmlContent;
    private string $plainText;
    private string $htmlContentContainer;

    public function __construct()
    {
    }
    public function extract(simple_html_dom $htmlContent, string $plainText)
    {
        $this->htmlContent = $htmlContent;
        $this->plainText = $plainText;
        /**
         * Si todo el texto se extrae en una sola linea entonces partimos dicha linea usando el "." como delimitador y asi obtener pequeños paragraphs.
         * Luego de cada paragraph se toma una porcion de unos 50 caracteres del segundo paragraph, que sera el texto a usar para buscar los contenedores.
         * Si el texto tiene varias lineas, primero se evalua que la linea tenga mas de 200 caracteres y si los tiene entonces se separa por el "." para obtener paragraphs mas pequeños. 
         * Luego de cada paragraph se toma una porcion de unos 50 caracteres del segundo paragraph, que sera el texto a usar para buscar los contenedores.
         * */

        $chosenTexts = (new ExtractSampleText($this->plainText))->extract();
        if ($this->htmlContentHasArticleTagOnTop()) :
            $this->htmlContentContainer = "article";
        else :
            $this->htmlContentContainer = (new ExtractContainerBySampleText($this->htmlContent, $chosenTexts))->extract();
        endif;
        $this->htmlContentContainer = preg_replace('/[[:^print:]]/', '', $this->htmlContentContainer);
        return $this->htmlContentContainer;
    }

    private function htmlContentHasArticleTagOnTop(): bool
    {
        return ((count($this->htmlContent->find('article')) == 1)
            && (new FindContainerPosition($this->htmlContent, 'article', "tag"))->isAboveTheFold());
    }
}
