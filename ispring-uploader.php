<?php
/**
 * Plugin Name: iSpring ZIP Uploader for WordPress
 * Description: Creates backend pages for uploading iSpring Projects into WordPress Uploads
 * Version: 1.0
 * Author: Greenstein Designagentur
 */

// Admin-Menü hinzufügen
add_action('admin_menu', 'my_unzip_plugin_menu');

function my_unzip_plugin_menu() {
    add_menu_page('Unzip Plugin', 'Unzip Plugin', 'manage_options', 'my_unzip_plugin', 'my_unzip_plugin_page');
}

// Admin-Seite rendern
function my_unzip_plugin_page() {
    ?>
    <div class="wrap">
        <h2>ZIP-Datei hochladen und entpacken</h2>
        <form method="post" enctype="multipart/form-data">
            <label for="directory_name">Verzeichnisname:</label>
            <input type="text" name="directory_name" required>
            <label for="zip_file">ZIP-Datei:</label>
            <input type="file" name="zip_file" accept=".zip" required>
            <input type="submit" name="submit" value="Hochladen und Entpacken">
        </form>
        <?php
        if (isset($_POST['submit'])) {
            my_unzip_plugin_handle_upload();
        }
        ?>
    </div>
    <?php
}

// ZIP-Datei hochladen und entpacken
function my_unzip_plugin_handle_upload() {
    $directory_name = sanitize_text_field($_POST['directory_name']);
    $zip_file = $_FILES['zip_file'];

    // Pfad definieren
    $upload_dir = wp_upload_dir();
    $path = $upload_dir['basedir'] . '/ispring/' . $directory_name;

    // ZIP-Datei entpacken
    $zip = new ZipArchive;
    if ($zip->open($zip_file['tmp_name']) === TRUE) {
        $zip->extractTo($path);

        // Den Inhalt des einzigen Ordners in der Root-Ebene kopieren
        $root_folder = glob($path . '/*', GLOB_ONLYDIR)[0];
        my_unzip_plugin_copy_folder($root_folder, $path);

        // Link zum Verzeichnis ausgeben
        $url = $upload_dir['baseurl'] . '/ispring/' . $directory_name;
        echo '<p>Verzeichnis erfolgreich entpackt: <a href="' . $url . '">' . $url . '</a></p>';

        $zip->close();
    } else {
        echo '<p>Fehler beim Entpacken der ZIP-Datei.</p>';
    }
}

// Ordner kopieren
function my_unzip_plugin_copy_folder($src, $dst) {
    $dir = opendir($src);
    @mkdir($dst);
    while (false !== ($file = readdir($dir))) {
        if (($file != '.') && ($file != '..')) {
            if (is_dir($src . '/' . $file)) {
                my_unzip_plugin_copy_folder($src . '/' . $file, $dst . '/' . $file);
            } else {
                copy($src . '/' . $file, $dst . '/' . $file);
            }
        }
    }
    closedir($dir);
}
?>
