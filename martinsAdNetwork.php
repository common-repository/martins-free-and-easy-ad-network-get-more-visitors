<?php
/**
 * Plugin Name:       Martins Free And Easy Ad Network - Get more visitors
 * Plugin URI:        https://adnetwork.martinstools.com
 * Description:       Free ad network for WordPress, blogs and WooCommerce. Boost your Ecommerce business sales or affiliate marketing with free ads.
 * Version:           1.0.4
 * Requires at least: 5.0
 * Requires PHP:      5.6
 * Author:            Martins Tools
 * Author URI:        https://www.martinstools.com
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       martins-ad-network
 * Domain Path:       /languages
 */

if(!defined ('ABSPATH')) {die;} // Block direct access to the file


require_once(ABSPATH . "/wp-admin/includes/plugin.php");
require_once(ABSPATH . "/wp-admin/includes/file.php");


class maadne_martinsAdNetworkFront 
{
    public function __construct() 
    {
        add_action("wp_enqueue_scripts", [$this, "addScript"] );
        add_filter( 'script_loader_tag', [$this, "addAttributesToScript"], 10, 3);
    }
    
    
    function addAttributesToScript($tag, $handle, $src)
    {
        if ($handle == "maadne_martins-ad-network") {
            $tag = "<script defer src='" . esc_url($src) . "' data-client='wordpress' data-theme='" . get_option("maadne_martinsadnetwork_theme") . "' data-position='" . get_option("maadne_martinsadnetwork_position") . "' data-offset='" . get_option("maadne_martinsadnetwork_offset") . "'></script>";
        }

        return $tag;
   }

    
    function addScript() {
        // Set default settings if they do not exist
        if (!get_option("maadne_martinsadnetwork_theme")) {
            update_option("maadne_martinsadnetwork_theme", "light", false);
            update_option("maadne_martinsadnetwork_position", "bottom", false);
            update_option("maadne_martinsadnetwork_offset", 0, false);
        }
        
        wp_enqueue_script('maadne_martins-ad-network', 'https://adnetwork.martinstools.com/assets/js/ad-network.js', false, [
            'strategy'  => 'defer'
        ]);
    }

    
}



class maadne_martinsAdNetworkAdmin 
{
    private $url = "";
    private $key = "";
    private $version = "1.0.4";
    
    
    public function __construct() 
    {
        $this->url = wp_parse_url(get_site_url());
        
        add_action('admin_init', [$this, "redirectDashboard"]);
        add_action('admin_menu', [$this, "addMenuItems"]);
        add_action( 'admin_head', function() {
            remove_submenu_page( 'index.php', 'maadne_martins-ad-network-settings' );
        } );
        add_action( 'admin_post_add_martins_ad_network_settings', [$this, "saveSettings"]);
        add_filter('plugin_action_links_' . plugin_basename(__FILE__), [$this, 'addActionLinks']);
        register_activation_hook(__FILE__, [$this, 'pluginActivated']);
    }
    
    
    public function pluginActivated()
    {
        if (!get_option("maadne_martinsadnetwork_key")) {
            $this->getDashboardKey();
        }  
    }
    
    
    // Ad settings
    public function settingsPage() 
    {
        if (isset($_GET['page']) && $_GET['page'] == 'maadne_martins-ad-network-settings') {
            echo("<div style='width:100%;height:100%;padding:0;margin:0;text-align:center;'>");
                echo("<h1>Martins Ad Network</h1>");

                echo("<div style='max-width:500px;padding:20px 20px 50px 20px;margin:auto;border-radius:0.25rem;background-color:#fff;'>");
                    echo("<h3>Settings</h3>");

                    echo("<form action='/wp-admin/admin-post.php' method='post' style='text-align:center;'>");
                    echo("<input type='hidden' name='action' value='add_martins_ad_network_settings'>");
                    echo("<div style='text-align:left;'>Ad Theme</div>");
                    echo("<div><select name='theme' style='width:100%;max-width:500px;margin-bottom:10px;'>");
                        echo("<option value='light' " . (get_option("maadne_martinsadnetwork_theme") == "light" ? "selected" : "") . ">Light</option>");
                        echo("<option value='dark' " . (get_option("maadne_martinsadnetwork_theme") == "dark" ? "selected" : "") . ">Dark</option>");
                    echo("</select></div>");
                    echo("<div style='text-align:left;'>Ad position</div>");
                    echo("<div><select name='position' style='width:100%;max-width:500px;margin-bottom:10px;'>");
                        echo("<option value='bottom' " . (get_option("maadne_martinsadnetwork_position") == "bottom" ? "selected" : "") . ">Bottom</option>");
                        echo("<option value='top' " . (get_option("maadne_martinsadnetwork_position") == "top" ? "selected" : "") . ">Top</option>");
                    echo("</select></div>");
                    echo("<div style='text-align:left;'>Position offset in pixels (0-100)</div>");
                    echo("<div><input type='number' min='0' max='100' name='offset' value='" . (get_option("maadne_martinsadnetwork_offset") ? get_option("maadne_martinsadnetwork_offset") : 0) . "' style='width:100%;max-width:500px;'></div>");
                    echo("<div style='text-align:left;margin-bottom:10px;'><small><i>Moves the ad an amount of pixels from the top or bottom position</i></small></div>");
                    echo("<div style='text-align:center;margin-bottom:10px;'><input type='submit' value='Save' style='font-size:0.925rem;color:#fff;background-color:#1cbb8c;padding:0.4rem 1rem;border-radius:0.3rem;border:0;cursor:pointer;'></div>");
                    
                    if (isset($_GET["maadne_saved"])) {
                        echo("<b>Saved succesfully!</b>");
                    }
                    
                    echo("</form>");

                echo("</div>");
            echo("</div>");
            die();
        }
    }
    
    
    function saveSettings() {
        status_header(200);
        
        update_option("maadne_martinsadnetwork_theme", $_REQUEST["theme"], false);
        update_option("maadne_martinsadnetwork_position", $_REQUEST["position"], false);
        update_option("maadne_martinsadnetwork_offset", $_REQUEST["offset"], false);
        
        wp_redirect(get_admin_url() . "?page=maadne_martins-ad-network-settings&maadne_saved");
        exit;
    }
    

