<?php
/*
Plugin Name: Tiny Bootstrap Elements Light
Plugin URI: http://codecanyon.net/item/tinymce-bootstrap-plugin/10086522
Description: Tiny Bootstrap Elements Light adds Bootstrap toolbar in tinyMce, giving access to visual editors to insert Bootstrap elements into your content. Complete plugin with all elements available at http://codecanyon.net/item/tinymce-bootstrap-plugin/10086522
Version: 1.1
Author: Gilles Migliori
Author URI: http://codecanyon.net/user/migli
*/

// plugin version
add_option('tbel_version', '1.0.0');

add_action('admin_init', 'tbel_admin_init');
add_action('admin_menu', 'tbel_admin_menu');
add_action('admin_enqueue_scripts', 'tbel_admin_scripts');

function tbel_admin_init()
{
    global $tbel_elements;
    // register tbel elements array
    for ($i=0; $i < count($tbel_elements); $i++) {
        register_setting('tbel_options', 'tbel_element_choice_' . $tbel_elements[$i]);
    }
    register_setting('tbel_options', 'tinymce_custom_background');
    register_setting('tbel_options', 'tinymce_background_color');
    register_setting('tbel_options', 'use_frontend_css');
    register_setting('tbel_options', 'bootstrap_css_path');
    register_setting('tbel_options', 'custom_bootstrap_css_path');
}

// create custom plugin settings menu
function tbel_admin_menu()
{
    //create new top-level menu
    $page = add_menu_page('Tiny Bootstrap Elements Light Plugin Settings', 'Tiny Bootstrap Elements Light Settings', 'administrator', __FILE__, 'tbel_settings_page', 'dashicons-editor-bold');
}

// add bootstrap icon font to admin panel

function tbel_admin_scripts($hook)
{

    $screen = get_current_screen();
    // echo '$screen->base = ' . $screen->base;
    if (preg_match('`tiny-bootstrap-elements-light`', $hook)) {
        // tbel admin page
        // css
        wp_register_style('tbelAdminStylesheet', plugins_url('assets/css/admin-page.css', __FILE__));
        wp_register_style('tbelColorpickerStylesheet', plugins_url('assets/css/colorpicker.min.css', __FILE__));
        wp_enqueue_style('tbelAdminStylesheet');
        wp_enqueue_style('tbelColorpickerStylesheet');
        // js
        wp_enqueue_script('admin_page_colorpicker', plugin_dir_url(__FILE__) . 'assets/js/colorpicker.min.js', 'jquery');
        wp_enqueue_script('admin_page_script', plugin_dir_url(__FILE__) . 'assets/js/wp-admin.js', 'jquery');
    } elseif ($screen->base == 'post') {
        // plugin stylesheets on pages with editor
        wp_register_style('tbelEditorStylesheet', plugins_url('assets/css/editor.css', __FILE__));
        wp_register_style('tbelColorpickerStylesheet', plugins_url('assets/css/colorpicker.min.css', __FILE__));
        wp_enqueue_style('tbelEditorStylesheet');
        wp_enqueue_style('tbelColorpickerStylesheet');
    } else {
        return;
    }
}

$tbel_elements = array('btn', 'label', 'badge', 'alert', 'snippet');
$frontend_css_files = array();

// get the selected Bootstrap css in option list
function tbel_get_selected($url)
{
    $bootstrap_css_path = get_option('bootstrap_css_path');
    if ($url == $bootstrap_css_path) {
        return ' selected';
    } else {
        return false;
    }
}

// get custom Bootstrap css path from plugin options | theme default css
function tbel_get_custom_path()
{
    $custom_path = get_option('custom_bootstrap_css_path');
    if (!empty($custom_path)) {
        return $custom_path;
    } else {
        return get_stylesheet_uri();
    }
}

