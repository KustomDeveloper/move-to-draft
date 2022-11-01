<?php
/*
*   Plugin Name: Move To Draft
*   Plugin Description: Changes list of urls in CSV file from published to draft
*   Plugin Author: Michael Hicks
*/

/*
*  Create Settings Page 
*/
function move_to_draft_create_menu() {
	//create new top-level menu
	add_menu_page(
        'Move To Draft Settings', 
        'Move To Draft', 
        'administrator', __FILE__, 
        'move_to_draft_settings_page', 
        'dashicons-image-filter'
    );

	//call register settings function
	add_action( 'admin_init', 'register_move_to_draft_settings' );
}

add_action('admin_menu', 'move_to_draft_create_menu');

function register_move_to_draft_settings() {
	register_setting( 'move-to-draft-settings-group', 'move_to_draft_csv_url' );
	register_setting( 'move-to-draft-settings-group', 'move_to_draft_csv_header' );
}

function move_to_draft_settings_page() { ?>
    <div class="wrap">
        <h1>Move To Draft</h1>

        <form method="post" action="options.php">
            <?php settings_fields( 'move-to-draft-settings-group' ); ?>
            <?php do_settings_sections( 'move-to-draft-settings-group' );  ?>

            <div>
                <div style="margin: 15px 0;">
                    <label for="CSV Url">CSV Column Heading</label><br/>
                    <input type="text" name="move_to_draft_csv_header" value="<?php echo esc_attr( get_option('move_to_draft_csv_header') ); ?>" required />
                </div>

                <div style="margin: 15px 0;">
                    <label for="CSV Url">CSV Url</label><br/>
                    <input type="text" name="move_to_draft_csv_url" value="<?php echo esc_attr( get_option('move_to_draft_csv_url') ); ?>" required />
                </div>
            </div>

            <?php submit_button(); ?>
        </form>
    </div>

    <?php
    /*
    *   Get CSV and add urls to empty array for processing
    */

    // Set CSV Url & Header
    $csv_url = get_option('move_to_draft_csv_url');
    $csv_header = get_option('move_to_draft_csv_header');

    $request = wp_remote_get( $csv_url );

    if( is_wp_error( $request ) ) {
        return false;
    }

    $body = trim(wp_remote_retrieve_body( $request ));

    //Create an array fron results
    $arr1 = preg_split("/[\s]+/", $body);
    $csv = array_map("str_getcsv", $arr1);

    // Seperate the header from data
    $header = array_shift($csv); 
    $col = array_search( $csv_header, $header, true ); 
    foreach ($csv as $row) {      
        $array[] = $row[$col]; 
    }

    /*
    *  Change Post Status To Draft 
    */
    function change_post_status($url, $status) {
        $id = url_to_postid( $url );

        if($id !== 0) {
            $current_post = get_post( $id, 'ARRAY_A' );
            $current_post['post_status'] = $status;

            wp_update_post($current_post);
        }
    } ?>

    <?php 

    foreach($array as $url) {
        //for testing
        // echo $url . '<br/>';
        change_post_status($url, 'draft');
    }

    ?>

<?php } 