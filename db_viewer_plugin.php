<?php
/*
Plugin Name: Database Viewer and Admin
Description: Plugin to display all database tables and their content, and allow administrators to run SQL queries on the database.
Version: 1.0
Author: Artem Stepanov
*/

// Hook to add a menu item in the WordPress admin panel
add_action('admin_menu', 'db_viewer_menu');

function db_viewer_menu() {
    add_menu_page('Database Viewer and Admin', 'Database Viewer', 'manage_options', 'db-viewer', 'display_all_database_tables');
}

// Function to display all database tables and their content
function display_all_database_tables() {
    global $wpdb; // WordPress database object

    echo '<div class="wrap">';
    echo '<h2>Database Viewer and Admin</h2>';

    // Check if the current user has permission to manage options (typically administrators)
    if (current_user_can('manage_options')) {
        // Display SQL query form
        echo '<h3>Run SQL Query</h3>';
        echo '<form method="post">';
        echo '<textarea name="sql_query" rows="5" cols="60" placeholder="Enter your SQL query here"></textarea><br>';
        echo '<input type="submit" class="button button-primary" value="Execute Query">';
        echo '</form>';
        
        // Execute SQL query if submitted
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["sql_query"])) {
            $sql_query = $_POST["sql_query"];
            $result = $wpdb->query($sql_query);
            
            if ($result !== false) {
                echo '<div class="updated"><p>SQL query executed successfully.</p></div>';
            } else {
                echo '<div class="error"><p>Error executing SQL query.</p></div>';
            }
        }

        echo '<hr>';
        
        // Display database tables and their content
        $tables = $wpdb->get_results("SHOW TABLES", ARRAY_N);

        if (!empty($tables)) {
            foreach ($tables as $table) {
                $table_name = $table[0];
                $data = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);

                echo '<h3>Table: ' . esc_html($table_name) . '</h3>';

                if (count($data) > 0) {
                    echo '<table class="widefat">';
                    echo '<thead><tr>';

                    foreach ($data[0] as $key => $value) {
                        echo '<th>' . esc_html($key) . '</th>';
                    }

                    echo '</tr></thead>';
                    echo '<tbody>';

                    foreach ($data as $row) {
                        echo '<tr>';
                        foreach ($row as $cell) {
                            echo '<td>' . esc_html($cell) . '</td>';
                        }
                        echo '</tr>';
                    }

                    echo '</tbody>';
                    echo '</table>';
                } else {
                    echo '<p>No data found in the table.</p>';
                }
            }
        } else {
            echo '<p>No tables found in the database.</p>';
        }
    } else {
        echo 'You do not have permission to access this page.';
    }

    echo '</div>';
}