function tbel_settings_page()
{
    global $tbel_elements;
    ?>
    <div class="wrap">
    <h2>Tiny Bootstrap Elements Light Settings</h2>
    <h3>Check the elements to display in TinyMce Bootstrap Elements Light toolbar</h3>
    <form method="post" action="options.php">
        <?php settings_fields('tbel_options'); ?>
        <?php do_settings_sections('tbel_options'); ?>
        <table class="form-table mce-bs-icon-btn">
            <tr valign="top">
                <th scope="row">check all</th>
                <td><input type="checkbox" name="checkall" value="checked" /></td>
                <td>&nbsp;</td>
            </tr>
    <?php
        echo '<tr valign="top">' . " \n";
        for ($i=0; $i < count($tbel_elements); $i++) {
            $option_name = 'tbel_element_choice_' . $tbel_elements[$i];
            $icon = '<i class="mce-i-icon-' . $tbel_elements[$i] . '"></i>';
            $checked = esc_attr(get_option($option_name));
            echo '    <th scope="row">' . $icon . $tbel_elements[$i] . '</th>' . " \n";
            echo '    <td><input type="checkbox" name="' . $option_name . '" value="checked"' . $checked . ' /></td>' . " \n";
            if ((round($i / 2) - ($i / 2)) != 0) {
                echo '</tr>' . " \n";
                echo '<tr valign="top">' . " \n";
            } elseif ($i == 10) {
                echo '<td>&nbsp;</td>' . " \n";
                echo '</tr>' . " \n";
            }
        }
    ?>
        </table>
        <h3>Choose witch Bootstrap css to use in Wordpress Admin Editor + Frontend Template</h3>
        <?php
            $checked = esc_attr(get_option('use_frontend_css'));
        ?>
        <p class="help" style="position:relative;padding-left: 40px;"><span class="dashicons dashicons-info" style="position:absolute;top:0;left: -0;"></span>Check "use Wordpress Theme css in Wordpress Admin Editor" if your active theme includes Bootstrap css.<br>Otherwise, choose any Bootstrap css in the list ; it will be included in admin editor and in frontend pages.<br>The Default Bootstrap CSS, Darkly, Flatly &amp; Slate themes are included in this plugin.</p>
        <table class="form-table">
            <tr valign="top">
                <th scope="row">Use Wordpress Theme css <br>in Admin Editor : </th>
                <td><input type="checkbox" name="use_frontend_css" value="checked"<?php echo $checked; ?> /></td>
            </tr>
            <tr valign="top">
                <th scope="row">Bootstrap CSS to add to Wordpress Theme <br>&amp; Admin Editor : </th>
                <td>
                    <select name="bootstrap_css_path">
    <?php
        $options = array(
            array(
                'url' => '',
                'text' => 'None - Bootstrap is already included in my Theme css'
           ),
            array(
                'url' => plugins_url('assets/css/bootstrap.min.css', __FILE__),
                'text' => 'default Bootstrap CSS'
           ),
            array(
                'url' => plugins_url('assets/themes/darkly/bootstrap.min.css', __FILE__),
                'text' => 'Bootstrap Darkly Theme'
           ),
            array(
                'url' => plugins_url('assets/themes/flatly/bootstrap.min.css', __FILE__),
                'text' => 'Bootstrap Flatly Theme'
           ),
            array(
                'url' => plugins_url('assets/themes/slate/bootstrap.min.css', __FILE__),
                'text' => 'Bootstrap Slate Theme'
           ),
            array(
                'url' => 'custom',
                'text' => 'Custom'
           )
       );
        for ($i=0; $i < count($options); $i++) {
            $url      = $options[$i]['url'];
            $text     = $options[$i]['text'];
            $selected = tbel_get_selected($url);
            echo '<option value="' . $url . '"' . $selected . '>' . $text . '</option>' . " \n";
        }
    ?>
                    </select>
                    <div id="custom-editor-input-wrapper">

                        <p><label><strong>Custom Bootstrap CSS URL for Wordpress : </strong></label><br><input type="text" size="80" name="custom_bootstrap_css_path" value="<?php echo tbel_get_custom_path(); ?>"></p>
                        <p><span class="dashicons dashicons-info"></span> Your theme CSS path is <code><?php echo get_bloginfo('template_url') . '/css/default.css'; ?></code></p>
                    </div>
                </td>
            </tr>
            <?php
            $checked = esc_attr(get_option('tinymce_custom_background'));
        ?>
            <tr valign="top">
                <th scope="row">Custom Background color <br>in Admin Editor : </th>
                <td>
                    <input type="checkbox" name="tinymce_custom_background" value="checked"<?php echo $checked; ?> />
                    <div id="custom-editor-background-wrapper">
                        <p><label><strong>Pick a color : </strong></label><input type="text" size="80" name="tinymce_background_color" id="colorpicker" value="<?php echo get_option('tinymce_background_color'); ?>"></p>
                    </div>
                </td>
            </tr>
        </table>
        <?php submit_button(); ?>

    </form>
    </div>
<?php
}

/* =============================================
    End admin settings
============================================= */

//Include Bootstrap CSS in Frontend Template
$bootstrap_css_path = get_option('bootstrap_css_path');
if ($bootstrap_css_path !== get_bloginfo('template_url') . '/css/default.css') {
    add_action('wp_head', 'tbel_include_bootstrap_css_in_frontend');
}

