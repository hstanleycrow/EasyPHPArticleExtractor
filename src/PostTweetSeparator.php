<?php

namespace hstanleycrow\EasyPHPArticleExtractor;

class PostTweetSeparator extends AbstractProcessArticleContent
{
    public function __construct(simple_html_dom_node $mainContent)
    {
        parent::__construct($mainContent);
    }

    public function execute(): simple_html_dom_node
    {
        #buscamos si hay tweets incrustados para separarlos 
        #die('antes de buscar tuits');
        if ($this->mainContent->find(ArticleExtractorSetup::TWEET_CONTAINER)) :
            $i = 0;
            foreach ($this->mainContent->find(ArticleExtractorSetup::TWEET_CONTAINER) as $tweetBlock) :
                if ($tweetBlock->find('a')) :
                    $j = 0;
                    foreach ($tweetBlock->find('a') as $a) :
                        if ($this->isValidTweetURL($a)) :
                            $this->mainContent->find(ArticleExtractorSetup::TWEET_CONTAINER, $i)->outertext = "<br>" . $tweetBlock->find('a', $j)->href . "<br><br>";
                            break;
                        endif;
                        $j++;
                    endforeach;
                endif;
                $i++;
            endforeach;
        endif;
        #cada vez que modificamos algo, guradamos y cargamos el contenedor
        #$this->mainContent->load($this->mainContent->save());
        return $this->mainContent;
    }
    private function isValidTweetURL($tweetElement): bool
    {
        return (stripos($tweetElement, '/status/') !== false);
    }
}
