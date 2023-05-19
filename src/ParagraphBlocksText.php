<?php

namespace hstanleycrow\EasyPHPArticleExtractor;

class ParagraphBlocksText
{
    private string $plainText;
    private string $paragraphSeparatorOrigin;
    private array $paragraphBlockList;

    public function __construct(string $plainText)
    {
        $this->plainText = $plainText;
        $this->getOrigin();
    }
    public function getOrigin(): void
    {
        $this->paragraphBlockList = explode("<br />", $this->plainText);
        $this->paragraphSeparatorOrigin = "br";
        if ($this->hasOnlyOneParagraph()) :
            $this->paragraphBlockList = explode(".", $this->plainText);
            $this->paragraphSeparatorOrigin = "period";
        endif;
    }
    private function hasOnlyOneParagraph(): bool
    {
        return (count($this->paragraphBlockList) == 1);
    }
    public function paragraphSeparatorOrigin(): string
    {
        return $this->paragraphSeparatorOrigin;
    }
    public function paragraphBlockList(): array
    {
        return $this->paragraphBlockList;
    }
    public function hasMoreThanOnePeriod(): bool
    {
        return (count($this->paragraphBlockList) > 1);
    }
    public function originIsBr(): bool
    {
        return $this->paragraphSeparatorOrigin == "br";
    }
    public function originIsPeriod(): bool
    {
        return $this->paragraphSeparatorOrigin == "period";
    }
    /*public function cleanParagraphBlock(string $paragraphBlock): string
    {
        $paragraphBlock = htmlentities($paragraphBlock, ENT_QUOTES, "UTF-8");
        $paragraphBlock = str_replace("&nbsp;", " ", $paragraphBlock);
        $paragraphBlock = str_replace("&#039;", "'", $paragraphBlock);
        $paragraphBlock = str_replace("&rsquo;", "'", $paragraphBlock);
        return $paragraphBlock;
    }
    public function cleanText(string $text): string
    {
        $text = trim($text);
        $text = str_replace("&nbsp;", " ", $text);
        $text = str_replace("&#039;", "'", $text);
        $text = str_replace("&rsquo;", "'", $text);
        $text = preg_replace('/[[:^print:]]\"/', '', $text);
        return $text;
    }*/
}