    public function dashboardPage() 
    {
        echo("Oops!!! Unable to connect to Dashboard.<br />Please try again later...");
    }

    
    public function redirectDashboard() 
    {
        if (isset($_GET['page']) && $_GET['page'] == 'maadne_martins-ad-network-dashboard') {
            // Get key for the dashboard
            $this->getDashboardKey();

            // Redirect to external dashboard
            if ($this->key != "failed") {
                wp_redirect("https://adnetwork.martinstools.com/admin/#/statswp/" . $this->url["host"] . "/" . $this->key);
                exit;
            }
            else {
                $this->dashboardPage();
            }
        }            
            
    }

    
    public function addMenuItems() 
    {
        add_dashboard_page('Martins Ad Network', 'Martins Ad Network', 'manage_options', 'maadne_martins-ad-network-dashboard', [$this, 'dashboardPage'], 2);
        add_dashboard_page('Martins Ad Network Settings', 'Martins Ad Network Settings', 'manage_options', 'maadne_martins-ad-network-settings', [$this, 'settingsPage'], 2);
    }  
    
    
    public function addActionLinks($links) 
    {
        // Add links in plugin list
        $mylinks = array(
            "<a href='" . admin_url('?page=maadne_martins-ad-network-dashboard') . "'>Dashboard</a>",
            "<a href='" . admin_url('?page=maadne_martins-ad-network-settings') . "'>Settings</a>",
            "<a href='https://adnetwork.martinstools.com#contact' target='_blank'>Support</a>"
        );
        
       return array_merge($mylinks, $links);
    }
    
    
    public function getDashboardKey()
    {
        // Try getting dashboard key from server
        $result = wp_remote_post("https://adnetwork.martinstools.com/api/domains/?getKey", ['timeout' => 30, 'method' => 'POST', 'body' => ["url" => get_site_url(), "email" => get_option("admin_email"), "version" => $this->version]]);
        
        if (!isset($result->errors)) {
            $this->data = json_decode($result["body"]);

            if ($this->data->status == "success") {
                $key = $this->data->key;
                update_option("maadne_martinsadnetwork_key", $key, false);
            }
            else {
                // New key not allowed. Using cached key
                $key = get_option("maadne_martinsadnetwork_key");        
            }
        } 
        // New key failed
        else {
            $key = "failed";
        }
        
        $this->key = $key;
    }

}


// Start the show
if (!is_admin()) {
    $maadne_martinsAdNetworkFront = new maadne_martinsAdNetworkFront(); 
}
else {
    $maadne_martinsAdNetworkAdmin = new maadne_martinsAdNetworkAdmin();
}
