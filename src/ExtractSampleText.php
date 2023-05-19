<?php

namespace hstanleycrow\EasyPHPArticleExtractor;

class ExtractSampleText
{
    private string $plainText;
    public function __construct(string $plainText)
    {
        $this->plainText = $plainText;
    }

    public function extract(): array
    {
        $paragraphBlocksText = new ParagraphBlocksText($this->plainText);
        # SE van a tomar hasta 3 bloques de texto para probar otros por si el primero no funciona
        $chosenTexts = array();
        if ($paragraphBlocksText->hasMoreThanOnePeriod()) :
            $blocksCount = 0;
            foreach ($paragraphBlocksText->paragraphBlockList() as $blockText) :
                if ($this->blockTextIsLargerThanMinLenght($blockText)) :
                    if ($paragraphBlocksText->originIsBr()) :
                        $paragraphLines = explode(".", $blockText);
                        foreach ($paragraphLines as $paragraphLine) :
                            #$paragraphBlocksText->cleanParagraphBlock($paragraphLine);
                            $paragraphBlocksText = TextCleaner::cleanHTMLText($paragraphLine);
                            if (trim(strlen($paragraphLine)) > ArticleExtractorSetup::BLOCK_MIN_LENGHT) :
                                $postText = trim(substr($paragraphLine, 0, ArticleExtractorSetup::BLOCK_MIN_LENGHT));
                                #$postText = $paragraphBlocksText->cleanText($postText);
                                $postText = TextCleaner::cleanPlainText($postText);
                                $chosenTexts[] = $postText;
                                $blocksCount++;
                                if ($blocksCount == ArticleExtractorSetup::MIN_TEXT_LINE_TO_READ) break;
                            #break;
                            endif;
                        endforeach;
                    endif;
                    if ($paragraphBlocksText->originIsPeriod()) :
                        if (strlen($blockText) > ArticleExtractorSetup::BLOCK_MIN_LENGHT) :
                            $postText = substr($blockText, 0, ArticleExtractorSetup::BLOCK_MIN_LENGHT);
                            $postText = TextCleaner::cleanPlainText($postText);
                            #$postText = $paragraphBlocksText->cleanText($postText);
                            $chosenTexts[] = $postText;
                            $blocksCount++;
                        #break;
                        endif;
                    endif;
                endif;
                if ($blocksCount == ArticleExtractorSetup::MIN_LINES_TO_READ) break;
            endforeach;
        endif;
        return $chosenTexts;
    }
    private function blockTextIsLargerThanMinLenght(string $blockText): bool
    {
        return strlen($blockText) >= ArticleExtractorSetup::PARAGRAPH_MIN_LENGHT;
    }
}
