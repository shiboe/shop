<?php

    include_once("db.php");

    class shopcart_order
    {
        private static $order = Array();
        const db = "pre_orders";
        const db_completed = "completed_orders";

        // sales numbers
        const sales_tax = .085;
        private $sales_tax = 0.0;     public function get_sales_tax(){ return round( $this->sales_tax, 2 ); }
        private $subtotal = 0.0;      public function get_subtotal(){ return round( $this->subtotal, 2 ); }
        private $shipping = 0.0;      public function get_shipping(){ return round( $this->shipping, 2 ); }
        private $total = 0.0;         public function get_total(){ return round( $this->total, 2 ); }

        // pre-order inclusive
        private $orderString;
        private $zip;               public function get_zip(){ return $this->zip; }
        private $is_california;     public function is_california(){ return $this->is_california; }
        private $promo;             public function get_promo(){ return $this->promo; }
        private $id;                public function get_id(){ return $this->id; }

        private $timestamp;

        // exclusive completed order
        private $email;
        private $address;
        private $name;
        private $country;
        private $city;
        private $state;
        private $phone;

        private $charged;
        private $transaction_id;

        public function  __construct( $existingID = false )
        {
            try
            {   
                //start by clearing out older pre-orders, defined as older than 1 hour
                db::query("DELETE FROM ".shopcart_order::db." WHERE created_on < :max_life",
                        Array( ":max_life" => db::to_mysql_timestamp( time()-3600 ) ) );
            }
            catch(Exception $e){ throw new Exception ("Failed to clear old records."); }

            if( $existingID ) $this->load_pre_order( substr($existingID,0,32) );
        }

        public function check_for_pre_order_updates()
        {
            if( isset( $_POST["ZIP"] ) )
            {
                if( ! is_numeric( $_POST["ZIP"] ) || strlen($_POST["ZIP"]) < 5 || strlen($_POST["ZIP"]) > 5 )throw new Exception("ZIP code was not of proper form.");
                else $this->zip = intval($_POST["ZIP"]);
            }
            elseif( ! $this->zip ) return false;

            if( isset( $_POST["promo"] ) )$this->promo = $_POST["promo"];
            if( isset( $_COOKIE["shopcart_order"] ) )$this->orderString = $_COOKIE["shopcart_order"];
            $this->is_california = $this->zip > 90000 && $this->zip < 96200;

            if( ! isset( $_POST["ZIP"] ) && ! isset( $_POST["promo"] ) && ! isset( $_COOKIE["shopcart_order"] ) )return false;
            else return true;
        }

        private function load_pre_order( $id )
        {
            db::query("SELECT * FROM ".shopcart_order::db." WHERE id = :id", Array( ":id" => $id ) );
            if( db::num_rows() < 1 ) throw new Exception ("Could not match pre-order to that stored in database.");
            $order = db::fetch_assoc();

            $this->id = $order["id"];
            $this->orderString = $order["orderString"];
            $this->timestamp = db::to_php_timestamp( $order["created_on"] );
            $this->is_california = $order["is_california"];
            $this->zip = $order["zip"];
            $this->promo = $order["promo"];
        }

        public function create_pre_order()
        {
            $this->timestamp = time();
            $this->id = $this->hash_id();

            try
            {
                // now that all values are set, lets save a pre-order to the db
                db::query("INSERT INTO ".shopcart_order::db." ".
                "(id,created_on,zip,promo,is_california,orderString) VALUES ".
                "(:id,:created_on,:zip,:promo,:is_california,:orderString)",
                Array(
                    ":id" => $this->id,
                    ":created_on" => db::to_mysql_timestamp( $this->timestamp ),
                    ":zip" => $this->zip,
                    ":promo" => $this->promo,
                    ":is_california" => $this->is_california,
                    ":orderString" => $this->orderString
                ));
            }
            catch(Exception $e){ throw new Exception ("Failed to create pre-order in database."); }
        }

        public function update_pre_order()
        {
            try
            {
                // now that all fields have been updated as necessary, lets save our updtated pre-order to the db
                db::query("UPDATE ".shopcart_order::db." SET zip=:zip,promo=:promo,is_california=:is_california,orderString=:orderString WHERE id=:id ",
                Array(
                    ":id" => $this->id,
                    ":zip" => $this->zip,
                    ":promo" => $this->promo,
                    ":is_california" => $this->is_california,
                    ":orderString" => $this->orderString
                ));
            }
            catch(Exception $e){ throw new Exception ("Failed to update pre-order in database."); }
        }

        public static function exportJavascriptArray()
        {
            $orders = db::query("SELECT * FROM ".shopcart_order::db_completed." ORDER BY created_on DESC");
            $output = "[";
            $first = true;

            while( $row = db::fetch_assoc() )
            {
                if( ! $first )$output .= ",";
                $output .= "{".
                    "id:'".htmlspecialchars( $row["id"] )."',".
                    "created_on:".db::to_php_timestamp( $row["created_on"] ).",".
                    "zip:".htmlspecialchars($row["zip"]).",".
                    "promo:'".htmlspecialchars($row["promo"])."',".
                    "is_california:".htmlspecialchars($row["is_california"]).",".
                    "orderString:'".htmlspecialchars($row["orderString"])."',".
                    "email:'".htmlspecialchars($row["email"])."',".
                    "address:'".htmlspecialchars($row["address"])."',".
                    "name:'".htmlspecialchars($row["name"])."',".
                    "country:'".htmlspecialchars($row["country"])."',".
                    "city:'".htmlspecialchars($row["city"])."',".
                    "state:'".htmlspecialchars($row["state"])."',".
                    "phone:'".htmlspecialchars($row["phone"])."',".
                    "charged:'".htmlspecialchars($row["charged"])."',".
                    "transaction_id:'".htmlspecialchars($row["transaction_id"])."'"
                ."}";
                $first = false;
            }

            return $output . "]";
        }

        public function complete( $newvars )
        {
            $this->email = substr($newvars["buyerEmail"],0,60);
            $this->address = substr($newvars["addressLine1"],0,100)."\\r\\n".substr($newvars["addressLine2"],0,100);
            $this->name = substr($newvars["buyerName"],0,60);
            $this->country = substr($newvars["country"],0,30);
            $this->city = substr($newvars["city"],0,30);
            $this->state = substr($newvars["state"],0,2);
            $this->phone = substr($newvars["phoneNumber"],0,14);
            $this->charged = substr($newvars["transactionAmount"],0,12);
            $this->transaction_id = substr($newvars["transactionId"],0,60);

            db::query("INSERT INTO ".shopcart_order::db_completed." ".
                "(id,created_on,zip,promo,is_california,orderString,email,address,name,country,city,state,phone,charged,transaction_id) VALUES ".
                "(:id,:created_on,:zip,:promo,:is_california,:orderString,:email,:address,:name,:country,:city,:state,:phone,:charged,:transaction_id)",
                Array(
                    ":id" => $this->id,
                    ":created_on" => db::to_mysql_timestamp( $this->timestamp ),
                    ":zip" => $this->zip,
                    ":promo" => $this->promo,
                    ":is_california" => $this->is_california,
                    ":orderString" => $this->orderString,
                    ":email" => $this->email,
                    ":address" => $this->address,
                    ":name" => $this->name,
                    ":country" => $this->country,
                    ":city" => $this->city,
                    ":state" => $this->state,
                    ":phone" => $this->phone,
                    ":charged" => $this->charged,
                    ":transaction_id" => $this->transaction_id
                ));

            db::query("DELETE FROM ".shopcart_order::db." WHERE id = :id",Array(":id" => $this->id));
        }

        public function emailSellerReciept( $send_to )
        {
            $emailContent = "<h1>Order processed from paperloveanddreams.com</h1>";
            $emailContent .= "<h3>".date("l, M jS, Y (g:i T)",$this->timestamp)."</h3>";
            $emailContent .= "<h4>transaction ID (amazon) : <a target='_blank' href='https://payments.amazon.com/sdui/sdui/txndetail?transactionId=".$this->transaction_id."'>".$this->transaction_id."</a></h4>";
            $emailContent .= "<ul>".$this->htmlListItem("Buyer Name", $this->name)
                                   .$this->htmlListItem("Email", $this->email)
                                   .$this->htmlListItem("Phone", $this->phone)."</ul>";
            $emailContent .= "<div style='width:500px;padding:10px;margin:20px;border:1px solid lightgrey;'>".str_replace("\\r\\n","<br>",$this->address)."<br>".$this->city." ".$this->state.", ".$this->zip."<br>".$this->country."</div>";
            $emailContent .= "<ul>".$this->htmlListItem("Promo", $this->promo).$this->htmlListItem("Charged", $this->charged)."</ul>";

            $productInfoString = "<br><table cellspacing='2' cellpadding='8' style='border:1px solid lightgrey; margin-left:20px;'>";
            $productInfoString .= "<tr><td style='border:1px solid lightgrey;'>product id</td><td style='border:1px solid lightgrey;'>quantity</td><td style='border:1px solid lightgrey;'>type</td><td style='border:1px solid lightgrey;'>additional</td></tr>";
            $orders = explode("##", $this->orderString);

            foreach( $orders as $o )
            {
                $orderInfo = explode("||",$o);
                
                $typeString;
                $details;

                switch( $orderInfo[1] ){
                    case "0":
                        $typeString = "a single";
                        break;
                    case "1":
                        $typeString = "a set";
                        break;
                    case "2":
                        $typeString = "custom print";
                        $details = $orderInfo[3] . "<br>" . $orderInfo[4] . "<br>" . $orderInfo[5];
                        break;
                    case "3":
                        $typeString = "a single w/ monogram";
                        $details = $orderInfo[3];
                        break;
                    case "4":
                        $typeString = "a set w/ monogram";
                        $details = $orderInfo[3];
                        break;
                }

                $productInfoString .= "<tr><td style='border:1px solid lightgrey;'>$orderInfo[0]</td><td style='border:1px solid lightgrey;'>$orderInfo[2]</td><td style='border:1px solid lightgrey;'>$typeString</td><td style='border:1px solid lightgrey;'>$details</td></tr>";
            }
            $productInfoString .="</table><br>";

            $emailContent .= $productInfoString;

            $emailContent .= "<h5>Sent by shopcart plugin @ ".$_SERVER["SERVER_NAME"]."</h5>";

            $mail_headers = "MIME-Version: 1.0" . "\r\n" . "Content-type: text/html; charset=iso-8859-1"."\r\n"."From: " . $this->email . "\r\n"."Return-Path: ".$this->email."\r\n"."Reply-To: ".$this->email."\r\n";
            $mail_subject = "Order processed - ".$this->name." [".$this->email."]";

            mail($send_to, $mail_subject, $emailContent, $mail_headers);
            
            $this->emailBuyerReciept($this->charged, $productInfoString, $this->email );
        }

        public function emailBuyerReciept( $total, $productString, $emailTo )
        {
            $emailBuyerContent = "<h1>Your Order from paperloveanddreams.com</h1>";
            $emailBuyerContent .= $productString;
            $emailBuyerContent .= "<h3>Amount Charged: <b>".$total."</b></h3>";
            $emailBuyerContent .= "<p>Thank you so much for your order!<br><br>Questions, concerns?<br><a href='http://www.paperloveanddreams.com/contact/'>Contact me @ paperloveanddreams.com</a><br><br>xoxo Grace</p>";

            $mail_headers = "MIME-Version: 1.0" . "\r\n" . "Content-type: text/html; charset=iso-8859-1"."\r\n"."From: billing@paperloveanddreams.com"."\r\n"."Reply-To: info@paperloveanddreams.com"."\r\n"."Return-Path: info@paperloveanddreams.com\r\n";

            $mail_subject = "Order Confirmation from paperloveanddreams.com";

            mail($emailTo, $mail_subject, $emailBuyerContent, $mail_headers );
        }

        private function htmlListItem( $title, $value )
        {
            return "<li><i>".$title."</i> : <b>".$value."</b></li>";
        }

        private function hash_id()
        {
            return md5( $this->orderString . $this->zip . $this->timestamp );
        }

        public static function load( $order_string )
        {
            $orders = explode(chr(206),$order_string);

            foreach($orders as $order)
            {
                shopcart_order::$order[] = new shopcart_order_item($order);
            }
        }

        public function calculate_costs( $allProducts )
        {
            $orders = explode( "##", $this->orderString );

            $subtotal = 0.0;
            $shipBase = 0.0;
            $shipAdd = 0.0;

            for( $i=0; $i<count($orders); $i++ )
            {
                $order = explode( "||", $orders[$i] );
                $id = $order[0];
                $thisProduct;

                for( $j=0; $j<count($allProducts); $j++ )
                {
                    if( $allProducts[$j]->getId() == $id ){
                        $thisProduct = $allProducts[$j];
                        break;
                    }
                }

                $quantity = $order[2];
                $isSet = $order[1] == "1" || $order[1] == "4";
                $isMono = $order[1] == "3" || $order[1] == "4";

                $subtotal += $thisProduct->calculate_subtotal( $quantity, $isSet, $isMono );

                if( $thisProduct->shipping_base > $shipBase ) $shipBase = floatval( $thisProduct->shipping_base );
                $shipAdd += floatval( $thisProduct->shipping_add ) * floatval($quantity);
            }

            $this->subtotal = $subtotal;
            $this->shipping = $shipBase + $shipAdd;
            if( $this->is_california ) $this->sales_tax = shopcart_order::sales_tax * $subtotal;
            
            $this->total = $this->subtotal + $this->shipping + $this->sales_tax;
        }
    }

    class shopcart_order_item
    {
        private $product_id;
        private $type; // 0 = single, 1 = set, 2 = custom, 3 = single w/mono, 4 = single w/mono

        private $quantity;

        private $monogram;

        private $names;
        private $date;
        private $location;

        private $cookieString;

        public function __construct( $order_string )
        {
            $this->cookieString = $order_string;
            $order_array = explode(chr(197), $order_string);

            $this->product_id = $order_array[0];
            $this->type = $order_array[1];
            $this->quantity = $order_array[2];

            switch ( $this->type )
            {
                case "3":
                case "4":
                    $this->monogram = $order_array[3];
                    break;
                case "2":
                    $this->names = $order_array[3];
                    $this->date = $order_array[4];
                    $this->location = $order_array[5];
                    break;
            }
        }

        public function  __toString()
        {
            $output = "{ 'id':'".htmlspecialchars( $this->product_id )."','type':'".htmlspecialchars( $this->type )."','quantity':'".htmlspecialchars( $this->quantity )."'";

            switch ( $this->type )
            {
                case "3":
                case "4":
                    $output .= ",'monogram':'".  htmlspecialchars($this->monogram)."'}";
                    break;
                case "2":
                    $output .= ",'names':'".  htmlspecialchars($this->names)."','date':'".  htmlspecialchars($this->date)."','location':'".  htmlspecialchars($this->location)."'}";
                    break;
            }

            return $output;
        }

        public function toCookieString(){ return $this->cookieString; }
    }