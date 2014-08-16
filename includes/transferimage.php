<?php
/**
*  Example:
*
*  $i = new Image($pathtoimage);
*  // We can verify original image dimensions
*  if ( $i->getWidth() > 600 || $i->getHeight() > 400 ){
*    $i->resize(600,400,Image::RESIZE_FORCEWIDTH); 
*    // We can use any of these *constants  
*  }
*
*  $m = new Image($pathtomark);
*  $i->applyMark($m, 0, 0 );
*
*  // We need save the new image ... we can replace the old image  
*  $i->save($pathtoimage);
*/
$picdir = "../images/gallery/";
$img = $_POST['aviaryurl'];
$imgname = $_POST['imgname'];
$i = new Image($img);
$newimg = $picdir.$imgname.".jpg";
$i->save($newimg);

// echo $img." >> ".$newimg;

// Create Thumbnail
$thumb = new thumbnail($picdir.$imgname.".jpg"); 
$thumb->size_width(150);
$thumb->save($picdir."_thumbs/".$imgname.".jpg");	

class Image{
    protected $filename = null;
    protected $img = null;
    
    const RESIZE_DONTFORCE = 0;
    const RESIZE_FORCEWIDTH = 1;
    const RESIZE_FORCEHEIGHT = 2;
    
    
    public function __construct($filename){
        $this->open($filename);
    }
    
    public function open($filename){
        $this->filename = $filename;
        $datos=getimagesize ($this->filename);
        $this->width = $datos[0];
        $this->height = $datos[1];
        $this->mime = $datos[2];
        $this->img = $this->createImage($this->filename);
    }
    
    public function __destruct(){
        imagedestroy($this->img);
    }
    
    protected function createImage(){
        $img = null;
        switch( $this->mime ){
            case IMAGETYPE_GIF:
                $img = imagecreatefromgif($this->filename);
                break;
            case IMAGETYPE_JPEG:
                $img = imagecreatefromjpeg($this->filename);
                break;
            case IMAGETYPE_PNG:
                $img = imagecreatefrompng($this->filename);
                break;
            default:
                throw new Exception("Formato de imagen no soportado");
        }
        
        return $img;
    }
    
    function delete(){
        imagedestroy($this->img);
        unlink($this->filename);
    }
    
    function save($filename){

        switch($this->mime){
            case IMAGETYPE_GIF:
                imagegif($this->img,$filename);
                break;
            case IMAGETYPE_JPEG:
                imagejpeg($this->img,$filename);
                break;
            case IMAGETYPE_PNG:
                imagepng($this->img,$filename);
                break;
            default:
                throw new Exception("Formato de imagen no soportado");
        }
        return true;
    }

    function resize($w,$h, $force = 0){

        $_w = $w; $_h = $h;

        if ( $this->width < $this->height){ //Imagen Vertical
            $h = ( $w / $this->width  ) * $this->height;
        }
        
        if ( $this->width >= $this->height){ //Imagen Horizontal
            $w = ( $h / $this->height ) * $this->width;
        }

        switch ( $force ){
        
            case Image::RESIZE_DONTFORCE:           
            case Image::RESIZE_FORCEWIDTH:
                $w = $_w;
                $h = ( $w / $this->width  ) * $this->height;
           	break;
            
            case Image::RESIZE_FORCEHEIGHT:
                $h = $_h;
                $w = ( $h / $this->height ) * $this->width;
             break;
        }

        
        $img = $this->createImage();

        if ( function_exists('imagecreatetruecolor') ){
            $img2 = imagecreatetruecolor($w,$h);
            imagecopyresampled($img2,$img,0,0,0,0,$w,$h,$this->width,$this->height);
        } else {
            $img2 = imagecreate($w,$h);
            imagecopyresized($img2,$img,0,0,0,0,$w,$h,$this->width,$this->height);
        }
       
        $this->img = $img2;

        return true;    
    }
    
    public function getWidth(){ return $this->width; }
    public function getHeight(){ return $this->height; }
   
}

class thumbnail {
    var $img;

