<?php

function shopcart_settingsPage()
{
    $nonce_action = "shopcart_settings";
    $nonce_name = "fauncy_nauncy";

    $failed = false;
    $success = false;

    try
    {
        if( isset($_POST["categories"]) )
        {
            check_admin_referer($nonce_action,$nonce_name);

            categories::reorder( $_POST["categories"] );
            $success = "Categories re-ordered successfully!";
        }

        if( isset($_POST["active"]) || isset($_POST["inactive"]) )
        {
            check_admin_referer($nonce_action,$nonce_name);
            if( isset($_POST["active"]) ) categories::set_activity (1, $_POST["active"]);
            else categories::set_activity (0, $_POST["inactive"]);
            $success = "Category active status adjusted successfully!";
        }

        if( isset($_POST["rename"]) )
        {
            $cats = explode("#",$_POST["rename"]);
            categories::rename($cats[0], $cats[1]);
            $success = "Category '".$cats[0]."' renamed to '".$cats[1]."' successfully! All corresponding products have been updated.";
        }

        if( isset($_POST["delete"]) )
        {
            categories::delete( $_POST['delete'] );
            $success = "Category '".$_POST['delete']."' deleted successfully!";
        }
    }
    catch(Exception $e)
    {
        $failed = $e->getMessage();
    }

    $allProducts = new shopcart_products("all");
    $allCategories = categories::get_js_array();
    $activeCategories = categories::get_js_array( true );

    ob_start(); ?>

    <script type="text/javascript" src="<?php echo plugin_dir_url(__FILE__); ?>../js/shopcart-admin.js"></script>
    <script type="text/javascript" src="<?php echo plugin_dir_url(__FILE__); ?>../js/jquery.dragOrder.js"></script>
    <link rel="stylesheet" type="text/css" href="<?php echo plugin_dir_url(__FILE__); ?>../css/shopcart-admin.css" />

    <script type="text/javascript">
        var PAGE = "settings",
            ALLPRODUCTS = <?php echo $allProducts; ?>,
            ALLCATEGORIES = <?php echo $allCategories; ?>;
            ACTIVECATEGORIES = <?php echo $activeCategories; ?>;
    </script>

    <div id="settingsPage" class="wrap adminContainer">
        <h2>Settings <span style="font-size:.5em;">Shopcart</span></h2>

        <?php if( $failed ) echo "<p class='failText'>$failed</p>"; ?>
        <?php if( $success ) echo "<p class='successText'>$success</p>"; ?>

        <form id="categorySettingsForm" method="post" action="/wp-admin/admin.php?page=shopcart">

            <?php wp_nonce_field($nonce_action,$nonce_name); ?>

            <h3>Order Categories:</h3>

            <div id="categoriesList">
                <div class="template categoryListItem">
                    <div class="isActive"><label>display? </label><input type="checkbox" /></div>
                    <input class="button-primary rename" type="button" value="rename" />
                    <input class="button-primary delete" type="button" value="delete" />
                    <span class="categoryName"></span>
                    <span class="productsIn"></span>
                    
                </div>
            </div>

            <input id="categorySettingsSubmit" class="button-primary" style="clear:both; margin:40px; width:auto;" type="button" value="Adjust categories" />

            <footer><?php include('_footerContent.php'); ?></footer>
        </form>
    </div>

    <?php echo ob_get_clean();
}


