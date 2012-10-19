<?php
    
    $allCategories = categories::get_raw_array( true );
    $output;

    for($i=0; $i<count($allCategories); $i++){
        $output .= "<a href='/shop/".str_replace( " ","_", strtolower($allCategories[$i]) )."'>$allCategories[$i]</a>";
    }
    
    ob_start(); ?>

    <script type='text/javascript'>
        jQuery(document).ready(function(){ 
            jQuery('#shopMenu').css("height", <?php echo count($allCategories); ?> * 46 );
            jQuery("#shoplink").toggle(function(){popShopMenu();},function(){hideShopMenu();});
        });

        function popShopMenu(){ jQuery('#shopMenu').css("display","block"); }
        function hideShopMenu(){ jQuery('#shopMenu').css("display","none"); }
    </script>

    <?php

    echo "<!-- menu [length:".count($allCategories)." -->";
    echo $output;

    echo ob_get_clean();