<?php
/*
Plugin Name: Add Contributor
Plugin URI: http://joeboydston.com/add-contributor/
Description: Plugin allows Editor and Admin roles to add Contributors
Author: Joe Boydston
Version: 1.4
Author URI: http://joeboydston.com
*/

define( 'ADD_CONTIBUTOR_URL' , plugins_url(plugin_basename(dirname(__FILE__)).'/') );
define( 'ADD_CONTIBUTOR_PAGE', 'add-contributor-menu' );

add_action('admin_menu', 'add_contrib_menu');
add_action('admin_enqueue_scripts', 'add_contrib_admin_scripts');
add_action( 'admin_print_styles', 'add_contrib_admin_styles' );

function add_contrib_admin_scripts() {
    if ((array_key_exists('page', $_GET)) && ($_GET['page'] == ADD_CONTIBUTOR_PAGE)) {
	    wp_enqueue_script("add-contributor-js", ADD_CONTIBUTOR_URL . "js/jquery.validate.min.js", array('jquery'), '1.0');	
    }
    
}

function add_contrib_admin_styles() {
    if ((array_key_exists('page', $_GET)) && ($_GET['page'] == ADD_CONTIBUTOR_PAGE)) {
	    wp_enqueue_style( 'add_contributor-css', ADD_CONTIBUTOR_URL . 'css/add_contributor.css', false );
    }
}

function add_contrib_menu() {
    add_management_page('Add Contributor', 'Add Contributor', 'edit_pages', 'add-contributor-menu', 'add_contrib_function');
}

function add_contrib_function() {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $name = $_POST['fname'] . " " . $_POST['lname'];
        $username = strtolower(preg_replace('/ /', '', $name));
        $fname = mb_convert_case($_POST['fname'], MB_CASE_TITLE, "UTF-8");
        $lname = mb_convert_case($_POST['lname'], MB_CASE_TITLE, "UTF-8");
        
        $u = get_user_by('email', $_POST['email']);
        
        if ($u) { 
            echo '<div id="message" class="updated"><p>';
            echo "User with this email address (" . $_POST['email'] . ") already exists.<br/>";
            echo "<a href=\"" . get_admin_url() . "tools.php?page=add-contributor-menu\">Please try again.</a><br/>";
            echo '</p></div>';
            return;
        }
        
        $u = get_user_by('login', strtolower(preg_replace('/ /', '', $name)));
        if ($u) { 
            echo '<div id="message" class="updated"><p>';
            echo "User with this login (" . strtolower(preg_replace('/ /', '', $name)) . ") already exists.<br/>";
            echo "<a href=\"" . get_admin_url() . "tools.php?page=add-contributor-menu\">Please try again.</a><br/>";
            echo '</p></div>';
            return;
        }
        
        $args = Array(
            'user_login' => strtolower(preg_replace('/ /', '', $name)),
            'display_name' => $fname . " " . $lname,
            'first_name' => $fname,
            'last_name' => $lname,
            'user_email' => $_POST['email'],
            'user_url' => $_POST['website'],
            'role' => 'contributor',
            'nickname' => $_POST['nname']
            );
            
        $user_id = wp_insert_user($args);
        update_user_meta($user_id, 'extended_user_info_phone_number', $_POST['phone']);
        
        echo '<div id="message" class="updated"><p>';
        if (current_user_can('administrator')) {
            echo "User (<a href=\"" . get_admin_url() . "user-edit.php?user_id=" . $user_id . "\">" . $username . "</a>) was added successfully.<br/>";
        } else {
            echo "User (" . $username . ") was added successfully.<br/>";
        }
        echo '</p></div>';
    } 
?>

    <div id="content" class="narrowcolumn">

	    <div class="wrap">
	        <h2>Add Contributor</h2>
	        <form method="post" action="" id="add_contributor">
                <table class="form-table">
                    <tr>
                        <th>
                        <label for="fname">First Name</label>
                        </th>
                        <td>
                        <input type="text" name="fname" id="name" class="regular-text required"/><br/>
                        <span class="description">Enter the user's first name</span>
                        </td>
                    </tr>
                    <tr>
                        <th>
                        <label for="lname">Last Name</label>
                        </th>
                        <td>
                        <input type="text" name="lname" id="name" class="regular-text required"/><br/>
                        <span class="description">Enter the user's last name</span>
                        </td>
                    </tr>
                    <tr>
                        <th>
                        <label for="email">E-Mail</label>
                        </th>
                        <td>
                        <input type="text" name="email" id="email" class="regular-text required email"/><br/>
                        <span class="description">Enter the user's complete e-mail address</span>
                        </td>
                    </tr>
                    <tr>
                        <th>
                        <label for="website">Website</label>
                        </th>
                        <td>
                        <input type="text" name="website" id="website" class="regular-text url required"/><br/>
                        <span class="description">Enter the URL of the user's website</span>
                        </td>
                    </tr>
                    <tr>
                        <th>
                        <label for="phone">Phone Number</label>
                        </th>
                        <td>
                        <input type="text" name="phone" id="phone required" class="regular-text"/><br/>
                        <span class="description">Enter the user's phone number</span>
                        </td>
                    </tr>
                    <tr>
                        <th>
                        <label for="nname">Nick Name</label>
                        </th>
                        <td>
                        <input type="text" name="nname" id="required" class="regular-text"/><br/>
                        <span class="description">Enter the user's nick name</span>
                        </td>
                    </tr>
                    <tr>
                        <th>
                        <input type="submit" value="Add Contributor"/>
                        </th>
                    </tr>
                </table>                    
	        </form>
	    </div>
	    
    </div>
    <script type="text/javascript">
        jQuery.noConflict();
        jQuery(document).ready(function(){
            jQuery.validator.addMethod("phoneUS", function(phone_number, element) {
                phone_number = phone_number.replace(/\s+/g, ""); 
            	return this.optional(element) || phone_number.length > 9 &&
            		phone_number.match(/^(1-?)?(\([2-9]\d{2}\)|[2-9]\d{2})-?[2-9]\d{2}-?\d{4}$/);
            }, "Please specify a valid phone number");
            
            jQuery("#add_contributor").validate({
                rules: {
                    phone: {
                        required: true,
                        phoneUS: true
                    }
                }
            });
        });
    </script>
    
<?php
}
?>
