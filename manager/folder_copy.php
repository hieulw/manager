<?php define('ACCESS', true);

    include_once 'function.php';

    if (IS_LOGIN) {
        $title = 'Sao chép thư mục';

        include_once 'header.php';

        echo '<div class="title">' . $title . '</div>';

        if ($dir == null || $name == null || !is_dir(processDirectory($dir . '/' . $name))) {
            echo '<div class="list"><span>Đường dẫn không tồn tại</span></div>
            <div class="title">Chức năng</div>
            <ul class="list">
                <li><img src="icon/list.png"/> <a href="index.php' . $pages['paramater_0'] . '">Danh sách</a></li>
            </ul>';
        } else {
            $dir = processDirectory($dir);

            if (isset($_POST['submit'])) {
                echo '<div class="notice_failure">';

                if (empty($_POST['path']))
                    echo 'Chưa nhập đầy đủ thông tin';
                else if ($dir == processDirectory($_POST['path']))
                    echo 'Đường dẫn mới phải khác đường dẫn hiện tại';
                else if (!is_dir($_POST['path']))
                    echo 'Đường dẫn mới không tồn tại';
                else if (isPathNotPermission(processDirectory($_POST['path'])))
                    echo 'Bạn không thể sao chép thư mục tới đường dẫn của File Manager';
                else if (isPathNotPermission(processDirectory($_POST['path'] . '/' . $name)))
                    echo 'Bạn không thể sao chép thư mục giống tên tới thư mục chứa thư mục của File Manager';
                else if (!copydir($dir . '/' . $name, processDirectory($_POST['path'])))
                    echo 'Sao chép thư mục thất bại';
                else
                    goURL('index.php?dir=' . $dirEncode . $pages['paramater_1']);

                echo '</div>';
            }

            echo '<div class="list">
                <span class="bull">&bull;</span><span>' . printPath($dir . '/' . $name, true) . '</span><hr/>
                <form action="folder_copy.php?dir=' . $dirEncode . '&name=' . $name . $pages['paramater_1'] . '" method="post">
                    <span class="bull">&bull;</span>Đường dẫn thư mục mới:<br/>
                    <input type="text" name="path" value="' . (isset($_POST['path']) ? $_POST['path'] : $dir) . '" size="18"/><br/>
                    <input type="submit" name="submit" value="Sao chép"/>
                </form>
            </div>
            <div class="title">Chức năng</div>
            <ul class="list">
                <li><img src="icon/zip.png"/> <a href="folder_zip.php?dir=' . $dirEncode . '&name=' . $name . $pages['paramater_1'] . '">Nén zip</a></li>
                <li><img src="icon/rename.png"/> <a href="folder_edit.php?dir=' . $dirEncode . '&name=' . $name . $pages['paramater_1'] . '">Đổi tên</a></li>
                <li><img src="icon/move.png"/> <a href="folder_move.php?dir=' . $dirEncode . '&name=' . $name . $pages['paramater_1'] . '">Di chuyển</a></li>
                <li><img src="icon/delete.png"/> <a href="folder_delete.php?dir=' . $dirEncode . '&name=' . $name . $pages['paramater_1'] . '">Xóa</a></li>
                <li><img src="icon/access.png"/> <a href="folder_chmod.php?dir=' . $dirEncode . '&name=' . $name . $pages['paramater_1'] . '">Chmod</a></li>
                <li><img src="icon/list.png"/> <a href="index.php?dir=' . $dirEncode . $pages['paramater_1'] . '">Danh sách</a></li>
            </ul>';
        }

        include_once 'footer.php';
    } else {
        goURL('login.php');
    }

?>