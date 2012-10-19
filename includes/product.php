<?php

include_once("db.php");

define("DB_PRODUCTS", "products");
define("DB_CATEGORIES", "categories");

class shopcart_product
{
    private $id;
    public function getId(){  return $this->id; }

    private $last_modified;
    private $method;
    private $is_new = false;

    public $name;
    public $description;
    public $category;

    public $unit_price;
    public $set_amount;
    public $set_price;
    public $shipping_base;
    public $shipping_add;

    public $monogram;

    public $is_full_set;

    public $images = Array();
    public $main_image;

    public function  __construct(  $method, $p )
    {
        $this->method = $method;

        switch ( $method )
        {
            case "create":
                $this->is_new = true;

                if( shopcart_product::exists($p['id']) ) throw new Exception("Cannot create new product, ID supplied already exists.");

                $clean = $this->sanitizeProduct($p);
                $this->setProduct($clean);
                $this->save();
                
                break;

            case "retrieve":
                if( ! shopcart_product::exists($p) ) throw new Exception("Cannot get product, ID[$p] supplied does not exist. [".$_SERVER["REQUEST_URI"]);

                db::query("SELECT * FROM ".DB_PRODUCTS." WHERE id=:id", Array( ":id" => $p ));
                $product = db::fetch_assoc();
                $this->setProduct($product);

                break;

            case "modify":

                if( ! shopcart_product::exists($p['id']) ) throw new Exception("Cannot modify product, ID supplied does not exist.");

                $clean = $this->sanitizeProduct($p);
                $this->setProduct($clean);
                $this->save();
                
                return;

            case "build":

                $this->setProduct($p);
                
                break;
        }
    }

    public function calculate_subtotal( $quantity, $isSet, $isMono )
    {
        $price = $isSet ? floatval( $this->set_price ) : floatval( $this->unit_price );
        $subtotal = floatval( $quantity ) * $price;
        if( $isMono )$subtotal += floatval( $this->monogram );

        return $subtotal;
    }

    public function save( )
    {
        try{
            if( ! categories::exists($this->category) )categories::add ($this->category);
        }
        catch(Exception $e){ throw new Exception ("Failed to add new category in database."); }

        if( $this->is_new )
        {
            try{
                db::query("INSERT INTO ".DB_PRODUCTS." ".
                "(id,last_modified,name,description,category,unit_price,set_amount,set_price,shipping_base,shipping_add,monogram,is_full_set) VALUES ".
                "(:id,:last_modified,:name,:description,:category,:unit_price,:set_amount,:set_price,:shipping_base,:shipping_add,:monogram,:is_full_set)",
                Array(
                    ":id" => $this->id,
                    ":last_modified" => db::to_mysql_timestamp( time() ),
                    ":name" => $this->name,
                    ":description" => $this->description,
                    ":category" => $this->category,
                    ":unit_price" => $this->unit_price,
                    ":set_amount" => $this->set_amount,
                    ":set_price" => $this->set_price,
                    ":shipping_base" => $this->shipping_base,
                    ":shipping_add" => $this->shipping_add,
                    ":monogram" => $this->monogram,
                    ":is_full_set" => $this->is_full_set
                ));
            }
            catch(Exception $e){ throw new Exception ("Failed to add new product in database."); }
        }
        else
        {
            try{
            db::query("UPDATE ".DB_PRODUCTS." SET last_modified=:last_modified, name=:name, description=:description, category=:category, unit_price=:unit_price, set_amount=:set_amount, set_price=:set_price, shipping_base=:shipping_base, shipping_add=:shipping_add, monogram=:monogram, is_full_set=:is_full_set WHERE id=:id",
                Array(
                    ":id" => $this->id,
                    ":last_modified" => db::to_mysql_timestamp( time() ),
                    ":name" => $this->name,
                    ":description" => $this->description,
                    ":category" => $this->category,
                    ":unit_price" => $this->unit_price,
                    ":set_amount" => $this->set_amount,
                    ":set_price" => $this->set_price,
                    ":shipping_base" => $this->shipping_base,
                    ":shipping_add" => $this->shipping_add,
                    ":monogram" => $this->monogram,
                    ":is_full_set" => $this->is_full_set
                ));
            }
            catch(Exception $e){ throw new Exception ("Failed to update product in database."); }
        }
    }

    public function delete( )
    {
        //TODO
    }

