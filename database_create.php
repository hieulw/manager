<?php

    define('ACCESS', true);
    define('PHPMYADMIN', true);

    include_once 'function.php';

    if (IS_LOGIN) {
        $title = 'Tạo database';

        include_once 'database_connect.php';
        include_once 'header.php';

        if (IS_CONNECT && IS_DATABASE_ROOT) {
            $name = null;
            $collection = null;
            $notice = null;

            if (isset($_POST['submit'])) {
                $name = addslashes($_POST['name']);
                $collection = addslashes($_POST['collection']);
                $notice = '<div class="notice_failure">';

                if (empty($name))
                    $notice .= 'Chưa nhập đầy đủ thông tin';
                else if (isDatabaseExists($name, null, true))
                    $notice .= 'Tên database đã tồn tại';
                else if ($collection == MYSQL_COLLECTION_NONE && !@mysql_query('CREATE DATABASE `' . $name . '`', LINK_IDENTIFIER))
                    $notice .= 'Tạo database thất bại, có thể tên database đã tồn tại';
                else if ($collection != MYSQL_COLLECTION_NONE && !preg_match('#^(.+?)' . MYSQL_COLLECTION_SPLIT . '(.+?)$#i', $collection, $matches))
                    $notice .= 'Mã hóa - Đối chiếu không hợp lệ';
                else if ($collection != MYSQL_COLLECTION_NONE && !@mysql_query('CREATE DATABASE `' . $name . '` CHARACTER SET ' . $matches[1] . ' COLLATE ' . $matches[2]))
                    $notice .= 'Tạo database thất bại: ' . mysql_error();
                else
                    goURL('database_lists.php');

                $notice .= '</div>';
            }

            echo '<div class="title">' . $title . '</div>';
            echo $notice;
            echo '<div class="list">
                <form action="database_create.php" method="post">
                    <span class="bull">&bull;</span>Tên database:<br/>
                    <input type="text" name="name" value="' . $name . '" size="18"/><br/>
                    <span class="bull">&bull;</span>Mã hóa - Đối chiếu:<br/>
                    <select name="collection">' . printCollection(stripslashes($collection)) . '</select><br/>
                    <input type="submit" name="submit" value="Tạo"/>
                </form>
            </div>
            <div class="title">Chức năng</div>
            <ul class="list">
                <li><img src="icon/database.png"/> <a href="database_lists.php">Danh sách database</a></li>
            </ul>';
        } else if (IS_CONNECT && !IS_DATABASE_ROOT) {
            echo '<div class="title">' . $title . '</div>
            <div class="list">Bạn đang kết nối tới một database không thể vào danh sách database</div>
            <div class="title">Chức năng</div>
            <ul class="list">
                <li><img src="icon/disconnect.png"/> <a href="database_disconnect.php">Ngắt kết nối database</a></li>
            </ul>';
        } else {
            echo '<div class="title">' . $title . '</div>
            <div class="list">Lỗi cấu hình hoặc không kết nối được</div>
            <div class="title">Chức năng</div>
            <ul class="list">
                <li><img src="icon/disconnect.png"/> <a href="database_disconnect.php">Ngắt kết nối database</a></li>
            </ul>';
        }

        include_once 'footer.php';
    } else {
        goURL('login.php');
    }

    include_once 'database_close.php';

?>