<?php

namespace hstanleycrow\EasyPHPArticleExtractor;

include_once('simple_html_dom.php');
class ArticleTitleExtractor
{
    const LEVENSHTEIN_MIN_VALUE = 100;
    private simple_html_dom $htmlContent;

    public function __construct(simple_html_dom $htmlContent)
    {
        $this->htmlContent = $htmlContent;
    }

    public function extract(): string
    {
        $metaTitle = $this->htmlContent->find('title', 0)->plaintext;
        if ($this->contentHasH1()) :
            if ($this->contentTotalOfH1() == 1) :
                $chosenTitle = $this->htmlContent->find('h1', 0)->plaintext;
            else :
                $chosenTitle = $this->findTheRightH1($metaTitle);
            endif;
        else :
            # si la pagina no tiene H1, se elige el meta title como titulo
            $chosenTitle = $metaTitle;
        endif;
        return $chosenTitle;
    }
    private function contentHasH1(): bool
    {
        return (bool)$this->htmlContent->find('h1');
    }
    private function contentTotalOfH1(): int
    {
        return count($this->htmlContent->find('h1'));
    }
    /**
     * Para encontrar el h1 cuando hay mas de un H1, se hacen calculos matematicos para verificar cual H1 es mas parecido al meta title, ya que por lo general el meta title y el H1 son iguales o muy parecidos.
     */
    private function findTheRightH1(string $metaTitle): string
    {
        $higherSimilarTextPercent = 0;
        $chosenTitle = "";
        foreach ($this->htmlContent->find('h1') as $h1_obj) :
            $h1 = $h1_obj->plaintext;
            similar_text($metaTitle, $h1, $similarTextPercent);
            $levenshteinDistance = levenshtein($metaTitle, $h1);
            # mienrtas menor sea la distancia levenshtein y mayor el porcentaje de similar_text, ese es el H1
            if ($this->verifyH1($similarTextPercent, $levenshteinDistance, $higherSimilarTextPercent)) :
                $higherSimilarTextPercent = $similarTextPercent;
                $chosenTitle = $h1;
            endif;
        endforeach;
        return $chosenTitle;
    }
    private function verifyH1(float $similarTextPercent, float $levenshteinDistance, float $higherSimilarTextPercent): bool
    {
        return ($similarTextPercent > 0 && $levenshteinDistance < self::LEVENSHTEIN_MIN_VALUE && $similarTextPercent > $higherSimilarTextPercent);
    }
}
