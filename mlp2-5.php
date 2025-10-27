<?php  
/**  
 * Plugin Name: MLP 2 to MLP 5 Migration Tool  
 * Description: Unified tool to export from MLP 2 and import to MLP 5  
 * Network: true  
 */  
  
class MLP_Migration_Tool {  
    private $wpdb;  
    private $is_mlp2_active = false;  
    private $is_mlp5_active = false;  
    private $content_relations;  
    private $site_relations;  
  
    public function __construct() {  
        global $wpdb;  
        $this->wpdb = $wpdb;  
          
        // Detect MLP version after plugins are loaded  
        add_action('plugins_loaded', [$this, 'detect_mlp_version'], 20);  
        add_action('network_admin_menu', [$this, 'add_admin_menu']);  
          
        // Handle export before any output  
        add_action('admin_init', [$this, 'maybe_handle_export']);  
          
        // Bypass MLP 5's legacy check when migration tool is active  
        add_filter('multilingualpress.is_check_legacy', '__return_false');  
          
        // Add admin notice when both MLP 5 and migration tool are active  
        add_action('network_admin_notices', [$this, 'show_migration_notice']);  
    }  
  
    public function detect_mlp_version() {  
        // Check for MLP 2 - the class is loaded in mlp_init() on plugins_loaded:0  
        if (class_exists('Multilingual_Press')) {  
            $this->is_mlp2_active = true;  
        }  
          
        // Check for MLP 5  
        if (function_exists('Inpsyde\MultilingualPress\resolve')) {  
            $this->is_mlp5_active = true;  
            add_action('admin_init', [$this, 'init_mlp5_services']);  
        }  
    }  
  
    public function init_mlp5_services() {  
        if (function_exists('Inpsyde\MultilingualPress\resolve')) {  
            try {  
                $this->content_relations = \Inpsyde\MultilingualPress\resolve(  
                    \Inpsyde\MultilingualPress\Framework\Api\ContentRelations::class  
                );  
                $this->site_relations = \Inpsyde\MultilingualPress\resolve(  
                    \Inpsyde\MultilingualPress\Framework\Api\SiteRelations::class  
                );  
            } catch (Exception $e) {  
                // Services not available yet  
            }  
        }  
    }  
  
    public function show_migration_notice() {  
        // Only show if MLP 5 is active  
        if (!$this->is_mlp5_active) {  
            return;  
        }  
          
        // Check if legacy option still exists  
        $has_legacy_option = get_site_option('inpsyde_multilingual');  
          
        if ($has_legacy_option) {  
            ?>  
            <div class="notice notice-warning">  
                <p>  
                    <strong>MLP 5 and Migration Tool are both active.</strong>   
                    Please complete the migration process by importing your MLP 2 data.   
                    The legacy data will be cleaned up automatically after import.  
                </p>  
            </div>  
            <?php  
        }  
    }  
  
    public function maybe_handle_export() {  
        if (!isset($_POST['export']) || !isset($_POST['_wpnonce'])) {  
            return;  
        }  
          
        if (!wp_verify_nonce($_POST['_wpnonce'], 'mlp_migration_export')) {  
            return;  
        }  
          
        if (!current_user_can('manage_network')) {  
            return;  
        }  
          
        $this->handle_export();  
    }  
  
    public function add_admin_menu() {  
        add_menu_page(  
            'MLP Migration',  
            'MLP Migration',  
            'manage_network',  
            'mlp-migration',  
            [$this, 'render_page'],  
            'dashicons-update'  
        );  
    }  
  
