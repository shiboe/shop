<?php

    $allCategories = categories::get_js_array( true );
    $allProducts = new shopcart_products("category", $category);

ob_start(); ?>

<div id="shopCart">

    <script type="text/javascript">
        var PAGE = "category",
            ALLPRODUCTS = <?php echo $allProducts; ?>,
            ALLCATEGORIES = <?php echo $allCategories; ?>,
            CATEGORY = '<?php echo $category; ?>';
    </script>

    <hr class="bigBar" style="margin-top:0px; margin-bottom:4px;">

<!-- CATEGORY -->

    <div id="categoryMenu">
        <a class="template category" href="">category</a>
    </div>

    <hr class="bigBar" style="margin-top:4px;">

    <img id="collectionHeader" />

    <hr class="bigBar">

    <div id="productList">
        <a class="template product" href="">
            <div class="thumb"><img /></div>
            <div class="priceTag">$ 0.00 US</div>
            <div class="name">Product Name</div>
        </a>
    </div>

    <div style="clear:both;">&nbsp;</div>
</div>

<?php echo ob_get_clean();