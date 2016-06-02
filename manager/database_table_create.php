<?php

    define('ACCESS', true);
    define('PHPMYADMIN', true);

    include_once 'function.php';

    if (IS_LOGIN) {
        $title = 'Tạo bảng';

        include_once 'database_connect.php';

        if (IS_CONNECT) {
            $title .= ': ' . DATABASE_NAME;

            include_once 'header.php';

            $table = null;
            $column = null;
            $default = null;
            $length = null;
            $type = null;
            $collection = null;
            $attributes = null;
            $engine_storage = null;
            $field_key = null;
            $is_null = false;
            $auto_increment = false;
            $notice = null;

            if (isset($_POST['submit'])) {
                $table = addslashes($_POST['table']);
                $column = addslashes($_POST['column']);
                $default = addslashes($_POST['default']);
                $length = addslashes($_POST['length']);
                $type = addslashes($_POST['type']);
                $collection = addslashes($_POST['collection']);
                $attributes = addslashes($_POST['attributes']);
                $engine_storage = addslashes($_POST['engine_storage']);
                $field_key = addslashes($_POST['field_key']);
                $is_null = isset($_POST['is_null']);
                $auto_increment = isset($_POST['auto_increment']);
                $notice = '<div class="notice_failure">';

                if ($collection != MYSQL_COLLECTION_NONE && !preg_match('#^(.+?)' . MYSQL_COLLECTION_SPLIT . '(.+?)$#i', $collection, $matches)) {
                    $notice .= 'Mã hóa - Đối chiếu không hợp lệ';
                } else if (empty($table)) {
                    $notice .= 'Chưa nhập tên bảng';
                } else if (empty($column)) {
                    $notice .= 'Chưa nhập tên cột';
                } else if (!empty($length) && !preg_match('#\\b[0-9]+\\b#', $length)) {
                    $notice .= 'Độ dài không hợp lệ';
                } else {
                    $type_put = $type . (empty($length) == false ? "($length)" : null);
                    $collection_put = $collection == MYSQL_COLLECTION_NONE ? null : 'CHARACTER SET ' . $matches[1] . ' COLLATE ' . $matches[2];
                    $attributes_put = $attributes == MYSQL_ATTRIBUTES_NONE ? null : $attributes;
                    $null_put = $is_null ? 'NULL' : 'NOT NULL';
                    $default_put = $default == null ? null : "DEFAULT '$default'";
                    $auto_increment_put = $auto_increment ? 'AUTO_INCREMENT' : null;
                    $field_key_put = $field_key == MYSQL_FIELD_KEY_NONE ? null : ", $field_key(`$column`)";

                    $sql = "CREATE TABLE `$table` ";
                    $sql .= "(`$column` ";
                    $sql .= $type_put;

                    if ($attributes_put != null)
                        $sql .= ' ' . $attributes_put;

                    $sql .= ' ' . $null_put;

                    if ($default_put != null)
                        $sql .= ' ' . $default_put;

                    if ($auto_increment_put != null)
                        $sql .= ' ' . $auto_increment_put;

                    if ($field_key_put != null)
                        $sql .= $field_key_put;

                    $sql .= ') ENGINE=' . $engine_storage;

                    if ($collection_put != null)
                        $sql .= ' ' . $collection_put;

                    if ($auto_increment_put != null)
                        $sql .= ' ' . $auto_increment_put . '=1';

                    if (!@mysql_query($sql, LINK_IDENTIFIER))
                        $notice .= 'Lỗi tạo bảng: ' . mysql_error();
                    else
                        goURL('database_tables.php' . DATABASE_NAME_PARAMATER_0);
                }

                $collection = $collection != MYSQL_COLLECTION_NONE && isset($matches) ? $matches[2] : MYSQL_COLLECTION_NONE;
                $notice .= '</div>';
            }

            echo '<div class="title"><div class="ellipsis">' . $title . '</div></div>';
            echo $notice;
            echo '<div class="list">
                <form action="database_table_create.php' . DATABASE_NAME_PARAMATER_0 . '" method="post">
                    <span class="bull">&bull;</span>Tên bảng:<br/>
                    <input type="text" name="table" value="' . stripslashes($table) . '" size="18"/><hr/>
                    <span class="bull">&bull;</span>Tên cột:<br/>
                    <input type="text" name="column" value="' . stripslashes($column) . '" size="18"/><br/>
                    <span class="bull">&bull;</span>Giá trị mặc định:<br/>
                    <input type="text" name="default" value="' . stripslashes($default) . '" size="18"/><br/>
                    <span class="bull">&bull;</span>Đội dài:<br/>
                    <input type="text" name="length" value="' . stripslashes($length) . '" size="18"/><br/>
                    <span class="bull">&bull;</span>Loại dữ liệu:<br/>
                    <select name="type">' . printDataType(stripslashes($type)) . '</select><br/>
                    <span class="bull">&bull;</span>Mã hóa - Đối chiếu:<br/>
                    <select name="collection">' . printCollection(stripslashes($collection)) . '</select><br/>
                    <span class="bull">&bull;</span>Thuộc tính:<br/>
                    <select name="attributes">' . printAttributes(stripslashes($attributes)) . '</select><br/>
                    <span class="bull">&bull;</span>Lưu trữ:<br/>
                    <select name="engine_storage">' . printEngineStorage(stripslashes($engine_storage)) . '</select><br/>
                    <span class="bull">&bull;</span>Khóa:
                    <br/>' . printFieldKey('field_key', stripslashes($field_key)) . '<br/>
                    <span class="bull">&bull;</span>Thêm:<br/>
                    <input type="checkbox" name="is_null" value="1"' . ($is_null ? ' checked="checked"' : null) . '/>Null<br/>
                    <input type="checkbox" name="auto_increment" value="1"' . ($auto_increment ? ' checked="checked"' : null) . '/>Tự tăng giá trị<hr/>
                    <input type="submit" name="submit" value="Tạo"/>
                </form>
            </div>
            <div class="title">Chức năng</div>
            <ul class="list">
                <li><img src="icon/database_table.png"/> <a href="database_tables.php' . DATABASE_NAME_PARAMATER_0 . '">Danh sách bảng</a></li>';

                if (IS_DATABASE_ROOT)
                    echo '<li><img src="icon/database.png"/> <a href="database_lists.php">Danh sách database</a></li>';

            echo '</ul>';
        } else if (ERROR_CONNECT == false && ERROR_SELECT_DB && IS_DATABASE_ROOT) {
            include_once 'header.php';

            echo '<div class="title">' . $title . '</div>
            <div class="list">Không thể chọn database</div>
            <div class="title">Chức năng</div>
            <ul class="list">
                <li><img src="icon/database.png"/> <a href="database_lists.php">Danh sách database</a></li>
            </ul>';
        } else {
            include_once 'header.php';

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