    public function __toString( )
    {
        $images = $this->get_images(TEMP_IMAGE_URL,TEMP_IMAGE_LOC);

        return "{'id':'".htmlspecialchars( $this->id, ENT_QUOTES )."',
                'name':'".htmlspecialchars( $this->name, ENT_QUOTES )."',
                'last_modified':'".htmlspecialchars( $this->last_modified, ENT_QUOTES )."',
                'description':'".preg_replace('/\r\n/', "\\r\\n", htmlspecialchars( $this->description, ENT_QUOTES ) )."',
                'category':'".htmlspecialchars( $this->category, ENT_QUOTES )."',
                'unit_price':'".htmlspecialchars( $this->unit_price, ENT_QUOTES )."',
                'set_amount':'".htmlspecialchars( $this->set_amount, ENT_QUOTES )."',
                'set_price':'".htmlspecialchars( $this->set_price, ENT_QUOTES )."',
                'shipping_base':'".htmlspecialchars( $this->shipping_base, ENT_QUOTES )."',
                'shipping_add':'".htmlspecialchars( $this->shipping_add, ENT_QUOTES )."',
                'monogram':'".htmlspecialchars( $this->monogram, ENT_QUOTES )."',
                'is_full_set':'".htmlspecialchars( $this->is_full_set, ENT_QUOTES )."',
                'images':$images}";
    }



    private function get_images( $url, $loc )
    {
        $loc .= $this->id."/";
        $url .= $this->id."/";

        if( ! is_dir( $loc ) )return "[]";
        $dir_contents = scandir( $loc );
        
        $images = Array();
        $output = "[";

        foreach($dir_contents as $image)
        {
            $type = substr($image, -4);
            if( $type == ".jpg" || $type == ".png" || $type == ".gif" )array_push($images, $image);
        }

        $this->main_image = $url . $images[1];

        $first = true;
        foreach($images as $image)
        {
            if( ! $first ) $output .= ",";
            $output .= "'" .$url . $image . "'";
            $first = false;
        }

        return $output . "]";
    }

    private static function exists( $id )
    {
        db::query("SELECT id FROM ".DB_PRODUCTS." WHERE id=:id", Array( ":id" => substr( $id, 0, 20) ) );
        if( db::num_rows() > 0 )return true;
        else return false;
    }

    private static function floatify( $number )
    {
        $matches;
        $pattern= "/[0-9]+(\.[0-9]+)?/";

        preg_match($pattern, substr( $number, 0, 20 ), $matches);
        
        return floatval( $matches[0] );
    }

    private function setProduct ( $p )
    {
        $this->id = $p["id"];
        $this->last_modified = db::to_php_timestamp($p["last_modified"]);
        $this->name = $p["name"];
        $this->description = $p["description"];
        $this->category = $p["category"];
        $this->unit_price = $p["unit_price"];
        $this->set_amount = $p["set_amount"];
        $this->set_price = $p["set_price"];
        $this->shipping_base = $p["shipping_base"];
        $this->shipping_add = $p["shipping_add"];
        $this->monogram = $p["monogram"];
        $this->is_full_set = $p["is_full_set"];
    }

    private function sanitizeProduct( $p )
    {
        $clean = Array();

        // make sure each is set and has minimum value
        if( !isset( $p["id"] ) || strlen($p["id"]) < 2 ||
            !isset( $p["name"] ) || strlen($p["name"]) < 2 ||
            !isset( $p["description"] ) || strlen($p["description"]) < 2 ||
            !isset( $p["category"] ) || strlen($p["category"]) < 2 ||
            !isset( $p["unit_price"] ) || strlen($p["unit_price"]) < 1 ||
            !isset( $p["set_amount"] ) ||
            !isset( $p["set_price"] ) ||
            !isset( $p["shipping_base"] ) || strlen($p["shipping_base"]) < 1 ||
            !isset( $p["shipping_add"] ) || strlen($p["shipping_add"]) < 1 ||
            !isset( $p["monogram"] ) ||
            !isset( $p["is_full_set"] ) || strlen($p["is_full_set"]) < 2
        )throw new Exception ("Not all product variables were provided. Could not sanitize.");

        // clean line feeds to prevent break on javascript output, substring to prevent overflow
        $clean["id"] = substr( preg_replace('/[\r\n]+/', "", $p["id"]), 0, 20);
        $clean["name"] = substr( preg_replace('/[\r\n]+/', "", $p["name"]), 0, 60);
        $clean["description"] = substr($p["description"], 0, 2048);
        $clean["category"] = substr( preg_replace('/[\r\n]+/', "", $p["category"]), 0, 30);
        $clean["unit_price"] = shopcart_product::floatify( $p["unit_price"] );
        $clean["set_amount"] = intval( $p["set_amount"] );
        $clean["set_price"] = shopcart_product::floatify( $p["set_price"] );
        $clean["shipping_base"] = shopcart_product::floatify( $p["shipping_base"] );
        $clean["shipping_add"] = shopcart_product::floatify( $p["shipping_add"] );
        $clean["monogram"] = shopcart_product::floatify( $p["monogram"] );
        $clean["is_full_set"] = substr( preg_replace('/[\r\n]+/', "", $p["is_full_set"]), 0, 3);

        return $clean;
    }
}

