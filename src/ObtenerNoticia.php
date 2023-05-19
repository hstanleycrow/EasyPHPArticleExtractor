<?php

namespace hstanleycrow\EasyPHPArticleExtractor;

include_once('simple_html_dom.php');
class ObtenerNoticia
{
  private $_url;
  private $_title;
  private $_contenedor;
  private $_html;
  private $_texto;
  private $_web;
  private $_blog_post_imagen;
  private $_blog_post_titulo;
  private $_imagen;
  private $_lista_imagenes;
  private $_imagenesLibres = true;

  public function __construct($url, $contenedor, $blog_post_imagen, $blog_post_titulo = "", $html = "")
  {
    $this->_setUrl($url);
    $this->_setContenedor($contenedor);
    $this->_setBlogPostImagen($blog_post_imagen);
    $this->_setBlogPostTitulo($blog_post_titulo);
    #echo $blog_post_imagen;die();
    $this::_getNewsHtml($html);
    #$this::_parseContenedor();
    #$this->asignarDivs();
  }

  private function _setUrl($url)
  {
    $urlScheme = parse_url($url, PHP_URL_SCHEME);
    $urlHost = parse_url($url, PHP_URL_HOST);
    $this->_web = $urlScheme . "://" . $urlHost;
    $this->_url = $url;
  }
  private function _setContenedor($contenedor)
  {
    $this->_contenedor = $contenedor;
    #echo $this->_contenedor;die();
  }
  private function _getContenedor()
  {
    return $this->_contenedor;
  }
  private function _setBlogPostImagen($blog_post_imagen)
  {
    $this->_blog_post_imagen = $blog_post_imagen;
  }
  private function _setBlogPostTitulo($blog_post_titulo)
  {
    $this->_blog_post_titulo = $blog_post_titulo;
  }

  private function _setHtml($html)
  {
    $this->_html = $html;
  }
  private function _getHtml()
  {
    return $this->_html;
  }
  private function _escapeJavaScriptText($string)
  {
    #return $string;
    #return addslashes($string);
    return str_replace("\n", '\n', str_replace('"', '\"', addcslashes(str_replace("\r", '', (string)$string), "\0..\37'\\")));
  }
  public function getTexto()
  {
    return $this->_escapeJavaScriptText($this->_texto);
  }
  public function getTitulo()
  {
    return $this->_title;
  }

  public function getImagenesLibres()
  {
    #return true;
    return $this->_imagenesLibres;
  }

  public function getImagenDestacada()
  {
    if (empty($this->_imagen) || !$this->_esImagen($this->_imagen))
      $this->_imagen = 'images/image-not-found.jpg';
    return $this->_imagen;
  }

  public function esImagenLibre($src)
  {
    $default_opts = array(
      'http' => array(
        'method' => "GET",
        'header' => "Referer: http://www.fakesite.com/hotlink-check/",
        'user_agent' =>    $_SERVER['HTTP_USER_AGENT']
      )
    );
    stream_context_set_default($default_opts);
    $headers = get_headers($src, 1);
    if (preg_match('/200 OK$/', $headers[0])) :
      return true;
    else :
      return false;
    endif;
  }

  private function _limpiarTexto($html)
  {
    $as = $html->find('a');
    foreach ($as as $a) :
      $html = str_replace($a, $a->plaintext, $html);
    #echo $a->plaintext . PHP_EOL;
    endforeach;
    #die();
    return $html;
  }
  private function _verifyDomainInLink($link)
  {
    if (stripos($link, $this->_web) === false)
      $link = $this->_web . "/" . $link;
    return $link;
  }

  private function _getTitulo($html)
  {
    if (!empty($this->_blog_post_titulo)) :
      $tmp_titulo = $html->find($this->_blog_post_titulo, 0);
      #echo $tmp_titulo[0]->plaintext;die();
      if ($tmp_titulo)
        $tmp_titulo = $tmp_titulo->plaintext;
    endif;
    if (empty($this->_blog_post_titulo)) :
      $tmp_titulo = $html->find('h1,h2,h3', 0);
      #echo $tmp_titulo[0]->plaintext;die();
      if ($tmp_titulo)
        $tmp_titulo = $tmp_titulo->plaintext;
      if (empty($tmp_titulo))
        $tmp_titulo = "Titulo no encontrado";
    endif;
    return trim($tmp_titulo);
  }

  private function _validarFormato($src)
  {
    if (!empty($src) && stripos($src, ".webp")) :
      #echo $src;die();
      $src = str_replace(".webp", ".jpg", $src);
      if (!@getimagesize($src)) :
        $src = str_replace(".jpg", ".png", $src);
        if (!@getimagesize($src)) :
          $src = "";
        endif;
      endif;
    endif;
    return $src;
  }

