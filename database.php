<?php define('ACCESS', true);

    include_once 'function.php';

    if (IS_LOGIN) {
        $title = 'Kết nối database';

        include_once 'header.php';

        $host = 'localhost';
        $username = 'root';
        $password = null;
        $name = null;
        $notice = null;
        $auto = false;
        $go = false;

        if (is_file(PATH_DATABASE)) {
            include PATH_DATABASE;

            if (isDatabaseVariable($databases)) {
                $host =$databases['db_host'];
                $username = $databases['db_username'];
                $password = $databases['db_password'];
                $name = $databases['db_name'];
                $auto = $databases['is_auto'];

                if ($auto && !isset($_POST['submit'])) {
                    if (!@mysql_connect($host, $username, $password))
                        $notice = '<div class="notice_failure">Không thể kết nối tới database</div>';
                    else if (!empty($name) && !@mysql_select_db($name))
                        $notice = '<div class="notice_failure">Không thể chọn database</div>';
                    else
                        $go = true;
                }
            } else if (!isset($_POST['submit'])) {
                if (@is_file(REALPATH . '/' . PATH_DATABASE))
                    @unlink(REALPATH . '/' . PATH_DATABASE);

                $notice = '<div class="notice_failure">Cấu hình database bị lỗi</div>';
            }
        }

        if (isset($_POST['submit'])) {
            $host = addslashes($_POST['host']);
            $username = addslashes($_POST['username']);
            $password = addslashes($_POST['password']);
            $name = addslashes($_POST['name']);
            $auto = isset($_POST['is_auto']) && intval($_POST['is_auto']) == 1;

            if (empty($host) || empty($username)) {
                $notice = '<div class="notice_failure">Chưa nhập đầy đủ thông tin</div>';
            } else if (!@mysql_connect($host, $username, $password)) {
                $notice = '<div class="notice_failure">Không thể kết nối tới database</div>';
            } else if (!empty($name) && !@mysql_select_db($name)) {
                $notice = '<div class="notice_failure">Không thể chọn database</div>';
            } else {
                if (createDatabaseConfig($host, $username, $password, $name, $auto))
                    $go = true;
                else
                    $notice = '<div class="notice_failure">Lưu cấu hình database thất bại</div>';
            }
        }

        if ($go) {
            if (empty($name) || $name == null)
                goURL('database_lists.php');
            else
                goURL('database_tables.php');
        }

        echo '<div class="title">' . $title . '</div>';
        echo $notice;
        echo '<div class="list">
            <form action="database.php" method="post">
                <span class="bull">&bull;</span>Host:<br/>
                <input type="text" name="host" value="' . stripslashes($host) . '" size="18"/><br/>
                <span class="bull">&bull;</span>Tài khoản database:<br/>
                <input type="text" name="username" value="' . stripslashes($username) . '" size="18"/><br/>
                <span class="bull">&bull;</span>Mật khẩu database:<br/>
                <input type="name" name="password" value="' . stripslashes($password) . '" size="18" autocomplete="off"/><br/>
                <span class="bull">&bull;</span>Tên database:<br/>
                <input type="text" name="name" value="' . stripslashes($name) . '" size="18"/><br/>
                <input type="checkbox" name="is_auto" value="1"' . ($auto ? ' checked="checked"' : null) . '/>Tự động kết nối<br/>
                <input type="submit" name="submit" value="Kết nối"/>
            </form>
        </div>
        <div class="tips"><img src="icon/tips.png"/> Tên database để trống nếu bạn muốn kết nối vào danh sách database. Nếu bạn không có toàn quyền với mysql hãy nhập tên database</div>
        <div class="title">Chức năng</div>
        <ul class="list">
            <li><img src="icon/list.png"/> <a href="index.php">Quản lý tập tin</a></li>
        </ul>';

        include_once 'footer.php';
    } else {
        goURL('login.php');
    }

?>