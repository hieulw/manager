<?php

    define('ACCESS', true);
    define('LOGIN', true);

    include_once 'function.php';

    if (IS_LOGIN) {
        goURL('index.php');
    } else {
        $title = 'Đăng nhập';
        $notice = null;

        if (isset($_POST['submit'])) {
            $notice = '<div class="notice_failure">';
            $username = addslashes($_POST['username']);
            $password = addslashes($_POST['password']);

            if ($username == null || $password == null) {
                $notice .= 'Chưa nhập đầy đủ thông tin';
            } else if (strtolower($username) != strtolower($configs['username']) || getPasswordEncode($password) != $configs['password']) {
                $notice .= 'Sai tài khoản hoặc mật khẩu';
            } else {
                $_SESSION[SESS] = true;

                goURL('index.php');
            }

            $notice .= '</div>';
        }

        include_once 'header.php';

        if (IS_INSTALL_ROOT_DIRECTORY) {
            echo '<div class="title">Lỗi File Manager</div>
            <div class="list">Bạn đang cài đặt File Manager trên thư mục gốc, hãy chuyển vào một thư mục. Bạn có thể đăng nhập nhưng tất cả các chức năng đều bị ẩn và chặn</div>';
        }

        echo '<div class="title">' . $title . '</div>';
        echo $notice;

        if (IS_CONFIG_UPDATE || IS_CONFIG_ERROR)
            @unlink(REALPATH . '/' . PATH_CONFIG);

        if (IS_CONFIG_UPDATE)
            echo '<div class="notice_info">Cấu hình cập nhật sẽ đưa về mặc định</div>';
        else if (IS_CONFIG_ERROR)
            echo '<div class="notice_failure">Cấu hình bị lỗi sẽ đưa về mặc định</div>';
        else if (!is_file(REALPATH . '/' . PATH_CONFIG))
            echo '<div class="notice_info">Cấu hình không tồn tại nó sẽ được tạo</div>';


        if (!is_file(REALPATH . '/' . PATH_CONFIG)) {
            if (createConfig())
                echo '<div class="notice_info">Tài khoản: <strong>' . LOGIN_USERNAME_DEFAULT . '</strong>, Mật khẩu: <strong>' . LOGIN_PASSWORD_DEFAULT . '</strong></div>';
            else
                echo '<div class="notice_failure">Tạo cấu hình thất bại, hãy thử lại</div>';
        }

        echo '<div class="list">
            <form action="login.php" method="post">
                <span class="bull">&bull;</span>Tên đăng nhập:<br/>
                <input type="text" name="username" value="" size="18"/><br/>
                <span class="bull">&bull;</span>Mật khẩu:<br/>
                <input type="password" name="password" value="" size="18"/><br/>
                <input type="submit" name="submit" value="Đăng nhập"/>
            </form>
        </div>';

        include_once 'footer.php';
    }

?>