  private function _getImageFromStyle($html, $contenedor)
  {
    $src = "";
    $src_tmp = "";
    $divs = $html->find($contenedor);
    foreach ($divs as $div) :
      $style = $div->style;
      if (empty($style))
        $style = $div->{'style'};
      if (empty($style))
        $style = $div;
      if (preg_match('#\bhttps?://[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/))#', $style, $matches)) {
        $src_tmp = $matches[0];
        if (!empty($src_tmp)) :
          #si ha extraido una imagen se valida que tenga el formato adecuado
          $src_tmp = $this->_validarFormato($src_tmp);
          if (!empty($src_tmp)) :
            #echo $src_tmp . " ";
            list($ancho, $alto, $tipo, $atributos) = getimagesize($src_tmp);
            #echo "<pre>";echo "$src_tmp | $tipo | $ancho" . PHP_EOL . "</pre>";
            if ($ancho > 200 && ($tipo >= 2 && $tipo <= 3)) :
              $src = $src_tmp;
              break;
            else :
              $src_tmp = "";
            endif;
          else :
            $src_tmp = "";
          endif;
        endif;
      }
    #echo "src: $src";die();
    endforeach;
    #echo "src: $src";die();
    return $src;
  }

  private function _getRealImage($image)
  {
    $encontrada = false;
    #si esta el data: en el SRC pasamos a buscarlo en otro lugar
    $src = $image->{'nitro-lazy-src'};
    if (!empty($src)) :
      $encontrada = true;
    endif;
    if (empty($src) && !$encontrada) :
      $src = $image->{'data-dt-lazy-src'};
      if (!empty($src)) :
        $encontrada = true;
      endif;
    endif;
    if (empty($src) && !$encontrada) :
      $src = $image->{'data-ezsrc'};
      if (!empty($src)) :
        $encontrada = true;
      endif;
    endif;
    #si la imagen default es SVG
    if (empty($src) && !$encontrada) :
      #die("entre aqui");
      #echo "emdia: " . $image->{'data-pin-media'};die();
      $src = $image->{'data-pin-media'};
      if (!empty($src)) :
        $encontrada = true;
      endif;
    endif;
    if (empty($src) && !$encontrada) :
      #echo "emdia: " . $image->{'data-pin-media'};
      $src = $image->{'data-img-url'};
      if (!empty($src)) :
        $encontrada = true;
      endif;
    endif;
    if (empty($src) && !$encontrada) :
      #se va a buscar en un srset, por lo tanto se debe obtener solo la primera imagen de la serie ya que por lo general es la mas grande
      $src = $image->{'data-srcset'};
      if (stripos($src, " "))
        list($src, $basura) = explode(" ", $src);
      if (!empty($src)) :
        $encontrada = true;
      endif;
    endif;
    if (empty($src) && !$encontrada) :
      #se va a buscar en un srcset, por lo tanto se debe obtener solo la primera imagen de la serie ya que por lo general es la mas grande
      $src = $image->{'srcset'};
      $srcs = array();
      if (stripos($src, " "))
        $srcs = explode(" ", $src);
      #list($src, $basura) = explode(" ", $src);
      #var_dump($srcs);die();
      $pos = 0;
      foreach ($srcs as $temp) :
        if (stripos($temp, "://") === false)
          unset($srcs[$pos]);
        $pos++;
      endforeach;
      #echo "<pre>"; print_r($srcs); echo PHP_EOL . count($srcs);die();
      $src = array_pop($srcs);
      #var_dump($src);die();
      if (!empty($src)) :
        $encontrada = true;
      endif;
    endif;
    if (empty($src) && !$encontrada) :
      #se va a buscar en un srset, por lo tanto se debe obtener solo la primera imagen de la serie ya que por lo general es la mas grande
      $src = $image->{'data-src'};
      if (stripos($src, " "))
        list($src, $basura) = explode(" ", $src);
      if (!empty($src)) :
        $encontrada = true;
      endif;
    endif;
    #si la imagen termina en .jpg.webp
    if (stripos($src, ".jpg.webp") !== FALSE) :
      $src = str_replace(".webp", "", $src);
    endif;
    #si la imagen termina en .png.webp
    if (stripos($src, ".png.webp") !== FALSE) :
      $src = str_replace(".webp", "", $src);
    endif;
    #si la imagen termina en .webp pero no tiene .png o .jpg antes
    if (stripos($src, ".webp") !== FALSE) :
      $src = str_replace(".webp", ".jpg", $src);
      if (!@getimagesize($src)) :
        $src = str_replace(".jpg", ".png", $src);
        if (!@getimagesize($src)) :
          $src = "";
        endif;
      endif;
    endif;
    return $src;
  }

