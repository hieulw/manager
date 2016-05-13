<?php define('ACCESS', true);

    include_once 'function.php';

    $path = isset($_GET['path']) && !empty($_GET['path']) ? rawurldecode($_GET['path']) : null;

    if (IS_LOGIN && is_file($path) && isPathNotPermission($path) == false && getimagesize($path) !== false)
        readfile($path);
    else
        die ('Not read image');

?>