function tbel_include_bootstrap_css_in_frontend()
{
    $bootstrap_css_path = get_option('bootstrap_css_path');
    if ($bootstrap_css_path == 'custom') {
        $bootstrap_css_path = get_option('custom_bootstrap_css_path');
    }
    wp_register_style('tbelBootstrapFrontend', $bootstrap_css_path);
    wp_enqueue_style('tbelBootstrapFrontend');
}

//Register Buttons in TinyMce
function tbel_register_tiny_bootstrap_elements_button($buttons)
{
    array_push($buttons, "|", "tiny_bootstrap_elements_light");

    return $buttons;
}

function add_tiny_bootstrap_elements_light_plugin($plugin_array)
{
    $plugin_array['tiny_bootstrap_elements_light'] = plugins_url('assets/plugin.min.js', __FILE__);

    return $plugin_array;
}

function add_tiny_bootstrap_elements_light_buttons()
{
    if (!current_user_can('edit_posts') && ! current_user_can('edit_pages')) {
        return;
    } elseif (get_user_option('rich_editing') == 'true') {
        add_filter('mce_external_plugins', 'add_tiny_bootstrap_elements_light_plugin');
        add_filter('mce_buttons', 'tbel_register_tiny_bootstrap_elements_button');
    }

}
add_action('init', 'add_tiny_bootstrap_elements_light_buttons');

// activate checked elements in tinyMce, include Bootstrap CSS & Frontend CSS in TinyMCE
add_filter('tiny_mce_before_init', 'set_tiny_bootstrap_light_config');
// set_tiny_bootstrap_light_config();
function set_tiny_bootstrap_light_config($tinyMceConfig)
{
    //Include Bootstrap CSS in TinyMce
    $bootstrap_css_path        = get_option('bootstrap_css_path');
    $use_frontend_css          = get_option('use_frontend_css');
    $tinymce_custom_background = get_option('tinymce_custom_background');

    if ($bootstrap_css_path == 'custom') {
        $bootstrap_css_path = get_option('custom_bootstrap_css_path');
    }
    //Include Frontend CSS in TinyMce
    if ($use_frontend_css == 'checked') {
        $theme_dir = get_bloginfo('template_url');
        $frontend_css_files = implode(', ', tbel_get_frontend_css_files($theme_dir));
    } else {
        $frontend_css_files = "";
    }
    // set background color for tinyMce
    if ($tinymce_custom_background == 'checked') {
        $tinymce_background_color = get_option('tinymce_background_color');
    } else {
        $tinymce_background_color = '';
    }
    $tinyMceConfig['bootstrapLightConfig'] = "{
        'bootstrapElementsLight': {
            'btn': " . tbel_get_element_value('btn') . ",
            'label': " . tbel_get_element_value('label') . ",
            'badge': " . tbel_get_element_value('badge') . ",
            'alert': " . tbel_get_element_value('alert') . ",
            'snippet': " . tbel_get_element_value('snippet') . "
        },
        'bootstrapLightCssPath': '" . $bootstrap_css_path . "',
        'frontEndLightCssFiles': '" . $frontend_css_files . "',
        'tinymceLightBackgroundColor': '" . $tinymce_background_color . "'
    }";

    return $tinyMceConfig;
}

function tbel_get_element_value($element)
{
    $option_name = 'tbel_element_choice_' . $element;
    $checked = esc_attr(get_option($option_name));
    if ($checked == 'checked') {
        return 'true';
    } else {
        return 'false';
    }
}

function tbel_get_frontend_css_files($theme_dir)
{
    global $frontend_css_files;
    $path = parse_url($theme_dir, PHP_URL_PATH);
    $directory = new RecursiveDirectoryIterator(
        $_SERVER['DOCUMENT_ROOT'] . $path,
        RecursiveDirectoryIterator::KEY_AS_FILENAME |
        RecursiveDirectoryIterator::CURRENT_AS_FILEINFO
    );
    $files = new RegexIterator(
        new RecursiveIteratorIterator($directory),
        '#.css$#',
        RegexIterator::MATCH,
        RegexIterator::USE_KEY
    );
    foreach ($files as $file) { // http://wordpress/wp-content/themes/bitter-sweet/\css\bootstrap.min.css
        $find = array($_SERVER['DOCUMENT_ROOT'] . $path, '\\');
        $replace = array($theme_dir, '/');
        $frontend_css_files[] = str_replace($find, $replace, $file->getPathname());
    }
    // var_dump($frontend_css_files);
    return $frontend_css_files;
}
