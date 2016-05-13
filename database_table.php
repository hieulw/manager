<?php

    define('ACCESS', true);
    define('PHPMYADMIN', true);

    include_once 'function.php';

    if (IS_LOGIN) {
        $title = 'Bảng';
        $name = isset($_GET['name']) ? addslashes($_GET['name']) : null;

        $page = array('current' => 0, 'total' => 1, 'paramater_0' => null, 'paramater_1' => null);
        $page['current'] = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $page['current'] = $page['current'] <= 0 ? 1 : $page['current'];

        $order = array('desc' => false, 'name' => null, 'paramater_0' => null, 'paramater_1' => null);
        $order['desc'] = isset($_GET['order']) && intval($_GET['order']) == 1;
        $order['name'] = $order['desc'] ? 'DESC' : 'ASC';
        $order['paramater_0'] = $order['desc'] ? '?order=1' : null;
        $order['paramater_1'] = $order['desc'] ? '&order=1' : null;

        $isTableExists = false;

        include_once 'database_connect.php';

        if (IS_CONNECT && $name != null && ($isTableExists = isTableExists($name))) {
            if (isset($_GET['action']) && trim($_GET['action']) == 'rename') {
                $title = 'Đổi tên bảng: ' . DATABASE_NAME . ' > ' . $name;
                $table = $name;

                include_once 'header.php';

                echo '<div class="title"><div class="ellipsis">' . $title . '</div></div>';

                if (isset($_POST['submit'])) {
                    $table = addslashes($_POST['table']);

                    if (empty($table))
                        echo '<div class="notice_failure">Chưa nhập tên bảng</div>';
                    else if (isTableExists($table, $name, true))
                        echo '<div class="notice_failure">Tên bảng đã tồn tại</div>';
                    else if (!@mysql_query("RENAME TABLE `$name` TO `$table`", LINK_IDENTIFIER))
                        echo '<div class="notice_failure">Đổi tên thất bại</div>';
                    else
                        goURL('database_tables.php' . DATABASE_NAME_PARAMATER_0);
                }

                echo '<div class="list">
                    <form action="database_table.php?action=rename&name=' . $name . DATABASE_NAME_PARAMATER_1 . '" method="post">
                        <span class="bull">&bull;</span>Tên bảng:<br/>
                        <input type="text" name="table" value="' . stripslashes($table) . '" size="18"/><br/>
                        <input type="submit" name="submit" value="Đổi tên"/>
                    </form>
                </div>';
            } else if (isset($_GET['action']) && trim($_GET['action']) == 'delete') {
                $title = 'Xóa bảng: ' . DATABASE_NAME . ' > ' . $name;

                include_once 'header.php';

                echo '<div class="title"><div class="ellipsis">' . $title . '</div></div>';

                if (isset($_POST['accept'])) {
                    if (!@mysql_query("DROP TABLE `$name`", LINK_IDENTIFIER))
                        echo '<div class="notice_failure">Xóa bảng thất bại</div>';
                    else
                        goURL('database_tables.php' . DATABASE_NAME_PARAMATER_0);
                } else if (isset($_POST['not'])) {
                    goURL('database_tables.php' . DATABASE_NAME_PARAMATER_0);
                }

                echo '<div class="list">
                    <form action="database_table.php?action=delete&name=' . $name . DATABASE_NAME_PARAMATER_1 . '" method="post">
                        <span>Bạn có thực sự muốn xóa bảng không dữ liệu của bảng sẽ bị xóa cùng?</span><hr/>
                        <center>
                            <input type="submit" name="accept" value="Xóa"/>
                            <input type="submit" name="not" value="Huỷ"/>
                        </center>
                    </form>
                </div>';
            } else if (isset($_GET['action']) && trim($_GET['action']) == 'list_struct') {
                $title = 'Danh sách cột: ' . DATABASE_NAME . ' > ' . $name;

                include_once 'header.php';

                $query = @mysql_query('SHOW COLUMNS FROM `' . $name . '`', LINK_IDENTIFIER);

                if (is_resource($query)) {
                    echo '<div class="title"><div class="ellipsis">' . $title . '</div></div>
                    <ul class="list_database">';

                    while ($assoc = mysql_fetch_assoc($query)) {
                        echo '<li>
                            <p>
                                <img src="icon/columns.png"/>
                                <a href="database_table.php?action=edit_columns&name=' . $name . DATABASE_NAME_PARAMATER_1 . '&columns='. $assoc['Field'] . '">
                                    <strong>' . $assoc['Field'] . '</strong>
                                </a>
                            </p>
                            <p>
                                <span>' . $assoc['Type'] . '</span>
                            </p>
                        </li>';
                    }

                    echo '</ul>';
                }
            } else if (isset($_GET['action']) && trim($_GET['action']) == 'add_columns') {
                $title = 'Tạo cột: ' . DATABASE_NAME . ' > ' . $name;

                include_once 'header.php';

                $column = null;
                $default = null;
                $length = null;
                $type = null;
                $collection = null;
                $attributes = null;
                $position = null;
                $field_key = null;
                $is_null = false;
                $auto_increment = false;
                $notice = null;

                if (isset($_POST['continue']) || isset($_POST['create'])) {
                    $column = addslashes($_POST['column']);
                    $default = addslashes($_POST['default']);
                    $length = addslashes($_POST['length']);
                    $type = addslashes($_POST['type']);
                    $collection = addslashes($_POST['collection']);
                    $attributes = addslashes($_POST['attributes']);
                    $position = addslashes($_POST['position']);
                    $field_key = addslashes($_POST['field_key']);
                    $is_null = isset($_POST['is_null']);
                    $auto_increment = isset($_POST['auto_increment']);
                    $notice = '<div class="notice_failure">';

                    if ($collection != MYSQL_COLLECTION_NONE && !preg_match('#^(.+?)' . MYSQL_COLLECTION_SPLIT . '(.+?)$#i', $collection, $matches)) {
                        $notice .= 'Mã hóa - Đối chiếu không hợp lệ';
                    } else if ($position != MYSQL_AFTER_FIRST && $position != MYSQL_AFTER_LAST && !preg_match('#^' . MYSQL_AFTER_POSITION . MYSQL_AFTER_SPLIT . '(.+?)$#', $position, $positions)) {
                        $notice .= 'Vị trí không hợp lệ';
                    } else if (empty($column)) {
                        $notice .= 'Chưa nhập tên cột';
                    } else if (isColumnsExists($column, $name, null, true)) {
                        $notice .= 'Tên cột đã tồn tại';
                    } else if (!empty($length) && !preg_match('#\\b[0-9]+\\b#', $length)) {
                        $notice .= 'Độ dài không hợp lệ';
                    } else {
                        $type_put = $type . (empty($length) == false ? "($length)" : null);
                        $collection_put = $collection == MYSQL_COLLECTION_NONE ? null : 'CHARACTER SET ' . $matches[1] . ' COLLATE ' . $matches[2];
                        $attributes_put = $attributes == MYSQL_ATTRIBUTES_NONE ? null : $attributes;
                        $null_put = $is_null ? 'NULL' : 'NOT NULL';
                        $default_put = $default == null ? null : "DEFAULT '$default'";
                        $auto_increment_put = $auto_increment ? 'AUTO_INCREMENT' : null;
                        $field_key_put = $field_key == MYSQL_FIELD_KEY_NONE ? null : $field_key;
                        $after_put = $position == MYSQL_AFTER_FIRST ? 'FIRST' : ($position == MYSQL_AFTER_LAST ? null : 'AFTER `' . $positions[1] . '`');

                        $sql = "ALTER TABLE `$name` ADD `$column` $type_put";

                        if ($attributes_put != null)
                            $sql .= ' ' . $attributes_put;

                        if ($collection_put != null)
                            $sql .= ' ' . $collection_put;

                        $sql .= ' ' . $null_put;

                        if ($default_put != null)
                            $sql .= ' ' . $default_put;

                        if ($auto_increment_put != null)
                            $sql .= ' ' . $auto_increment_put;

                        if ($field_key_put != null)
                            $sql .= ' ' . $field_key_put;

                        if ($after_put != null)
                            $sql .= ' ' . $after_put;

                        if (!@mysql_query($sql, LINK_IDENTIFIER)) {
                            $notice .= 'Lỗi tạo cột: ' . mysql_error();
                        } else {
                            if (isset($_POST['continue'])) {
                                $column = null;
                                $default = null;
                                $length = null;
                                $type = null;
                                $collection = null;
                                $attributes = null;
                                $position = null;
                                $field_key = null;
                                $is_null = false;
                                $auto_increment = false;
                                $notice = '<div class="notice_succeed">Tạo cột thành công';
                            }

                            if (isset($_POST['create']))
                                goURL('database_table.php?action=list_struct&name=' . $name . DATABASE_NAME_PARAMATER_1);
                        }
                    }

                    $collection = $collection != MYSQL_COLLECTION_NONE && isset($matches) ? $matches[2] : MYSQL_COLLECTION_NONE;
                    $notice .= '</div>';
                }

                $query = @mysql_query("SHOW COLUMNS FROM `$name`", LINK_IDENTIFIER);
                $position_list = null;

                if (is_resource($query) && @mysql_num_rows($query) > 0) {
                    $position_list = '<optgroup label="Cột">';

                    while ($assoc = @mysql_fetch_assoc($query))
                        $position_list .= '<option value="' . MYSQL_AFTER_POSITION . MYSQL_AFTER_SPLIT. $assoc['Field'] . '"' . ($position == MYSQL_AFTER_POSITION . MYSQL_AFTER_SPLIT . $assoc['Field'] ? ' selected="selected"' : null) . '>' . $assoc['Field'] . '</option>';

                    $position_list .= '</optgroup>';
                }

                if (@mysql_num_rows(@mysql_query("SHOW INDEXES FROM `$name` WHERE `Key_name`='PRIMARY'", LINK_IDENTIFIER)) > 0 && $field_key == null)
                    $field_key = MYSQL_FIELD_KEY_NONE;

                echo '<div class="title"><div class="ellipsis">' . $title . '</div></div>';
                echo $notice;
                echo '<div class="list">
                    <form action="database_table.php?action=add_columns&name=' . $name . DATABASE_NAME_PARAMATER_1 . '" method="post">
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
                        <span class="bull">&bull;</span>Vị trí:<br/>
                        <select name="position">
                            <option value="' . MYSQL_AFTER_FIRST . '"' . ($position == MYSQL_AFTER_FIRST ? ' selected="selected"' : null) . '>Đầu bảng</option>
                            <option value="' . MYSQL_AFTER_LAST . '"' . ($position == null || empty($position) || $position == MYSQL_AFTER_LAST ? ' selected="selected"' : null) . '>Cuối bảng</option>
                            ' . $position_list . '
                        </select><br/>
                        <span class="bull">&bull;</span>Khóa:
                        <br/>' . printFieldKey('field_key', stripslashes($field_key)) . '<br/>
                        <span class="bull">&bull;</span>Thêm:<br/>
                        <input type="checkbox" name="is_null" value="1"' . ($is_null ? ' checked="checked"' : null) . '/>Null<br/>
                        <input type="checkbox" name="auto_increment" value="1"' . ($auto_increment ? ' checked="checked"' : null) . '/>Tự tăng giá trị<hr/>
                        <input type="submit" name="continue" value="Tiếp tục"/>
                        <input type="submit" name="create" value="Tạo"/>
                    </form>
                </div>
                <div class="tips">
                    <img src="icon/tips.png"/> Ấn tiếp tục để tạo và tạo tiếp, ấn tạo để tạo và về danh sách cột
                </div>';
            } else if (isset($_GET['action']) && trim($_GET['action']) == 'edit_columns') {
                $info = null;
                $title = 'Sửa cột: ' . DATABASE_NAME . ' > ' . $name;
                $columns = isset($_GET['columns']) && empty($_GET['columns']) == false ? addslashes($_GET['columns']) : null;

                include_once 'header.php';

                if ($columns != null && isColumnsExists($columns, $name, null, true, $info)) {
                    $column = null;
                    $default = null;
                    $length = null;
                    $type = null;
                    $collection = null;
                    $attributes = null;
                    $is_null = false;
                    $auto_increment = false;
                    $notice = null;

                    if (isset($_POST['submit'])) {
                        $column = addslashes($_POST['column']);
                        $default = addslashes($_POST['default']);
                        $length = addslashes($_POST['length']);
                        $type = addslashes($_POST['type']);
                        $collection = addslashes($_POST['collection']);
                        $attributes = addslashes($_POST['attributes']);
                        $is_null = isset($_POST['is_null']);
                        $auto_increment = isset($_POST['auto_increment']);
                        $notice = '<div class="notice_failure">';

                        if ($collection != MYSQL_COLLECTION_NONE && !preg_match('#^(.+?)' . MYSQL_COLLECTION_SPLIT . '(.+?)$#i', $collection, $matches)) {
                            $notice .= 'Mã hóa - Đối chiếu không hợp lệ';
                        } else if (empty($column)) {
                            $notice .= 'Chưa nhập tên cột';
                        } else if (isColumnsExists($column, $name, $columns, true)) {
                            $notice .= 'Tên cột đã tồn tại';
                        } else if (!empty($length) && !preg_match('#\\b[0-9]+\\b#', $length)) {
                            $notice .= 'Độ dài không hợp lệ';
                        } else {
                            $type_put = $type . (empty($length) == false ? "($length)" : null);
                            $collection_put = $collection == MYSQL_COLLECTION_NONE ? null : 'CHARACTER SET ' . $matches[1] . ' COLLATE ' . $matches[2];
                            $attributes_put = $attributes == MYSQL_ATTRIBUTES_NONE ? null : $attributes;
                            $null_put = $is_null ? 'NULL' : 'NOT NULL';
                            $default_put = $default == null ? null : "DEFAULT '$default'";
                            $auto_increment_put = $auto_increment ? 'AUTO_INCREMENT' : null;

                            $sql = "ALTER TABLE `$name` CHANGE `$columns` `$column` $type_put";

                            if ($attributes_put != null)
                                $sql .= ' ' . $attributes_put;

                            if ($collection_put != null)
                                $sql .= ' ' . $collection_put;

                            $sql .= ' ' . $null_put;

                            if ($default_put != null)
                                $sql .= ' ' . $default_put;

                            if ($auto_increment_put != null)
                                $sql .= ' ' . $auto_increment_put;

                            if (!@mysql_query($sql, LINK_IDENTIFIER))
                                $notice .= 'Lỗi sửa cột: ' . mysql_error();
                            else
                                goURL('database_table.php?action=list_struct&name=' . $name . DATABASE_NAME_PARAMATER_1);
                        }

                        $collection = $collection != MYSQL_COLLECTION_NONE && isset($matches) ? $matches[2] : MYSQL_COLLECTION_NONE;
                        $notice .= '</div>';
                    } else {
                        $column = $info['Field'];
                        $type = $info['Type'];

                        if (strpos($info['Type'], ' ')) {
                            $type = explode(' ', $info['Type']);
                            $attributes = strtoupper($type[1]);
                            $type = $type[0];
                        }

                        if (preg_match('#(\w+)\s*\((\d+)\)#i', $type, $matches)) {
                            $type = strtoupper($matches[1]);
                            $length = intval($matches[2]);
                        } else {
                            $type = strtoupper($type);
                        }

                        $default = htmlspecialchars($info['Default']);
                        $is_null = strtolower($info['Null']) != 'no';
                        $auto_increment = strtolower($info['Extra']) == 'auto_increment';
                        $isDataTypeNumeric = isDataTypeNumeric($type);

                        if ($isDataTypeNumeric)
                            $collection = MYSQL_COLLECTION_NONE;
                    }

                    echo '<div class="title"><div class="ellipsis">' . $title . '</div></div>';
                    echo $notice;
                    echo '<div class="list">
                        <span class="bull">&bull;</span>Cột: <strong class="name_columns_edit">' . $columns . '</strong><hr/>
                        <form action="database_table.php?action=edit_columns&name=' . $name . DATABASE_NAME_PARAMATER_1 . '&columns=' . $columns . '" method="post">
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
                            <span class="bull">&bull;</span>Thêm:<br/>
                            <input type="checkbox" name="is_null" value="1"' . ($is_null ? ' checked="checked"' : null) . '/>Null<br/>
                            <input type="checkbox" name="auto_increment" value="1"' . ($auto_increment ? ' checked="checked"' : null) . '/>Tự tăng giá trị<hr/>
                            <input type="submit" name="submit" value="Lưu"/>
                            <a href="database_table.php?action=delete_columns' . DATABASE_NAME_PARAMATER_1 . '&name=' . $name . '&columns=' . $columns . '" id="href_delete_columns">Xóa</a>
                        </form>
                    </div>';
                } else {
                    echo '<div class="title"><div class="ellipsis">' . $title . '</div></div>
                    <div class="list">Tên cột không tồn tại</div>';
                }
            } else if (isset($_GET['action']) && trim($_GET['action']) == 'delete_columns') {
                $info = null;
                $title = 'Sửa cột: ' . DATABASE_NAME . ' > ' . $name;
                $columns = isset($_GET['columns']) && empty($_GET['columns']) == false ? addslashes($_GET['columns']) : null;

                include_once 'header.php';

                if ($columns != null && isColumnsExists($columns, $name, null, true, $info)) {

                    echo '<div class="title"><div class="ellipsis">' . $title . '</div></div>';

                    if (isset($_POST['accept'])) {
                        if (!@mysql_query("ALTER TABLE `$name` DROP `$columns`", LINK_IDENTIFIER))
                            echo '<div class="notice_failure">Xóa cột thất bại: ' . mysql_error() . '</div>';
                        else
                            goURL('database_table.php?action=list_struct' . DATABASE_NAME_PARAMATER_1 . '&name=' . $name);
                    } else if (isset($_POST['not'])) {
                        goURL('database_table.php?action=list_struct' . DATABASE_NAME_PARAMATER_1 . '&name=' . $name);
                    }

                    echo '<div class="list">
                        <form action="database_table.php?action=delete_columns' . DATABASE_NAME_PARAMATER_1 . '&name=' . $name . '&columns=' . $columns . '" method="post">
                            <span>Bạn có thực sự muốn xóa cột <strong class="name_columns_delete">' . $info['Field'] . '</strong> này không?</span><hr/>
                            <center>
                                <input type="submit" name="accept" value="Xóa"/>
                                <input type="submit" name="not" value="Huỷ"/>
                                <a href="database_table.php?action=edit_columns' . DATABASE_NAME_PARAMATER_1 . '&name=' . $name . '&columns=' . $columns . '" id="href_edit_columns">Sửa</a>
                            </center>
                        </form>
                    </div>';
                } else {
                    echo '<div class="title"><div class="ellipsis">' . $title . '</div></div>
                    <div class="list">Tên cột không tồn tại</div>';
                }
            } else if (isset($_GET['action']) && trim($_GET['action']) == 'add_data') {
                $title = 'Tạo dữ liệu: ' . DATABASE_NAME . ' > ' . $name;
                $query = @mysql_query("SHOW COLUMNS FROM `$name`", LINK_IDENTIFIER);
                $count = @mysql_num_rows($query);

                if ($page['current'] > 1) {
                    $page['paramater_0'] = '?page=' . $page['current'];
                    $page['paramater_1'] = '&page=' . $page['current'];
                }

                include_once 'header.php';

                if (is_resource($query) && $count > 0) {
                    echo '<div class="title"><div class="ellipsis">' . $title . '</div></div>';

                    $array = array();
                    $data = array();

                    while ($assoc = @mysql_fetch_assoc($query)) {
                        $array[$assoc['Field']] = $assoc;
                        $data[$assoc['Field']] = null;
                    }

                    if (isset($_POST['continue']) || isset($_POST['create'])) {
                        $sql = "INSERT INTO `$name` SET";
                        $split = null;
                        $cnt = count($data);
                        $i = 1;

                        foreach ($data AS $key => $value) {
                            $data[$key] = addslashes($_POST[$key]);
                            $split = $i < $count ? ',' : null;
                            $sql .= " `$key`='{$data[$key]}'{$split}";
                            $i++;
                        }

                        if (!@mysql_query($sql, LINK_IDENTIFIER)) {
                            echo '<div class="notice_failure">Tạo dữ liệu thất bại: ' . mysql_error() . '</div>';
                        } else {
                            if (isset($_POST['continue'])) {
                                foreach ($data AS $key => $value)
                                    $data[$key] = null;

                                echo '<div class="notice_succeed">Tạo dữ liệu thành công</div>';
                            } else if (isset($_POST['create'])) {
                                goURL('database_table.php?name=' . $name . DATABASE_NAME_PARAMATER_1 . $page['paramater_1'] . $order['paramater_1']);
                            }
                        }
                    }

                    echo '<div class="list">
                        <form action="database_table.php?action=add_data&name=' . $name . DATABASE_NAME_PARAMATER_1 . $page['paramater_1'] . $order['paramater_1'] . '" method="post">';

                        foreach ($array AS $key => $value) {
                            echo '<span class="bull">&bull;</span>Cột (<strong class="name_columns_create_data">' . $key . '</strong>):<br/>';

                            if (preg_match('/^([a-zA-Z0-9\-_]+)(\(+|\s+|\\b)/', $value['Type'], $matches) && isDataTypeHasLength($matches[1]) == false)
                                echo '<textarea cols="18" rows="5" name="' . $key . '">' . htmlspecialchars(stripslashes($data[$key])) . '</textarea>';
                            else
                                echo '<input type="text" name="' . $key . '" value="' . htmlspecialchars(stripslashes($data[$key])) . '" size="18"/>';

                            echo '<br/>';
                        }

                            echo '<hr/><input type="submit" name="continue" value="Tiếp tục"/> <input type="submit" name="create" value="Tạo"/>
                        </form>
                    </div>
                    <div class="tips">
                        <img src="icon/tips.png"/> Ấn tiếp tục để tạo và tạo tiếp, ấn tạo để tạo và về danh sách dữ liệu
                    </div>';
                } else if ($count <= 0) {
                    echo '<div class="title"><div class="ellipsis">' . $title . '</div></div>
                    <div class="list">
                        <span>Danh sách cột trống</span>
                    </div>';
                } else {
                    echo '<div class="title"><div class="ellipsis">' . $title . '</div></div>
                    <div class="list">
                        <span>Không thể lấy danh sách cột</span>
                    </div>';
                }
            } else if (isset($_GET['action']) && trim($_GET['action']) == 'truncate') {
                $title = 'Xóa sạch dữ liệu: ' . DATABASE_NAME . ' > ' . $name;

                include_once 'header.php';

                echo '<div class="title"><div class="ellipsis">' . $title . '</div></div>';

                if (isset($_POST['accept'])) {
                    if (!@mysql_query("TRUNCATE TABLE `$name`", LINK_IDENTIFIER))
                        echo '<div class="notice_failure">Xóa sạch dữ liệu bảng thất bại</div>';
                    else
                        goURL('database_table.php?name=' . $name . DATABASE_NAME_PARAMATER_1 . $order['paramater_1']);
                } else if (isset($_POST['not'])) {
                    goURL('database_table.php?name=' . $name . DATABASE_NAME_PARAMATER_1 . $page['paramater_1'] . $order['paramater_1']);
                }

                echo '<div class="list">
                    <form action="database_table.php?action=truncate&name=' . $name . DATABASE_NAME_PARAMATER_1 . $page['paramater_1'] . $order['paramater_1'] . '" method="post">
                        <span>Bạn có thực sự muốn xóa sạch dữ liệu của bảng không, các cột sẽ không bị xóa?</span><hr/>
                        <center>
                            <input type="submit" name="accept" value="Xóa"/>
                            <input type="submit" name="not" value="Huỷ"/>
                        </center>
                    </form>
                </div>';
            } else if (isset($_GET['action']) && trim($_GET['action']) == 'view_data') {
                $title = 'Xem dữ liệu: ' . DATABASE_NAME . ' > ' . $name;
                $where = isset($_GET['where']) && empty($_GET['where']) == false ? addslashes(rawurldecode($_GET['where'])) : null;

                include_once 'header.php';

                if ($where != null) {
                    $key = getColumnsKey($name);
                    $info = @mysql_fetch_assoc(@mysql_query("SELECT * FROM `$name` WHERE `$key`='$where' LIMIT 1", LINK_IDENTIFIER));

                    if ($info != false) {
                        echo '<div class="title"><div class="ellipsis">' . $title . '</div></div>
                        <div class="list">
                            <span class="bull">&bull;</span>[<strong class="name_columns_edit">' . htmlspecialchars($key) . '</strong>] => <span>' . htmlspecialchars(stripslashes($where)) . '</span><hr/>
                            <center>
                                <a href="database_table.php?action=edit_data&name=' . $name . DATABASE_NAME_PARAMATER_1 . '&where=' . rawurlencode($where) . $page['paramater_1'] . $order['paramater_1'] . '">Sửa</a>
                                <span> | </span>
                                <a href="database_table.php?action=delete_data&name=' . $name . DATABASE_NAME_PARAMATER_1 . '&where=' . rawurlencode($where) . $page['paramater_1'] . $order['paramater_1'] . '">Xóa</a>
                            </center>
                        </div>
                        <div class="list_line">';

                        foreach ($info AS $key => $value) {
                            echo '<div id="line">
                                <div>
                                    <span>' . htmlspecialchars($value) . '</span>
                                </div>
                                <div>
                                    [<strong class="name_columns_edit">' . htmlspecialchars($key) . '</strong>]
                                </div>
                            </div>';
                        }

                        echo '</div>';
                    } else {
                        echo '<div class="title"><div class="ellipsis">' . $title . '</div></div>
                        <div class="list">Lỗi khóa dữ liệu không đúng hoặc dữ liệu không tồn tại</div>';
                    }
                } else {
                    echo '<div class="title"><div class="ellipsis">' . $title . '</div></div>
                    <div class="list">Lỗi khóa dữ liệu không đúng hoặc dữ liệu không tồn tại</div>';
                }
            } else if (isset($_GET['action']) && trim($_GET['action']) == 'edit_data') {
                $title = 'Sửa dữ liệu: ' . DATABASE_NAME . ' > ' . $name;
                $where = isset($_GET['where']) && empty($_GET['where']) == false ? addslashes(rawurldecode($_GET['where'])) : null;

                include_once 'header.php';

                if ($where != null) {
                    $key = getColumnsKey($name);
                    $data = @mysql_fetch_assoc(@mysql_query("SELECT * FROM `$name` WHERE `$key`='$where' LIMIT 1", LINK_IDENTIFIER));
                    $columns = @mysql_query("SHOW COLUMNS FROM `$name`", LINK_IDENTIFIER);

                    if ($data != false && is_resource($columns)) {
                        $array = array();
                        $count = 0;
                        $i = 0;

                        while ($assoc = @mysql_fetch_assoc($columns))
                            $array[$assoc['Field']] = $assoc;

                        $count = count($array);

                        echo '<div class="title"><div class="ellipsis">' . $title . '</div></div>';

                        if (isset($_POST['submit'])) {
                            $sql = "UPDATE `$name` SET";

                            foreach ($array AS $k => $v) {
                                $data[$k] = addslashes($_POST[$k]);
                                $sql .= " `$k`='{$data[$k]}'";

                                if ($i < $count - 1)
                                    $sql .= ', ';

                                $i++;
                            }

                            $sql .= " WHERE `$key`='$where' LIMIT 1";
                            $i = 0;

                            if (!@mysql_query($sql, LINK_IDENTIFIER))
                                echo '<div class="notice_failure">Lưu thất bại: ' . mysql_error() . '</div>';
                            else
                                goURL('database_table.php?name=' . $name . DATABASE_NAME_PARAMATER_1 . $page['paramater_1'] . $order['paramater_1']);
                        }

                        echo '<div class="list">
                            <span class="bull">&bull;</span>[<strong class="name_columns_edit">' . htmlspecialchars($key) . '</strong>] => <span>' . htmlspecialchars(stripslashes($where)) . '</span><hr/>
                            <form action="database_table.php?action=edit_data&name=' . $name . DATABASE_NAME_PARAMATER_1 . '&where=' . rawurlencode($where) . $page['paramater_1'] . $order['paramater_1'] . '" method="post">';

                            foreach ($array AS $k => $v) {
                                echo '<span class="bull">&bull;</span>Cột (<strong class="name_columns_create_data">' . $k . '</strong>):<br/>';

                                if (preg_match('/^([a-zA-Z0-9\-_]+)(\(+|\s+|\\b)/', $v['Type'], $matches) && isDataTypeHasLength($matches[1]) == false)
                                    echo '<textarea cols="18" rows="5" name="' . $k . '">' . htmlspecialchars(stripslashes($data[$k])) . '</textarea>';
                                else
                                    echo '<input type="text" name="' . $k . '" value="' . htmlspecialchars(stripslashes($data[$k])) . '" size="18"/>';

                                if ($i < $count - 1)
                                    echo '<br/>';
                                else
                                    echo '<hr/>';

                                $i++;
                            }

                                echo '<input type="submit" name="submit" value="Lưu"/>
                                <a href="database_table.php?action=delete_data&name=' . $name . DATABASE_NAME_PARAMATER_1 . '&where=' . rawurlencode($where) . $page['paramater_1'] . $order['paramater_1'] . '" id="href_delete_columns">Xóa</a>
                                <a href="database_table.php?action=view_data&name=' . $name . DATABASE_NAME_PARAMATER_1 . '&where=' . rawurlencode($where) . $page['paramater_1'] . $order['paramater_1'] . '" id="href_edit_columns">Xem</a>
                            </form>
                        </div>';
                    } else {
                        echo '<div class="title"><div class="ellipsis">' . $title . '</div></div>
                        <div class="list">Lỗi khóa dữ liệu không đúng hoặc dữ liệu không tồn tại</div>';
                    }
                } else {
                    echo '<div class="title"><div class="ellipsis">' . $title . '</div></div>
                    <div class="list">Lỗi khóa dữ liệu không đúng hoặc dữ liệu không tồn tại</div>';
                }
            } else if (isset($_GET['action']) && trim($_GET['action']) == 'delete_data') {
                $title = 'Xóa dữ liệu: ' . DATABASE_NAME . ' > ' . $name;
                $where = isset($_GET['where']) && empty($_GET['where']) == false ? addslashes(rawurldecode($_GET['where'])) : null;

                include_once 'header.php';

                if ($where != null) {
                    $key = getColumnsKey($name);

                    if (@mysql_num_rows(@mysql_query("SELECT * FROM `$name` WHERE `$key`='$where' LIMIT 1", LINK_IDENTIFIER)) > 0) {
                        echo '<div class="title"><div class="ellipsis">' . $title . '</div></div>';

                        if (isset($_POST['submit'])) {
                            if (!@mysql_query("DELETE FROM `$name` WHERE `$key`='$where' LIMIT 1", LINK_IDENTIFIER))
                                echo '<div class="notice_failure">Xóa thất bại: ' . mysql_error() . '</div>';
                            else
                                goURL('database_table.php?name=' . $name . DATABASE_NAME_PARAMATER_1 . $page['paramater_1'] . $order['paramater_1']);
                        }

                        echo '<div class="list">
                            <span class="bull">&bull;</span>[<strong class="name_columns_edit">' . htmlspecialchars($key) . '</strong>] => <span>' . htmlspecialchars(stripslashes($where)) . '</span><hr/>
                            <form action="database_table.php?action=delete_data&name=' . $name . DATABASE_NAME_PARAMATER_1 . '&where=' . rawurlencode($where) . $page['paramater_1'] . $order['paramater_1'] . '" method="post">
                                <span>Bạn có thật sự muốn xóa dữ liệu này không?</span><hr/>
                                <center>
                                    <input type="submit" name="submit" value="Xóa"/>
                                    <a href="database_table.php?action=edit_data&name=' . $name . DATABASE_NAME_PARAMATER_1 . '&where=' . rawurlencode($where) . $page['paramater_1'] . $order['paramater_1'] . '" id="href_delete_columns">Sửa</a>
                                    <a href="database_table.php?action=view_data&name=' . $name . DATABASE_NAME_PARAMATER_1 . '&where=' . rawurlencode($where) . $page['paramater_1'] . $order['paramater_1'] . '" id="href_edit_columns">Xem</a>
                                </center>
                            </form>
                        </div>';
                    } else {
                        echo '<div class="title"><div class="ellipsis">' . $title . '</div></div>
                        <div class="list">Lỗi khóa dữ liệu không đúng hoặc dữ liệu không tồn tại</div>';
                    }
                } else {
                    echo '<div class="title"><div class="ellipsis">' . $title . '</div></div>
                    <div class="list">Lỗi khóa dữ liệu không đúng hoặc dữ liệu không tồn tại</div>';
                }
            } else if (isset($_GET['action']) && trim($_GET['action']) == 'selected_data') {
                $title = 'Chọn lựa: ' . DATABASE_NAME . ' > ' . $name;
                $entrys = isset($_POST['entry']) && is_array($_POST['entry']) && count($_POST['entry']) > 0 ? $_POST['entry'] : null;

                if (isset($_POST['delete']) && $entry != null) {
                    $title = 'Xóa dữ liệu: ' . DATABASE_NAME . ' > ' . $name;
                    $key = getColumnsKey($name);
                    $isAllExists = true;
                    $entryHtml = null;
                    $listEntryHtml = null;

                    foreach ($entrys AS $v) {
                        if (@mysql_num_rows(@mysql_query("SELECT `$key` FROM `$name` WHERE `$key`='" . addslashes($v) ."' LIMIT 1", LINK_IDENTIFIER)) == 0) {
                            $isAllExists = false;
                            break;
                        } else {
                            $entryHtml .= '<input type="hidden" name="entry[]" value="' . $v . '"/>';
                            $listEntryHtml .= '<li><img src="icon/rows.png"/> <span>' . $v . '</span></li>';
                        }
                    }

                    include_once 'header.php';

                    if ($isAllExists) {
                        echo '<div class="title"><div class="ellipsis">' . $title . '</div></div>';

                        if (isset($_POST['accept'])) {
                            $isDeleteAll = true;

                            foreach ($entrys AS $v) {
                                if (!@mysql_query("DELETE FROM `$name` WHERE `$key`='" . addslashes($v) . "' LIMIT 1", LINK_IDENTIFIER)) {
                                    $isDeleteAll = false;

                                    echo '<div class="notice_failure">Xóa [<strong>' . $v . '</strong>] thất bại: ' . mysql_error() . '</div>';
                                } else {
                                    echo '<div class="notice_succeed">Xóa [<strong>' . $v . '</strong>] thành công</div>';
                                }
                            }

                            if ($isDeleteAll)
                                goURL('database_table.php?name=' . $name . DATABASE_NAME_PARAMATER_1 . $page['paramater_1'] . $order['paramater_1']);
                        } else if (isset($_POST['not'])) {
                            goURL('database_table.php?name=' . $name . DATABASE_NAME_PARAMATER_1 . $page['paramater_1'] . $order['paramater_1']);
                        }

                        echo '<ul class="list">' . $listEntryHtml . '</ul>';

                        echo '<div class="list">
                            <form action="database_table.php?action=selected_data&name=' . $name . DATABASE_NAME_PARAMATER_1  . $page['paramater_1'] . $order['paramater_1'] . '" method="post">
                                <span>Bạn có thật sự muốn xóa những dữ liệu đã chọn không?</span><hr/>
                                <input type="hidden" name="delete" value="1"/>
                                ' . $entryHtml . '
                                <center>
                                    <input type="submit" name="accept" value="Xóa"/>
                                    <input type="submit" name="not" value="Huỷ"/>
                                </center>
                            </form>
                        </div>';
                    } else {
                        echo '<div class="title"><div class="ellipsis">' . $title . '</div></div>
                        <div class="list">Dữ liệu không tồn tại</div>';
                    }
                } else if ($entrys == null) {
                    include_once 'header.php';

                    echo '<div class="title"><div class="ellipsis">' . $title . '</div></div>
                    <div class="list">Không có mục nào được chọn</div>';
                } else {
                    include_once 'header.php';

                    echo '<div class="title"><div class="ellipsis">' . $title . '</div></div>
                    <div class="list">Không có lựa chọn</div>';
                }
            } else {
                $title = 'Danh sách dữ liệu: ' . DATABASE_NAME . ' > ' . $name;
                $by = getColumnsKey($name);

                include_once 'header.php';

                $page['start'] = 0;
                $page['end'] = $configs['page_database_list_rows'];

                if ($page['current'] > 1 && $configs['page_database_list_rows'] > 0) {
                    $page['start'] = ($page['current'] * $configs['page_database_list_rows']) - $configs['page_database_list_rows'];
                    $page['end'] = $configs['page_database_list_rows'];

                    $page['paramater_0'] = '?page=' . $page['current'];
                    $page['paramater_1'] = '&page=' . $page['current'];
                }

                if ($configs['page_database_list_rows'] > 0 && empty($by) == false)
                    $query = @mysql_query("SELECT * FROM `$name` ORDER BY `$by` {$order['name']} LIMIT {$page['start']}, {$page['end']}", LINK_IDENTIFIER);
                else if (empty($by) == false)
                    $query = @mysql_query("SELECT * FROM `$name` ORDER BY `$by` {$order['name']}", LINK_IDENTIFIER);

                $count = empty($by) == false ? @mysql_num_rows(@mysql_query("SELECT * FROM `$name`", LINK_IDENTIFIER)) : 0;

                if ($count <= 0 && isset($_GET['start']))
                    goURL('database_table.php?action=list_struct' . DATABASE_NAME_PARAMATER_1 . '&name=' . $name . $order['paramater_1']);

                echo '<div class="title"><div class="ellipsis">' . $title . '</div></div>';

                if ($count <= 0) {
                    echo '<ul class="list">
                        <li>
                            <img src="icon/empty.png"/> Không có dữ liệu
                        </li>
                    </ul>';
                } else {
                    if ($configs['page_database_list_rows'] > 0) {
                        $page['total'] = ceil($count / $configs['page_database_list_rows']);

                        if ($page['current'] > $page['total'])
                            goURL('database_table.php?name=' . $name . DATABASE_NAME_PARAMATER_1 . ($page['total'] <= 1 ? null : '&page=' . $page['total']) . $order['paramater_1']);
                    }

                    echo '<script language="javascript" src="checkbox.js"></script>';
                    echo '<form action="database_table.php?action=selected_data&name=' . $name . DATABASE_NAME_PARAMATER_1 . $page['paramater_1']  . $order['paramater_1'] . '" method="post" name="form"><ul class="list">';

                    {
                        echo '<li><center>';

                        if ($order['desc'] == true)
                            echo '<a href="database_table.php?name=' . $name . DATABASE_NAME_PARAMATER_1 . $page['paramater_1'] . '"><strong class="order_query_href">ASC</strong></a>';
                        else
                            echo '<strong class="order_query">ASC</strong>';

                        echo ' <span> | </span> ';

                        if ($order['desc'] == false)
                            echo '<a href="database_table.php?name=' . $name . DATABASE_NAME_PARAMATER_1 . $page['paramater_1'] . '&order=1"><strong class="order_query_href">DESC</strong></a>';
                        else
                            echo '<strong class="order_query">DESC</strong>';

                        echo '</center></li>';
                    }

                    while ($assoc = @mysql_fetch_assoc($query))
                        echo '<li>
                            <input type="checkbox" name="entry[]" value="' . $assoc[$by] . '"/>
                            <a href="database_table.php?action=edit_data&name=' . $name . DATABASE_NAME_PARAMATER_1 . '&where=' . rawurlencode($assoc[$by]) . $page['paramater_1'] . $order['paramater_1'] . '">
                                <img src="icon/rows.png"/>
                            </a>
                            <a href="database_table.php?action=view_data&name=' . $name . DATABASE_NAME_PARAMATER_1 . '&where=' . rawurlencode($assoc[$by]) . $page['paramater_1'] . $order['paramater_1'] . '">
                                <span>' . htmlspecialchars($assoc[$by]) . '</span>
                            </a>
                        </li>';

                    echo '<li><input type="checkbox" name="all" value="1" onClick="javascript:onCheckItem();"/> <strong class="form_checkbox_all">Chọn tất cả</strong></li>';

                    if ($page['total'] > 1)
                        echo '<li class="page">' . page($page['current'], $page['total'], array(PAGE_URL_DEFAULT => 'database_table.php?name=' . $name . DATABASE_NAME_PARAMATER_1 . $order['paramater_1'], PAGE_URL_START => 'database_table.php?name=' . $name . DATABASE_NAME_PARAMATER_1 . $order['paramater_1'] . '&page=')) . '</li>';

                    echo '</ul>
                        <div class="list">
                            <input type="submit" name="delete" value="Xóa"/>
                        </div>
                    </form>';
                }
            }

            echo '<div class="title">Chức năng</div>
            <ul class="list">';

                if (isset($_GET['action']) == false || (isset($_GET['action']) && $_GET['action'] != 'add_columns'))
                    echo '<li><img src="icon/create.png"/> <a href="database_table.php?action=add_columns' . DATABASE_NAME_PARAMATER_1 . '&name=' . $name . '">Tạo cột</a></li>';

                if (isset($_GET['action']) == false || (isset($_GET['action']) && $_GET['action'] != 'add_data'))
                    echo '<li><img src="icon/insert_query.png"/> <a href="database_table.php?action=add_data' . DATABASE_NAME_PARAMATER_1 . '&name=' . $name . $page['paramater_1'] . $order['paramater_1'] . '">Tạo dữ liệu</a></li>';

                if (isset($_GET['action']) == false || (isset($_GET['action']) && $_GET['action'] != 'rename'))
                    echo '<li><img src="icon/rename.png"/> <a href="database_table.php?action=rename' . DATABASE_NAME_PARAMATER_1 . '&name=' . $name . '">Đổi tên bảng</a></li>';

                if (isset($_GET['action']) == false || (isset($_GET['action']) && $_GET['action'] != 'delete'))
                    echo '<li><img src="icon/delete.png"/> <a href="database_table.php?action=delete' . DATABASE_NAME_PARAMATER_1 . '&name=' . $name . '">Xóa bảng</a></li>';

                if (isset($_GET['action']) == false || (isset($_GET['action']) && $_GET['action'] != 'truncate'))
                    echo '<li><img src="icon/clear.png"/> <a href="database_table.php?action=truncate&name=' . $name . DATABASE_NAME_PARAMATER_1 . $page['paramater_1'] . $order['paramater_1'] . '">Xóa sạch dữ liệu</a></li>';

                if (isset($_GET['action']) && empty($_GET['action']) == false)
                    echo '<li><img src="icon/rows.png"/> <a href="database_table.php?name=' . $name . DATABASE_NAME_PARAMATER_1 . $page['paramater_1'] . $order['paramater_1'] . '">Danh sách dữ liệu</a></li>';

                if (isset($_GET['action']) == false || (isset($_GET['action']) && $_GET['action'] != 'list_struct'))
                    echo '<li><img src="icon/columns.png"/> <a href="database_table.php?action=list_struct' . DATABASE_NAME_PARAMATER_1 . '&name=' . $name . '">Danh sách cột</a></li>';

                echo '<li><img src="icon/database_table.png"/> <a href="database_tables.php' . DATABASE_NAME_PARAMATER_0 . '">Danh sách bảng</a></li>';

                if (IS_DATABASE_ROOT)
                    echo '<li><img src="icon/database.png"/> <a href="database_lists.php">Danh sách database</a></li>';

            echo '</ul>';
        } else if ($name == null || $isTableExists == false) {
            include_once 'header.php';

            echo '<div class="title">' . $title . '</div>
            <div class="list">Tên bảng không tồn tại</div>
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