    public function render_page() {  
        ?>  
        <div class="wrap">  
            <h1>MLP 2 to MLP 5 Migration Tool</h1>  
              
            <?php if (!$this->is_mlp2_active && !$this->is_mlp5_active): ?>  
                <div class="notice notice-error">  
                    <p>Neither MLP 2 nor MLP 5 is detected. Please activate one of them.</p>  
                </div>  
            <?php elseif ($this->is_mlp2_active && $this->is_mlp5_active): ?>  
                <div class="notice notice-warning">  
                    <p>Both MLP 2 and MLP 5 are active. Please deactivate one before proceeding.</p>  
                </div>  
            <?php elseif ($this->is_mlp2_active): ?>  
                <?php $this->render_export_interface(); ?>  
            <?php elseif ($this->is_mlp5_active): ?>  
                <?php $this->render_import_interface(); ?>  
            <?php endif; ?>  
        </div>  
        <?php  
    }  
  
    private function render_export_interface() {  
        ?>  
        <div class="card">  
            <h2>Step 1: Export MLP 2 Data</h2>  
            <p>MLP 2 is currently active. Export your site and content relationships.</p>  
            <form method="post" action="">  
                <?php wp_nonce_field('mlp_migration_export'); ?>  
                <p>  
                    <button type="submit" name="export" class="button button-primary button-hero">  
                        Export Relationships  
                    </button>  
                </p>  
            </form>  
            <hr>  
            <h3>Next Steps:</h3>  
            <ol>  
                <li>Click "Export Relationships" to download your data</li>  
                <li>Deactivate MLP 2</li>  
                <li>Activate MLP 5</li>  
                <li>Return to this page to import your data</li>  
            </ol>  
        </div>  
        <?php  
    }  
  
    private function render_import_interface() {  
        if (isset($_POST['import']) && isset($_FILES['import_file'])) {  
            $this->handle_import();  
            return;  
        }  
        ?>  
        <div class="card">  
            <h2>Step 2: Import to MLP 5</h2>  
            <p>MLP 5 is currently active. Upload your exported data file.</p>  
            <form method="post" enctype="multipart/form-data">  
                <?php wp_nonce_field('mlp_migration_import'); ?>  
                <table class="form-table">  
                    <tr>  
                        <th scope="row">  
                            <label for="import_file">Export File</label>  
                        </th>  
                        <td>  
                            <input type="file" name="import_file" id="import_file"   
                                   accept=".json" required class="regular-text">  
                            <p class="description">  
                                Upload the JSON file you exported from MLP 2  
                            </p>  
                        </td>  
                    </tr>  
                </table>  
                <p>  
                    <button type="submit" name="import" class="button button-primary button-hero">  
                        Import Relationships  
                    </button>  
                </p>  
            </form>  
        </div>  
        <?php  
    }  
  
    private function handle_export() {  
        $data = [  
            'version' => '1.0',  
            'exported_at' => current_time('mysql'),  
            'site_relations' => $this->export_site_relations(),  
            'content_relations' => $this->export_content_relations()  
        ];  
      
        $json = json_encode($data, JSON_PRETTY_PRINT);  
        $filename = 'mlp-migration-' . date('Y-m-d-His') . '.json';  
          
        // Clean ALL output buffers  
        while (ob_get_level()) {  
            ob_end_clean();  
        }  
          
        header('Content-Type: application/json; charset=utf-8');  
        header('Content-Disposition: attachment; filename="' . $filename . '"');  
        header('Content-Length: ' . strlen($json));  
        header('Cache-Control: no-cache, must-revalidate');  
        header('Expires: 0');  
          
        echo $json;  
        exit;  
    } 
  
    private function export_site_relations() {  
        $table = $this->wpdb->base_prefix . 'mlp_site_relations';  
        $query = "SELECT DISTINCT site_1, site_2 FROM {$table}";  
        $results = $this->wpdb->get_results($query, ARRAY_A);  
        return $results ?: [];  
    }  
  
