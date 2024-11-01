<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
$_POST  = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING); ?>
<form action="" method="post">
    <?php wp_nonce_field( 'VTOW_nonce', 'VTOW_nonce' ); ?>
    <p>
        Because maybe <strong>migration proccess</strong> take long time and you get <strong>php timeout error</strong>, you can import data As part of the products and do again for another part of products. <br>
    </p>
    <p><input type="number" name="offset" placeholder="Offset" value="<?=(isset($_POST['offset']))?esc_html($_POST['offset']+$_POST['count']):'';?>"></p>
    <p><input type="number" name="count" placeholder="Product count for import" value="<?=(isset($_POST['count']))?esc_html($_POST['count']):'';?>"></p>
    <p><input class="button primary" type="submit" value="Migration" name="submit"></p>

</form>