    function thumbnail($imgfile) {
        //detect image format
        $this->img["format"] = @ereg_replace(".*\.(.*)$","\\1",$imgfile);
        $this->img["format"] = strtoupper($this->img["format"]);
        if ($this->img["format"]=="JPG" || $this->img["format"]=="JPEG") {
            //JPEG
            $this->img["format"]="JPEG";
            $this->img["src"] = ImageCreateFromJPEG ($imgfile);
        } elseif ($this->img["format"]=="PNG") {
            //PNG
            $this->img["format"]="PNG";
            $this->img["src"] = ImageCreateFromPNG ($imgfile);
        } elseif ($this->img["format"]=="GIF") {
            //GIF
            $this->img["format"]="GIF";
            $this->img["src"] = ImageCreateFromGIF ($imgfile);
        } elseif ($this->img["format"]=="WBMP") {
            //WBMP
            $this->img["format"]="WBMP";
            $this->img["src"] = ImageCreateFromWBMP ($imgfile);
        } else {
            //DEFAULT
            echo "Not Supported File";
            exit();
        }
        @$this->img["height"] = imagesx($this->img["src"]);
        @$this->img["width"] = imagesy($this->img["src"]);
        //default quality jpeg
        $this->img["quality"]=75;
    }

    function size_height($size=100) {
        //height
        $this->img["width_thumb"]=$size;
        @$this->img["height_thumb"] = ($this->img["width_thumb"]/$this->img["width"])*$this->img["height"];
    }

    function size_width($size=100) {
        //width
        $this->img["height_thumb"]=$size;
        @$this->img["width_thumb"] = ($this->img["height_thumb"]/$this->img["height"])*$this->img["width"];
    }

    function size_auto($size=100) {
        //size
        if ($this->img["height"]>=$this->img["width"]) {
            $this->img["height_thumb"]=$size;
            @$this->img["width_thumb"] = ($this->img["height_thumb"]/$this->img["height"])*$this->img["width"];
        } else {
            $this->img["width_thumb"]=$size;
            @$this->img["height_thumb"] = ($this->img["width_thumb"]/$this->img["width"])*$this->img["height"];
        }
    }

    function jpeg_quality($quality=95)    {
        //jpeg quality
        $this->img["quality"]=$quality;
    }

    function show()    {
        //show thumb
        @Header("Content-Type: image/".$this->img["format"]);

        /* change ImageCreateTrueColor to ImageCreate if your GD not supported ImageCreateTrueColor function*/
        $this->img["des"] = ImageCreateTrueColor($this->img["height_thumb"],$this->img["width_thumb"]);
            @imagecopyresized ($this->img["des"], $this->img["src"], 0, 0, 0, 0, $this->img["height_thumb"], $this->img["width_thumb"], $this->img["height"], $this->img["width"]);

        if ($this->img["format"]=="JPG" || $this->img["format"]=="JPEG") {
            //JPEG
            imageJPEG($this->img["des"],"",$this->img["quality"]);
        } elseif ($this->img["format"]=="PNG") {
            //PNG
            imagePNG($this->img["des"]);
        } elseif ($this->img["format"]=="GIF") {
            //GIF
            imageGIF($this->img["des"]);
        } elseif ($this->img["format"]=="WBMP") {
            //WBMP
            imageWBMP($this->img["des"]);
        }
    }

    function save($save="") {
        //save thumb
        if (empty($save)) $save=strtolower("./thumb.".$this->img["format"]);
        /* change ImageCreateTrueColor to ImageCreate if your GD not supported ImageCreateTrueColor function*/
        $this->img["des"] = ImageCreateTrueColor($this->img["height_thumb"],$this->img["width_thumb"]);
            @imagecopyresized ($this->img["des"], $this->img["src"], 0, 0, 0, 0, $this->img["height_thumb"], $this->img["width_thumb"], $this->img["height"], $this->img["width"]);

        if ($this->img["format"]=="JPG" || $this->img["format"]=="JPEG") {
            //JPEG
            imageJPEG($this->img["des"],"$save",$this->img["quality"]);
        } elseif ($this->img["format"]=="PNG") {
            //PNG
            imagePNG($this->img["des"],"$save");
        } elseif ($this->img["format"]=="GIF") {
            //GIF
            imageGIF($this->img["des"],"$save");
        } elseif ($this->img["format"]=="WBMP") {
            //WBMP
            imageWBMP($this->img["des"],"$save");
        }
    }
}
?> 