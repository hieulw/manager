<?php define('ACCESS', true);

    include_once 'function.php';

    if (IS_LOGIN) {
        $title = 'Nén zip thư mục';

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

                if (empty($_POST['name']) || empty($_POST['path']))
                    echo 'Chưa nhập đầy đủ thông tin';
                else if (isset($_POST['is_delete']) && processDirectory($_POST['path']) == $dir . '/' . $name)
                    echo 'Nếu chọn xóa thư mục bạn không thể lưu tập tin nén ở đó';
                else if (isPathNotPermission(processDirectory($_POST['path'])))
                    echo 'Bạn không thể nén tập tin zip tới đường dẫn của File Manager';
                else if (isNameError($_POST['name']))
                    echo 'Tên tập tin zip không hợp lệ';
                else if (!zipdir($dir . '/' . $name, processDirectory($_POST['path'] . '/' . processName($_POST['name'])), isset($_POST['is_delete']) == 1))
                    echo 'Nén zip thư mục thất bại';
                else
                    goURL('index.php?dir=' . $dirEncode . $pages['paramater_1']);

                echo '</div>';
            }

            echo '<div class="list">
                <span class="bull">&bull;</span><span>' . printPath($dir . '/' . $name, true) . '</span><hr/>
                <form action="folder_zip.php?dir=' . $dirEncode . '&name=' . $name . $pages['paramater_1'] . '" method="post">
                    <span class="bull">&bull;</span>Tên tập tin nén:<br/>
                    <input type="text" name="name" value="' . (isset($_POST['name']) ? $_POST['name'] : $name . '.zip') . '" size="18"/><br/>
                    <span class="bull">&bull;</span>Đường dẫn lưu:<br/>
                    <input type="text" name="path" value="' . (isset($_POST['path']) ? $_POST['path'] : $dir) . '" size="18"/><br/>
                    <input type="checkbox" name="is_delete" value="1"/> Xóa thư mục<br/>
                    <input type="submit" name="submit" value="Nén"/>
                </form>
            </div>
            <div class="title">Chức năng</div>
            <ul class="list">
                <li><img src="icon/rename.png"/> <a href="folder_edit.php?dir=' . $dirEncode . '&name=' . $name . $pages['paramater_1'] . '">Đổi tên</a></li>
                <li><img src="icon/copy.png"/> <a href="folder_copy.php?dir=' . $dirEncode . '&name=' . $name . $pages['paramater_1'] . '">Sao chép</a></li>
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