  private function _getImageOld($html, $contenedor)
  {
    # primero se buscan las imagenes por tag
    # si no se encuentra por tag, se busca segun la configuracion de la BD y el campo blog_listado_imagen
    #echo "src: $src | " . $this->_blog_post_imagen;die();
    $src = "";
    $lista_imagenes = array(); # se va a usar para registrar las imagenes que se encuentren, y la ponderacion de si es una imagen destacada o no.
    #Si tengo el contenedor ya definido
    if (!empty($this->_blog_post_imagen)) :
      #echo $this->_blog_post_imagen;die();
      #echo $html;die();
      if ($tmp_imagen = $html->find($this->_blog_post_imagen)) :
        #var_dump($tmp_imagen);die();
        #echo $tmp_imagen;die();
        $src = $tmp_imagen->src;
      else :
        #die("no entro");
        #busco si la imagen destacada esta dentro de un <a>
        #echo $html;die();
        if ($html->find('a')) :
          $i = 0;
          #die('entro');
          foreach ($html->find('a') as $a) :
            #die('entre');
            #echo $contenedor->find('a', $i)->outertext;die();
            #Si el enlace tiene una imagen dentro
            #$buscar = "img" . $this->_blog_post_imagen;
            #echo $buscar;die();
            if ($a->find('img')) :
              $imagen_en_a = $a->find('img', 0);
              #echo($imagen_en_a);
              $buscar = str_replace(".", "", $this->_blog_post_imagen);
              $buscar = str_replace("#", "", $buscar);
              #echo $buscar;die();
              if (stripos($imagen_en_a, $buscar)) :
                $src = $imagen_en_a->src;
                break;
              endif;
            #$a_img = $newElement;
            #$html->find('a')[$i] = $newElement;
            endif;
            #$html->find('a', $i) = str_replace($a, $a->plaintext, $html);
            #echo $a->plaintext . PHP_EOL;
            $i++;
          endforeach;
        #die('termino');
        endif;
      endif;
    #echo "src: $src";die();
    endif;
    # si esta configurado en la BD que se extrae de un elemento especifico (contenedor) pero no es una imagen como tal, se pasa a buscar en el style del contenedor, ejemplo <div class="loop-image b-lazy b-loaded b-loaded" style="background-image:url(https://cdn57.androidauthority.net/wp-content/uploads/2021/04/AAW-Slashy-Camp-screenshot-300x200.jpg);"></div>
    if (empty($src) && !empty($this->_blog_post_imagen)) :
      /*$divs = $html->find($this->_blog_post_imagen);
        foreach ($divs as $div):
          $style = $div->style;
          if(empty($style))
            $style = $div->{'style'};
          if(empty($style))
            $style = $div;
          if(preg_match('#\bhttps?://[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/))#',$style, $matches)){
            $src = $matches[0];
          }
        endforeach;*/
      $src = $this->_getImageFromStyle($html, $this->_blog_post_imagen);
    endif;
    #Si esta configurado un contenedor, lo mas segurgo es que la encuentre, si la encuentra, la agregamos al listado de imagenes y le damos la ponderacion maxima.
    if (!empty($src)) :
      $lista_imagenes[] = array("src" => $src, "puntos" => 10);
    endif;
    $noConsiderar = array("noscript");
    #Si sigue sin encontrarse la imagen o no tenia definido un contenedor
    if (empty($src)) :
      #echo $image->{'nitro-lazy-src'}
      #$src = $this::_getRealImage($html);
      $html2 = new simple_html_dom();
      $html2->load($html);
      if ($html2->find('nav')) :
        foreach ($html2->find('nav') as $quitar) :
          $quitar->outertext = '';
        endforeach;
      endif;
      if ($html2->find('.pagenav')) :
        foreach ($html2->find('.pagenav') as $quitar) :
          $quitar->outertext = '';
        endforeach;
      endif;
      if ($html2->find("[class*='navbar']")) :
        //[attribute*=value]
        foreach ($html2->find("[class*='navbar']") as $quitar) :
          $quitar->outertext = '';
        endforeach;
      endif;
      if ($html2->find("[class*='topnav']")) :
        //[attribute*=value]
        foreach ($html2->find("[class*='topnav']") as $quitar) :
          $quitar->outertext = '';
        endforeach;
      endif;
      if ($html2->find('header')) :
        foreach ($html2->find('header') as $quitar) :
          $quitar->outertext = '';
        endforeach;
      endif;
      if ($html2->find('footer')) :
        foreach ($html2->find('footer') as $quitar) :
          $quitar->outertext = '';
        endforeach;
      endif;
      if ($html2->find('aside')) :
        foreach ($html2->find('aside') as $quitar) :
          $quitar->outertext = '';
        endforeach;
      endif;
      if ($html2->find("[class*='author']")) :
        foreach ($html2->find("[class*='author']") as $quitar) :
          #echo "<pre>" . $quitar->tag . PHP_EOL ;
          if ($quitar->tag <> "body" && $quitar->tag <> "main" && $quitar->tag <> "article")
            $quitar->outertext = '';
        endforeach;
      endif;
      if ($html2->find('a[rel*=sponsored]')) :
        //[attribute*=value]
        foreach ($html2->find('a[rel*=sponsored]') as $quitar) :
          $quitar->outertext = '';
        endforeach;
      endif;
      $html2->save();
      $html2->load($html2->save());

      $images = $html2->find('img');
      #echo "<pre>"; print_r($images);die();
      $pos = 0;
      foreach ($images as $image) :
        $src = $image->src;
        $urlScheme = parse_url($this->_url, PHP_URL_SCHEME);
        $urlHost = parse_url($this->_url, PHP_URL_HOST);
        $imgScheme = parse_url($src, PHP_URL_SCHEME);
        $imgHost = parse_url($src, PHP_URL_HOST);
        if (($urlHost <> $imgHost) && $imgHost == "") :
          $src = $urlScheme . "://" . $urlHost . str_replace(" ", "%20", $src);
        endif;
        if (stripos($src, ".svg")) :
          $src = "";
        endif;
        if (stripos($src, "data:")  === false) :
        /*if(!empty($src)):
              break;
            endif;*/
        else :
          $src = "";
        endif;
        /*if( stripos( $src, "data:")  !== false )
            $src = "";*/
        #Si no se ha logrado extraer el SRC de la imagen, probablemente este en de otra forma, como en un style, o lazy content
        if (empty($src)) :
          $src = $this::_getRealImage($image);
        endif;
        # Si se ha obtenido una imagen, la ponderamos para evaluar si puede ser la imagen destacada
        if (!empty($src)) :
          if (strpos($src, "?"))
            list($src, $basura) = explode("?", $src);
          if (stripos($src, "http") === false) :
            $src = str_replace("//", "https://", $src);
          endif;
          if (!in_array($image->parent->tag, $noConsiderar)) :
            #echo "<pre>Padre: " . $image->parent->tag . PHP_EOL . "</pre>";
            $lista_imagenes[] = array("src" => $src, "puntos" => $this->_evaluarImagenDestacada($src, $pos));
          endif;
        endif;
        $pos++;
      endforeach;
    endif;
    #echo "<pre>"; print_r($lista_imagenes);die();
    $mejorK = -1;
    $mejorPuntos = 0;
    foreach ($lista_imagenes as $k => $v) :
      $src_tmp = $v['src'];
      $puntos_tmp = $v['puntos'];
      if ($puntos_tmp > $mejorPuntos) :
        $mejorPuntos = $puntos_tmp;
        $mejorK = $k;
      endif;
    endforeach;
    #echo "me: $mejorPuntos, k $mejorK src: " . $lista_imagenes[$mejorK]['src'];die();
    # si no se ha logrado extraer ninguna imagen, se asigna una generica
    $src = $lista_imagenes[$mejorK]["src"];
    if (empty($src))
      $src = "" . "images/image-not-found.jpg";
    $html2 = "";
    return $src;
  }

