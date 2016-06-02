<?php define('ACCESS', true);

    include_once 'function.php';

    if (IS_LOGIN) {
        $title = 'Tải tập tin';

        if ($dir == null || $name == null || !is_file(processDirectory($dir . '/' . $name))) {
            include_once 'header.php';

            echo '<div class="title">' . $title . '</div>';
            echo '<div class="list"><span>Đường dẫn không tồn tại</span></div>
            <div class="title">Chức năng</div>
            <ul class="list">
                <li><img src="icon/list.png"/> <a href="index.php">Danh sách</a></li>
            </ul>';

            include_once 'footer.php';
        } else {
            $dir = processDirectory($dir);
            $path = $dir . '/' . $name;

            header('Content-Type: application/octet-stream');
            header('Content-Disposition: inline; filename=' . $name);
            header('Content-Length: ' . filesize($path));
            readfile($path);
        }
    } else {
        goURL('login.php');
    }

?>