    private function export_content_relations() {  
        $table = $this->wpdb->base_prefix . 'multilingual_linked';  
        $query = "  
            SELECT   
                ml_source_blogid,  
                ml_source_elementid,  
                ml_blogid,  
                ml_elementid,  
                ml_type  
            FROM {$table}  
            ORDER BY ml_source_blogid, ml_source_elementid, ml_type  
        ";  
          
        $results = $this->wpdb->get_results($query, ARRAY_A);  
        return $this->group_relationships($results);  
    }  
  
    private function group_relationships($relations) {  
        $grouped = [];  
          
        foreach ($relations as $relation) {  
            $key = $relation['ml_source_blogid'] . '_' .   
                   $relation['ml_source_elementid'] . '_' .   
                   $relation['ml_type'];  
              
            if (!isset($grouped[$key])) {  
                $grouped[$key] = [  
                    'type' => $relation['ml_type'],  
                    'sites' => []  
                ];  
            }  
              
            $grouped[$key]['sites'][$relation['ml_source_blogid']] = (int)$relation['ml_source_elementid'];  
            $grouped[$key]['sites'][$relation['ml_blogid']] = (int)$relation['ml_elementid'];  
        }  
          
        return array_values($grouped);  
    }  
  
    private function handle_import() {  
        check_admin_referer('mlp_migration_import');  
  
        if (!$this->content_relations || !$this->site_relations) {  
            echo '<div class="notice notice-error"><p>MLP 5 services are not available.</p></div>';  
            return;  
        }  
  
        if ($_FILES['import_file']['error'] !== UPLOAD_ERR_OK) {  
            echo '<div class="notice notice-error"><p>File upload failed.</p></div>';  
            return;  
        }  
  
        $json = file_get_contents($_FILES['import_file']['tmp_name']);  
          
        // Remove BOM if present  
        $json = preg_replace('/^\xEF\xBB\xBF/', '', $json);  
          
        $data = json_decode($json, true);  
  
        // Add detailed debugging  
        if (!$data) {  
            echo '<div class="notice notice-error"><p>Failed to parse JSON. Error: ' . json_last_error_msg() . '</p></div>';  
            return;  
        }  
  
        if (!isset($data['site_relations'])) {  
            echo '<div class="notice notice-error"><p>Missing site_relations in import file.</p></div>';  
            return;  
        }  
  
        if (!isset($data['content_relations'])) {  
            echo '<div class="notice notice-error"><p>Missing content_relations in import file.</p></div>';  
            return;  
        }  
  
        $site_count = $this->import_site_relations($data['site_relations']);  
        $content_count = $this->import_content_relations($data['content_relations']);  
  
        // Delete the legacy option after successful import  
        delete_site_option('inpsyde_multilingual');  
  
        echo '<div class="notice notice-success"><p>';  
        echo sprintf(  
            'Import complete! Created %d site relationships and %d content relationships. Legacy data has been cleaned up.',  
            $site_count,  
            $content_count  
        );  
        echo '</p></div>';  
    }  
  
    private function import_site_relations($relations) {  
        $count = 0;  
          
        foreach ($relations as $relation) {  
            $site1 = (int)$relation['site_1'];  
            $site2 = (int)$relation['site_2'];  
              
            // Use insertRelations() with an array of site IDs  
            $inserted = $this->site_relations->insertRelations($site1, [$site2]);  
            if ($inserted > 0) {  
                $count++;  
            }  
        }  
          
        return $count;  
    } 
  
    private function import_content_relations($relations) {  
        $count = 0;  
          
        foreach ($relations as $relation) {  
            if (!isset($relation['sites'], $relation['type'])) {  
                continue;  
            }  
  
            $content_ids = [];  
            foreach ($relation['sites'] as $site_id => $content_id) {  
                $content_ids[(int)$site_id] = (int)$content_id;  
            }  
  
            $relationship_id = $this->content_relations->createRelationship(  
                $content_ids,  
                $relation['type']  
            );  
  
            if ($relationship_id > 0) {  
                $count++;  
            }  
        }  
          
        return $count;  
    }  
}  
  
new MLP_Migration_Tool();