  private function _getImage($html)
  {
    # primero se buscan las imagenes por tag
    # si no se encuentra por tag, se busca segun la configuracion de la BD y el campo blog_listado_imagen
    #echo "src: $src | " . $this->_blog_post_imagen;die();
    $src = "";
    $lista_imagenes = array(); # se va a usar para registrar las imagenes que se encuentren, y la ponderacion de si es una imagen destacada o no.
    #Si tengo el contenedor ya definido
    if (!empty($this->_blog_post_imagen)) :
      #echo $this->_blog_post_imagen;die();
      #echo $html;die();
      if ($tmp_imagen = $html->find($this->_blog_post_imagen)) :
        #var_dump($tmp_imagen);die();
        #echo $tmp_imagen;die();
        $src = $tmp_imagen->src;
      else :
        #die("no entro");
        #busco si la imagen destacada esta dentro de un <a>
        #echo $html;die();
        if ($html->find('a')) :
          $i = 0;
          #die('entro');
          foreach ($html->find('a') as $a) :
            #die('entre');
            #echo $contenedor->find('a', $i)->outertext;die();
            #Si el enlace tiene una imagen dentro
            #$buscar = "img" . $this->_blog_post_imagen;
            #echo $buscar;die();
            if ($a->find('img')) :
              $imagen_en_a = $a->find('img', 0);
              #echo($imagen_en_a);
              $buscar = str_replace(".", "", $this->_blog_post_imagen);
              $buscar = str_replace("#", "", $buscar);
              #echo $buscar;die();
              if (stripos($imagen_en_a, $buscar)) :
                $src = $imagen_en_a->src;
                break;
              endif;
            #$a_img = $newElement;
            #$html->find('a')[$i] = $newElement;
            endif;
            #$html->find('a', $i) = str_replace($a, $a->plaintext, $html);
            #echo $a->plaintext . PHP_EOL;
            $i++;
          endforeach;
        #die('termino');
        endif;
      endif;
    #echo "src: $src";die();
    endif;
    # si esta configurado en la BD que se extrae de un elemento especifico (contenedor) pero no es una imagen como tal, se pasa a buscar en el style del contenedor, ejemplo <div class="loop-image b-lazy b-loaded b-loaded" style="background-image:url(https://cdn57.androidauthority.net/wp-content/uploads/2021/04/AAW-Slashy-Camp-screenshot-300x200.jpg);"></div>
    if (empty($src) && !empty($this->_blog_post_imagen)) :
      /*$divs = $html->find($this->_blog_post_imagen);
        foreach ($divs as $div):
          $style = $div->style;
          if(empty($style))
            $style = $div->{'style'};
          if(empty($style))
            $style = $div;
          if(preg_match('#\bhttps?://[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/))#',$style, $matches)){
            $src = $matches[0];
          }
        endforeach;*/
      $src = $this->_getImageFromStyle($html, $this->_blog_post_imagen);
    endif;
    #Si esta configurado un contenedor, lo mas segurgo es que la encuentre, si la encuentra, la agregamos al listado de imagenes y le damos la ponderacion maxima.
    if (!empty($src)) :
      $lista_imagenes[] = array("src" => $src, "puntos" => 10);
    endif;
    $noConsiderar = array("noscript");
    #print_r($this->_lista_imagenes);die();
    #Si sigue sin encontrarse la imagen o no tenia definido un contenedor
    if (empty($src)) :
      $pos = 0;
      $lista_imagenes = array();
      foreach ($this->_lista_imagenes as $src) :
        $lista_imagenes[] = array("src" => $src, "puntos" => $this->_evaluarImagenDestacada($src, $pos));
        $pos++;
      endforeach;
    endif;
    $mejorK = -1;
    $mejorPuntos = 0;
    #echo "<pre>"; print_r($lista_imagenes);die();
    foreach ($lista_imagenes as $k => $v) :
      $src_tmp = $v['src'];
      $puntos_tmp = $v['puntos'];
      if ($puntos_tmp > $mejorPuntos) :
        $mejorPuntos = $puntos_tmp;
        $mejorK = $k;
      endif;
    endforeach;
    #echo "me: $mejorPuntos, k $mejorK src: " . $lista_imagenes[$mejorK]['src'];die();
    # si no se ha logrado extraer ninguna imagen, se asigna una generica
    $src = $lista_imagenes[$mejorK]["src"];
    if (empty($src))
      $src = "" . "images/image-not-found.jpg";
    return $src;
  }

