<?php

define("SHOPCART_ROOT","paperloveanddreams");
define("SHOPCART_PATH","/images/shopcart/");

class shopcart_image
{
    private $url;

    public function  __construct( $product_id, $method, $file )
    {
        ;
    }

    public function save()
    {
        
    }

    public function delete()
    {
        
    }

    public function __toString()
    {
        return '"' . $this->url . '"';
    }
}

class shopcart_images
{
    private $images = Array();
    private $path;
    private $directory;

    public function __construct( $product_id )
    {
        $path = $this->formatPath(SHOPCART_PATH);
        $this->path = $path;
        $root = $this->formatRoot($root);

        $current_dir = explode("/",__FILE__);
        if( ! $index = array_search($root, $current_dir) ) throw new Exception( 'Could not find root directory: "' . $root . "'" );
        else
        {
            for($i=1; $i<=$index; $i++)
            {
                $this->directory .= "/" . $current_dir[$i];
            }
            $this->directory .= $path;

            if( ! is_dir($this->directory) ) throw new Exception( 'the specified directory "' . $this->directory . '" does not exist.' );
            else
            {
                $dir_contents = scandir($this->directory);

                foreach($dir_contents as $image)
                {
                    $type = substr($image, -4);
                    if( $type == ".jpg" || $type == ".png" || $type == ".gif" )array_push($this->images, $image);
                }
                if( count($this->images) < 1 ) throw new Exception( "No images found at directory '" . cycleImage::$directory . "'" );
            }
        }
    }

    public function count(){ return count( $this->images ); }

    private function formatRoot( $root )
    {
        return str_replace("/", "", $root);
    }

    private function formatPath( $path )
    {
        $dirs = explode("/", $path);
        $p;

        for( $i=0;$i<count($dirs);$i++ )
        {
            if( $dirs[$i] != "" )$p .= "/" . $dirs[$i];
        }
        return $p . "/";
    }

    public function __toString()
    {
        $output = "[";

        for($i=0; $i<$this->count(); $i++)
        {
            if( $i != 0 )$output .= ",";
            $output .= $this->images[$i];
        }

        return $output . "]";
    }
}