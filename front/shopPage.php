<?php

    $allCategories = categories::get_js_array( true );

ob_start(); ?>

<div id="shopCart">

    <script type="text/javascript">
        var PAGE = "shop",
            ALLCATEGORIES = <?php echo $allCategories; ?>;
    </script>

    <hr class="bigBar" style="margin-top:0px; margin-bottom:4px;">

    <div id="categoryMenu">
        <a class="template category" href="/shop/category/">category</a>
    </div>
    
</div>

<?php echo ob_get_clean();