  private function _esImagen($path)
  {
    $imageSizeArray = getimagesize($path);
    $imageTypeArray = $imageSizeArray[2];
    return (bool)(in_array($imageTypeArray, array(IMAGETYPE_JPEG, IMAGETYPE_PNG)));
  }

  /**
   * Con esta funcion se pretende puntuar a las imagenes para decidir si se puede usar como destacada o no
   * */
  private function _evaluarImagenDestacada($src, $pos)
  {
    $puntos = 0;
    #Si la imagen es JPG o PNG
    if ($this->_esImagen($src)) :
      $puntos++;
      #Si contiene la palabra logo en el nombre del archivo, le restamos un punto
      if (stripos($src, "logo") !== false) return 0;
      list($ancho, $alto, $tipo, $atributos) = getimagesize($src);
      #si el alto o ancho de la imagen es menor a 100 se le asigna 0 puntos y se sale
      if ($ancho <= 150 || $alto < 150) :
        $puntos = 0;
      else :
        # Si es JPG le damos un punto mas
        if ($tipo == 2) $puntos++;
        #si el ancho de la imagen es entre 300 y 500 recibe un punto
        if ($ancho >= 300 && $ancho < 500) $puntos++;
        #si el ancho de la imagen es entre 500 y 1000 recibe 2 puntos
        if ($ancho >= 500 && $ancho < 1000) $puntos = $puntos + 2;
        #si el ancho de la imagen es mayor que 1000 recibe 3 puntos
        if ($ancho >= 1000) $puntos = $puntos + 3;
        #si el alto de la imagen es menor de 300 le restamos un punto
        if ($alto < 250) $puntos--;
        #si el alto de la imagen es entre 300 y 500 recibe 1 puntos
        if ($alto >= 300 && $ancho < 500) $puntos++;
        #si el alto de la imagen es mayor que 500 recibe 2 puntos
        if ($alto >= 500) $puntos = $puntos + 2;
        #Si la posicion de la imagen es la 0, restamos un punto, porque es probable que sea el LOGO
        if ($pos == 0) $puntos = $puntos + 3;
        #Si la posicion de la imagen es entre la primera y la 3, recibe 2 puntos ya que es de las primeras imagenes.
        if ($pos > 0 && $pos <= 1) $puntos = $puntos + 2;
      endif;
    else :
      #si no es una imagen valida, la saltamos
      $puntos = 0;
    endif;
    return $puntos;
  }

  /**
   * @param $image_path
   * @return bool|mixed
   */
  private function _get_image_mime_type($image_path)
  {
    $mimes  = array(
      IMAGETYPE_GIF => ".gif",
      IMAGETYPE_JPEG => ".jpg",
      IMAGETYPE_PNG => ".png",
      IMAGETYPE_SWF => ".swf",
      IMAGETYPE_BMP => ".bmp",
      IMAGETYPE_TIFF_II => ".tiff",
      IMAGETYPE_TIFF_MM => ".tiff",
      IMAGETYPE_WBMP => ".wbmp",
    );

    if (($image_type = exif_imagetype($image_path)) && (array_key_exists($image_type, $mimes))) :
      return $mimes[$image_type];
    else :
      return FALSE;
    endif;
  }