class shopcart_products
{
    private $list = Array();    public function get_raw_list(){ return $this->list; }

    public function __construct( $method = "all", $check = false )
    {
        switch ( $method )
        {
            case "category":
                db::query("SELECT * FROM ".DB_PRODUCTS." WHERE category=:category",
                        Array( ":category" => $check ) );
                break;
            case "all":
            default:
                db::query("SELECT * FROM ".DB_PRODUCTS);
                break;
        }
        while( $aProduct = db::fetch_assoc() ) array_push( $this->list, new shopcart_product( "build", $aProduct ) );
    }

    public function count(){ return count( $this->list ); }

    public function __toString()
    {
        $output = "[";
        
        for($i=0; $i<$this->count(); $i++)
        {
            if( $i != 0 )$output .= ",";
            $output .= $this->list[$i];
        }

        return $output . "]";
    }
}

class categories
{
    private static $raw = Array();
    private static $js;
    private static $active = Array();
    private static $active_js;

    public static function get_js_array( $only_active_categories = false )
    {
        if( ! categories::$js )categories::load_categories();

        if( $only_active_categories )return categories::$active_js;
        else return categories::$js;
    }

    public static function get_raw_array( $only_active_categories = false )
    {
        if( count(categories::$raw) == 0 )categories::load_categories();

        if( $only_active_categories )return categories::$active;
        else return categories::$raw;
    }

    public static function exists( $category_name )
    {
        db::query("SELECT category FROM ".DB_CATEGORIES." WHERE category=:category", Array(":category" => $category_name));

        if( db::num_rows() <= 0 ) return false;
        else return true;
    }

    public static function set_activity( $active, $category )
    {
        db::query("UPDATE ".DB_CATEGORIES." SET active=:active WHERE category = :category", Array( ":active" => $active, ":category" => $category ) );
    }

    public static function rename( $old, $new )
    {
        db::query("UPDATE ".DB_CATEGORIES." SET category=:new WHERE category = :old", Array( ":old" => $old, ":new" => $new ) );
    }

    public static function delete( $category )
    {
        $category_check = new shopcart_products( "category", $category );
        if( $num = $category_check->count() > 0 )throw new Exception("Could not delete category '".$category."' while it still has $num products using it.");

        try{
            db::query("DELETE FROM ".DB_CATEGORIES." WHERE category = :category", Array( ":category" => $category ));
        }catch(Exception $e)
        {
            throw new Exception("Could not delete category, an error occured during the database query.");
        }
    }

    public static function add( $category_name )
    {
        db::query("SELECT category FROM ".DB_CATEGORIES);
        
        $num_categories = db::num_rows();

        db::query ("INSERT INTO ".DB_CATEGORIES." (category,list_order) VALUES (:category,:list_order)", Array( ":category" => $category_name, ":list_order" => $num_categories ));
    }

    public static function reorder( $newOrder )
    {
        $orderArray = explode("#", $newOrder);

        for( $i=0; $i<count($orderArray); $i++ )
        {
            db::query("UPDATE ".DB_CATEGORIES." SET list_order=:list_order WHERE category = :category", Array( ":list_order" => $i, ":category" => $orderArray[$i] ) );
        }
    }

    private static function load_categories()
    {
        db::query("select * FROM ".DB_CATEGORIES." ORDER BY list_order");

        $first = true;
        $firstActive = true;
        categories::$raw = Array();
        categories::$js = "[";
        categories::$active_js = "[";

        while( $row = db::fetch_assoc() )
        {
            categories::$raw[] = $row["category"];
            if( $row["active"] ){
                categories::$active[] = $row["category"];
                
                if( ! $firstActive )categories::$active_js .= ",";
                categories::$active_js .= "'" . $row["category"] . "'";
                $firstActive = false;
            }

            if( ! $first )categories::$js .= ",";
            categories::$js .= "'" . $row["category"] . "'";
            $first = false;


        }
        
        categories::$js .= "]";
        categories::$active_js .= "]";

        return true;
    }
}