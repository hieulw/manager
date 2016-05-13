<?php

    define('ACCESS', true);
    define('PHPMYADMIN', true);

    include_once 'function.php';

    if (IS_LOGIN) {
        $title = 'Ngắt kết nối database';

        include_once 'database_connect.php';
        include_once 'header.php';

        if (IS_CONNECT) {
            if ($databases['is_auto']) {
                if (!createDatabaseConfig($databases['db_host'], $databases['db_username'], $databases['db_password'], $databases['db_name'], false)) {
                    echo '<div class="title">' . $title . '</div>
                    <div class="list">Ngắt kết nối thất bại</div>
                    <div class="title">Chức năng</div>
                    <ul class="list">';

                    if (IS_DATABASE_ROOT)
                        echo '<li><img src="icon/database.png"/> <a href="database_lists.php">Danh sách database</a></li>';
                    else
                        echo '<li><img src="icon/database.png"/> <a href="database_tables.php">Danh sách bảng</a></li>';

                    echo '</ul>';
                } else {
                    goURL('database.php');
                }
            } else {
                goURL('database.php');
            }
        } else {
            echo '<div class="title">' . $title . '</div>
            <div class="list">Lỗi cấu hình hoặc không kết nối được</div>
            <div class="title">Chức năng</div>
            <ul class="list">
                <li><img src="icon/database.png"/> <a href="database.php">Kết nối database</a></li>
            </ul>';
        }

        include_once 'footer.php';
    } else {
        goURL('login.php');
    }

    include_once 'database_close.php';

?>