  private function _getNewsHtml($html_tmp)
  {
    #echo gettype($html_tmp);die();
    $html = new simple_html_dom();
    if (empty($html_tmp)) :
      ini_set('user_agent', $_SERVER['HTTP_USER_AGENT']);
      $opciones = array(
        'http' => array(
          'method' =>   "GET",
          'header' =>    "Accept-language: es-SV,en-US;q=0.7,en;q=0.3\r\n" .
            "Cookie: tms_VisitorID=475i1zn25v\r\n",
          'user_agent' =>    $_SERVER['HTTP_USER_AGENT']
        )
      );
      $contexto = stream_context_create($opciones);
      $html->load_file($this->_url, true, $contexto);
    else :
      #echo $html_tmp->outertext;die();
      #$a = 
      $html->load($html_tmp->outertext);
    #echo $html->outertext;die();
    #$html_tmp = "";
    endif;
    if ($html !== false) :
      #die('despues de load files');
      $this->_title = $this->_getTitulo($html);
      #buscamos el contenedor que contiene tood el contenido (configurado en la BD)
      $contenedor = $html->find($this->_contenedor, 0);
    else :
      $contenedor = false;
    endif;
    #echo $this->_contenedor;die();
    if ($contenedor) :
      #echo $contenedor->innertext;die();
      #die('antes de buscar scriptws');
      #buscamos si hay etiquetas <script> que debamos borrar
      if ($contenedor->find('script')) :
        foreach ($contenedor->find('script') as $script) :
          $script->outertext = '';
        endforeach;
      endif;
      if ($contenedor->find('nav')) :
        foreach ($contenedor->find('nav') as $quitar) :
          $quitar->outertext = '';
        endforeach;
      endif;
      if ($contenedor->find('footer')) :
        foreach ($contenedor->find('footer') as $quitar) :
          $quitar->outertext = '';
        endforeach;
      endif;
      if ($contenedor->find('#footer')) :
        foreach ($contenedor->find('#footer') as $quitar) :
          $quitar->outertext = '';
        endforeach;
      endif;
      if ($contenedor->find('aside')) :
        foreach ($contenedor->find('aside') as $quitar) :
          $quitar->outertext = '';
        endforeach;
      endif;
      if ($contenedor->find('button')) :
        foreach ($contenedor->find('button') as $quitar) :
          $quitar->outertext = '';
        endforeach;
      endif;
      if ($contenedor->find('.sr-only')) :
        foreach ($contenedor->find('.sr-only') as $quitar) :
          $quitar->outertext = '';
        endforeach;
      endif;
      if ($contenedor->find('.socialite-widget')) :
        foreach ($contenedor->find('.socialite-widget') as $quitar) :
          $quitar->outertext = '';
        endforeach;
      endif;
      if ($contenedor->find('.author-block')) :
        foreach ($contenedor->find('.author-block') as $quitar) :
          $quitar->outertext = '';
        endforeach;
      endif;
      if ($contenedor->find("[class*='breadcrumb']")) :
        foreach ($contenedor->find("[class*='breadcrumb']") as $quitar) :
          $quitar->outertext = '';
        endforeach;
      endif;
      if ($contenedor->find("[id*='breadcrumb']")) :
        foreach ($contenedor->find("[id*='breadcrumb']") as $quitar) :
          $quitar->outertext = '';
        endforeach;
      endif;
      if ($contenedor->find('#pagenav')) :
        foreach ($contenedor->find('#pagenav') as $quitar) :
          $quitar->outertext = '';
        endforeach;
      endif;
      if ($contenedor->find('.entry-date')) :
        foreach ($contenedor->find('.entry-date') as $quitar) :
          $quitar->outertext = '';
        endforeach;
      endif;
      if ($contenedor->find('.published')) :
        foreach ($contenedor->find('.published') as $quitar) :
          $quitar->outertext = '';
        endforeach;
      endif;
      if ($contenedor->find("[class*='author']")) :
        foreach ($contenedor->find("[class*='author']") as $quitar) :
          if ($quitar->tag <> "body" && $quitar->tag <> "main")
            $quitar->outertext = '';
        endforeach;
      endif;
      #echo $contenedor->innertext;die();
      if ($contenedor->find("[class*='social']")) :
        foreach ($contenedor->find("[class*='social']") as $quitar) :
          $quitar->outertext = '';
        endforeach;
      endif;
      /* 27/08/22: agregado por xataka */
      if ($contenedor->find("[class*='aside']")) :
        foreach ($contenedor->find("[class*='aside']") as $quitar) :
          $quitar->outertext = '';
        endforeach;
      endif;
      #echo $html->innertext;die();
      # lo quite porque rompia algunas paginas
      /*if($contenedor->find("[class*='meta']")):
          foreach($contenedor->find("[class*='meta']") as $quitar):
            $quitar->outertext = '';
          endforeach;
        endif;*/
      #post__author-share
      #echo $contenedor->outertext;die();
      if ($contenedor->find('noscript')) :
        $i = 0;
        foreach ($contenedor->find('noscript') as $tmphtml) :
          if ($tmphtml->find('img')) :
            #echo "<pre>" . htmlentities($tmphtml->find('img',0)->outertext);die();
            #$tmphtml->outertext = "";
            $tmphtml->outertext = $tmphtml->find('img', 0)->outertext;
          #$html->load($html->save());
          endif;
        endforeach;
      endif;
      #cada vez que modificamos algo, guradamos y cargamos el contenedor
      $html->load($html->save());


      $contenedor = $html->find($this->_contenedor, 0);
      #buscamos si hay tweets incrustados para separarlos 
      #die('antes de buscar tuits');
      if ($contenedor->find('blockquote.twitter-tweet')) :
        $i = 0;
        foreach ($contenedor->find('blockquote.twitter-tweet') as $block) :
          if ($block->find('a')) :
            $j = 0;
            foreach ($block->find('a') as $a) :
              if (stripos($a, '/status/') !== false) :
                #echo $block->find('a',$j)->href;die();
                $contenedor->find('blockquote.twitter-tweet', $i)->outertext = "<br>" . $block->find('a', $j)->href . "<br><br>";
                break;
              endif;
              $j++;
            endforeach;
          endif;
          $i++;
        endforeach;
      endif;
      #cada vez que modificamos algo, guradamos y cargamos el contenedor
      $html->load($html->save());



      $contenedor = $html->find($this->_contenedor, 0);

      #buscamos todos los enlaces, si son enlaces internos los convertimos a texto, sino, se dejan.
      if ($contenedor->find('a')) :
        $i = 0;
        foreach ($contenedor->find('a') as $a) :
          #echo $contenedor->find('a', $i)->outertext;die();
          #Si el enlace tiene una imagen dentro, se deja solo la imagen
          if ($a->find('img')) :
            $newElement = $a->find('img', 0);
            #echo $a;
            $a = $newElement;
            $contenedor->find('a')[$i]->outertext = $newElement;
          #echo $a;die();
          else :
            $urlHost = parse_url($a->href, PHP_URL_HOST);
            $urlPost = parse_url($this->_url, PHP_URL_HOST);
            #echo $urlHost;die();
            if (empty($urlHost)) :
              $contenedor->find('a', $i)->outertext = $contenedor->find('a', $i)->plaintext;
            else :
              #echo $a->outertext . " | " . $urlHost;die();
              #if( stripos($a->outertext, $urlHost) !== false)
              if ($urlPost == $urlHost)
                $contenedor->find('a', $i)->outertext = $contenedor->find('a', $i)->plaintext;
            endif;
            if ($a->find('svg')) :
              foreach ($a->find('svg') as $svg) :
                $svg->outertext = '';
              endforeach;
            endif;
          endif;
          #$html->find('a', $i) = str_replace($a, $a->plaintext, $html);
          #echo $a->plaintext . PHP_EOL;
          $i++;
        endforeach;
      endif;
      #die('despues de buscar enlaces a');
      #cada vez que modificamos algo, guradamos y cargamos el contenedor
      $contenedor->save();
      $html->load($html->save());



      $contenedor = $html->find($this->_contenedor, 0);
      #echo $contenedor;die();
      #$contenedor->load($contenedor->save());
      #die();
      #uscamos los videos que esten incrustados para cambiar el iframe por la url del video de youtube o dailymotion
      if ($contenedor->find('iframe')) :
        $i = 0;
        #die('entre');
        foreach ($contenedor->find('iframe') as $iframe) :
          $src = $contenedor->find('iframe', $i)->src;
          #echo $src;die();
          # si no se obtiene con el atributo SRC se buscar en data-src
          if (empty($src))
            $src = $contenedor->find('iframe', $i)->getAttribute('data-src');
          if (stripos($src, "youtube")) :
            # Si el enlace comienza por // se reemplaza por el https
            if (stripos($src, "http") === false) :
              $src = str_replace("//", "https://", $src);
            endif;
            # si el enlace es acortado con youtu.be se crea el enlace completo
            if (stripos($src, "youtu.be") !== false) :
              $src = str_replace("youtu.be/", "www.youtube.com/watch?v=", $src);
            endif;
            # si el enlace esta como embed se debe cambiar a la forma https://www.youtube.com/watch?v=mequMsZo0WI
            if (stripos($src, "/embed/") !== false) :
              preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $src, $vid);
              $src = 'https://www.youtube.com/watch?v=' . $vid[1];
            endif;

