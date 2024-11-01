<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<p>Maybe some hosts do not permission to database connection from another hosts.<br>
    for this reason my suggestion is: first export virtuemart database (from phpmyadmin or host panel) then import that in your current host. this way is so fast and safer.</p>
<form action="" method="post">
    <?php wp_nonce_field( 'VTOW_nonce', 'VTOW_nonce' ); ?>
    <p><input type="text" name="host" placeholder="Host" value="<?=(get_option('vtm_Host'))?esc_html(get_option('vtm_Host')):'localhost';?>"></p>
    <p><input type="text" name="db" placeholder="Database name" value="<?=esc_html(get_option('vtm_db'));?>"></p>
    <p><input type="text" name="dbUser" placeholder="Database username" value="<?=esc_html(get_option('vtm_dbUser'));?>"></p>
    <p><input type="text" name="dbPass" placeholder="Database password" value="<?=esc_html(get_option('vtm_dbPass'));?>"></p>
    <p><input type="text" name="vrm_prefix" placeholder="Virtuemart database prefix" value="<?=esc_html(get_option('vrm_prefix'));?>"></p>
    <p><input type="submit" value="Save" name="vtm_setting_submit" class="button primary"></p>
</form>
<?php
//update_option('tax_ids','');
//update_option('attribute_ids','');
?>