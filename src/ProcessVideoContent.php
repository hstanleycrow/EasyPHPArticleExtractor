<?php

namespace hstanleycrow\EasyPHPArticleExtractor;

class ProcessVideoContent extends AbstractProcessArticleContent
{
    public function __construct(simple_html_dom_node $mainContent)
    {
        parent::__construct($mainContent);
    }

    public function execute(): simple_html_dom_node
    {
        #uscamos los videos que esten incrustados para cambiar el iframe por la url del video de youtube o dailymotion
        if ($this->mainContentHasIframe()) :
            $i = 0;
            foreach ($this->mainContent->find('iframe') as $iframe) :
                $src = $this->findSrc($i);
                if ($this->isYoutubeVideo($src))
                    $src = $this->getRealYoutubeURL($src);
                else
                    if ($this->isDailyMotionVideo($src))
                    $src = $this->getRealDailyMotionURL($src);
                else
                    $src = "";
                $this->setUrlInContent($src, $i);
                $i++;
            endforeach;
        endif;
        #$this->mainContent->load($this->mainContent->save());
        return $this->mainContent;
    }
    private function mainContentHasIframe(): bool
    {
        return (bool)($this->mainContent->find('iframe'));
    }
    private function findSrc(int $i): string
    {
        $src = $this->mainContent->find('iframe', $i)->src;
        #$src = $iframe->src; esta segunda linea deberia hacer lo mismo que la anterior, verificarlo despues
        if (empty($src))
            $src = $this->mainContent->find('iframe', $i)->getAttribute('data-src');
        return $src;
    }
    private function isYoutubeVideo(string $src): bool
    {
        return stripos($src, "youtube");
    }
    private function getRealYoutubeURL(string $src): string
    {
        # Si el enlace comienza por // se reemplaza por el https
        if ($this->srcHasNotProtocol($src)) :
            $src = str_replace("//", "https://", $src);
        endif;
        if ($this->srcHasShortYoutubeTextInUrl($src)) :
            $src = str_replace("youtu.be/", "www.youtube.com/watch?v=", $src);
        endif;
        # si el enlace esta como embed se debe cambiar a la forma https://www.youtube.com/watch?v=mequMsZo0WI
        if ($this->isEmbedURL($src)) :
            preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $src, $vid);
            $src = 'https://www.youtube.com/watch?v=' . $vid[1];
        endif;
        return $src;
    }
    private function setUrlInContent(string $src, int $i): void
    {
        $this->mainContent->find('iframe', $i)->outertext = "<p>" . $src . "</p>";
    }
    private function srcHasNotProtocol(string $src): bool
    {
        return stripos($src, "http") === false;
    }
    private function srcHasShortYoutubeTextInUrl(string $src): bool
    {
        return stripos($src, "youtu.be");
    }
    private function isEmbedURL(string $src): bool
    {
        return stripos($src, "/embed/") !== false;
    }
    private function isDailyMotionVideo(string $src): bool
    {
        return stripos($src, "dailymotion");
    }
    private function getRealDailyMotionURL(string $src): string
    {
        # Si el enlace comienza por // se reemplaza por el https
        if ($this->srcHasNotProtocol($src)) :
            $src = str_replace("//", "https://", $src);
        endif;
        return $src;
    }
}