            $contenedor->find('iframe', $i)->outertext = "<p>" . $src . "</p>"; // con esta linea pongo solo la URL del video
          #$contenedor->find('iframe', $i)->outertext = "<p><iframe allowfullscreen='true' frameborder='0' type='text/html' src='" . $src . "'>" . $src . "</iframe></p>"; // con esta linea pongo el video en un iframe (creo que no funciona asi)
          else :
            if (stripos($src, "dailymotion")) :
              if (stripos($src, "http") === false) :
                $src = str_replace("//", "https://", $src);
              endif;
              #echo $src;die();
              $contenedor->find('iframe', $i)->outertext = "<p>" . $src . "</p>"; // con esta linea 
            else :
              $contenedor->find('iframe', $i)->outertext = "";
            endif;
          endif;
          $i++;
        endforeach;
      endif;
      #die('despues de buscar iframes youtube');
      #cada vez que modificamos algo, guradamos y cargamos el contenedor
      $html->load($html->save());
      $contenedor = $html->find($this->_contenedor, 0);
      #echo $contenedor->tag;die();
      #$src = $this::_getRealImage($contenedor);
      #echo $contenedor->innertext;die();
      #echo "<pre>";


      #buscamos todas las imagenes y las ponemos dentro de un <p>
      if ($contenedor->find('img')) : //con false evito que se extraigan las imagens
        $i = 0;
        $lista_imagenes = array(); # con este array verificamos que las imagenes no se dupliquen
        #echo "<pre>";echo count($contenedor->find('img'));die();
        #echo "<pre>";
        foreach ($contenedor->find('img') as $img) :
          #echo "<pre>" . $img->outertext . PHP_EOL . "</pre>";
          $src = $contenedor->find('img', $i)->src;
          if (strpos($src, "?"))
            list($src, $basura) = explode("?", $src);
          #echo $src;
          $urlScheme = parse_url($this->_url, PHP_URL_SCHEME);
          $urlHost = parse_url($this->_url, PHP_URL_HOST);
          $imgScheme = parse_url($src, PHP_URL_SCHEME);
          $imgHost = parse_url($src, PHP_URL_HOST);
          #echo "<pre>IMGHost: $imgHost | URLHost $urlHost | SRC: $src" . PHP_EOL . "</pre>";
          if (($urlHost <> $imgHost) && $imgHost == "") :
            $src = $urlScheme . "://" . $urlHost . str_replace(" ", "%20", $src);
          endif;
          #echo "<pre>IMGHost: $imgHost | URLHost $urlHost | SRC: $src" . PHP_EOL . "</pre>";
          if ((stripos($src, ".svg") !== FALSE) || (stripos($src, ".file") !== FALSE)) :
            $src = "";
          endif;

          if (stripos($src, "data:")  !== false)
            $src = "";
          if (((stripos($src, ".jpg")  === false) && (stripos($src, ".jpeg")  === false) && (stripos($src, ".png")  === false) && (stripos($src, ".webp")  === false)) || !empty($img->{'srcset'}))
            $src = "";
          #echo $src;die();
          if (empty($src)) :
            $src = $this::_getRealImage($img);
          endif;
          #echo $src;die();
          #si la imagen termina en .jpg.webp
          if (stripos($src, ".jpg.webp") !== FALSE) :
            $src = str_replace(".webp", "", $src);
          endif;
          #si la imagen termina en .png.webp
          if (stripos($src, ".png.webp") !== FALSE) :
            $src = str_replace(".webp", "", $src);
          endif;
          #si la imagen termina en .webp pero no tiene .png o .jpg antes
          if (stripos($src, ".webp") !== FALSE) :
            $src = str_replace(".webp", ".jpg", $src);
            if (!@getimagesize($src)) :
              $src = str_replace(".jpg", ".png", $src);
              if (!@getimagesize($src)) :
                $src = "";
              endif;
            endif;
          endif;
          #echo $src;die();
          $alt = $contenedor->find('img', $i)->alt;
          if (stripos($src, ".file") !== false) :
          #echo $src;die('aqui');
          endif;
          #if( (stripos( $src, "data:")  === false) && (stripos($src, ".file") === FALSE) ):
          if (stripos($src, ".jpg") !== false) :
            list($src, $basura) = explode(".jpg", $src);
            $src .=  ".jpg";
          endif;
          if (stripos($src, ".jpeg") !== false) :
            list($src, $basura) = explode(".jpeg", $src);
            $src .=  ".jpeg";
          endif;
          #echo $src;die();
          if (stripos($src, ".png") !== false) :
            list($src, $basura) = explode(".png", $src);
            $src .= ".png";
          #echo $src;#die();
          endif;
          if (!in_array($src, $lista_imagenes)) :
            if ((stripos($src, ".jpg") !== false) || (stripos($src, ".jpeg") !== false) || (stripos($src, ".png") !== false) && $this->esImagenLibre($src)) :
              array_push($lista_imagenes, $src);
              $newimg = '<p><img src="' . $src . '" alt="' . $alt . '"></p>';
              if ($img->parent->tag == "a") :
                $img->parent->outertext = $newimg;
              else :
                $contenedor->find('img', $i)->outertext = $newimg;
                $this->_imagenesLibres = true;
              endif;
            else :
              $contenedor->find('img', $i)->outertext = "";
              $this->_imagenesLibres = false;
            endif;
          #echo "$i- " . $src . PHP_EOL;
          endif;
          #echo $img->parent->outertext;die();
          $i++;
        endforeach;
        #echo "<pre>"; print_r($lista_imagenes);die();
        $this->_lista_imagenes = $lista_imagenes;
        $this->_imagen = $this::_getImage($html);
      #$contenedor->save();
      #$html->save();
      endif;
      #echo $contenedor->id . PHP_EOL;
      #echo $contenedor->innertext;die();
      #die('despues de trabajar imagenes');
      #cada vez que modificamos algo, guradamos y cargamos el contenedor
      #$html->load($html->save());
      #$contenedor = $html->find($this->_contenedor, 0);

      #buscamos todas las imagenes en el contenido para gardarlas en el servidor y ponerlas bien en el contenido de forma local
      if ($contenedor->find('img') && false) : //con false evito que se extraigan las imagens
        $i = 0;
        foreach ($contenedor->find('img') as $img) :
          $src = $contenedor->find('img', $i)->src;
          $image = file_get_contents($src);
          $mime_type = $this->_get_image_mime_type($src);
          $path_imagen = "tmpimage/" . uniqid() . $mime_type;
          file_put_contents($path_imagen, $image);
          $contenedor->find('img', $i)->src = $path_imagen;
          #$contenedor->find('img', $i)->outertext = "<p>" . $path_imagen . "</p>";
          $contenedor->find('img', $i)->outertext = "<p>" . $contenedor->find('img', $i)->outertext . "</p>";
          #echo $contenedor->find('img', $i)->outertext;die();
          $i++;
        endforeach;
      endif;
      #echo $contenedor->innertext;die();
      #die('antes de poner el texto');
      #echo $html->innertext;die();
      $this->_texto = $contenedor->innertext; # se asigna el nuevo texto
      #echo $this->_texto;die();
      # si pongo $contenedor, va a considerar solo lo que este dentro del contenedor del contenido, si la imagen destacada o el titulo esta fuera, no va a poder extraerlo
      #$this->_imagen = $this::_getImage($contenedor);
      # Si pongo httml va a buscar en toda la pagina
      $html->load($html->save());
    #$this->_imagen = $this::_getImage($html);
    else :
      $this->_texto = "No se pudo extraer el contenido, por favor verificar";
    endif;
    $html->load($html->save());
    $html->clear();
    unset($html, $contenedor);
    #var_dump($contenedor);
    #var_dump($html);
    #$this->_setHtml($contenedor);
  }
}
