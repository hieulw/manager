<?php define('ACCESS', true);

    include_once 'function.php';

    if (IS_LOGIN) {
        $title = 'Tạo mới';

        include_once 'header.php';

        echo '<div class="title">' . $title . '</div>';

        if ($dir == null || !is_dir(processDirectory($dir))) {
            echo '<div class="list"><span>Đường dẫn không tồn tại</span></div>
            <div class="title">Chức năng</div>
            <ul class="list">
                <li><img src="icon/list.png"/> <a href="index.php' . $pages['paramater_0'] . '">Danh sách</a></li>
            </ul>';
        } else {
            $dir = processDirectory($dir);

            if (isset($_POST['submit'])) {
                echo '<div class="notice_failure">';

                if (empty($_POST['name'])) {
                    echo 'Chưa nhập đầy đủ thông tin';
                } else if (intval($_POST['type']) === 0 && file_exists($dir . '/' . $_POST['name'])) {
                    echo 'Tên đã tồn tại dạng thư mục hoặc tập tin';
                } else if (intval($_POST['type']) === 1 && file_exists($dir . '/' . $_POST['name'])) {
                    echo 'Tên đã tồn tại dạng thư mục hoặc tập tin';
                } else if (isNameError($_POST['name'])) {
                    echo 'Tên không hợp lệ';
                } else {
                    if (intval($_POST['type']) === 0) {
                        if (!@mkdir($dir . '/' . $_POST['name']))
                            echo 'Tạo thư mục thất bại';
                        else
                            goURL('index.php?dir=' . $dirEncode . $pages['paramater_1']);
                    } else if (intval($_POST['type']) === 1) {
                        if (!@file_put_contents($dir . '/' . $_POST['name'], '...'))
                            echo 'Tạo tập tin thất bại';
                        else
                            goURL('index.php?dir=' . $dirEncode . $pages['paramater_1']);
                    } else {
                        echo 'Lựa chọn không hợp lệ';
                    }
                }

                echo '</div>';
            }

            echo '<div class="list">
                <span>' . printPath($dir, true) . '</span><hr/>
                <form action="create.php?dir=' . $dirEncode . $pages['paramater_1'] . '" method="post">
                    <span class="bull">&bull;</span>Tên thư mục hoặc tập tin:<br/>
                    <input type="text" name="name" value="' . (isset($_POST['name']) ? $_POST['name'] : null) . '" size="18"/><br/>
                    <input type="radio" name="type" value="0" checked="checked"/>Thư mục<br/>
                    <input type="radio" name="type" value="1"/>Tập tin<br/>
                    <input type="submit" name="submit" value="Tạo"/>
                </form>
            </div>
            <div class="title">Chức năng</div>
            <ul class="list">
                <li><img src="icon/upload.png"/> <a href="upload.php?dir=' . $dirEncode . $pages['paramater_1'] . '">Tải lên tập tin</a></li>
                <li><img src="icon/import.png"/> <a href="import.php?dir=' . $dirEncode . $pages['paramater_1'] . '">Nhập khẩu tập tin</a></li>
                <li><img src="icon/list.png"/> <a href="index.php?dir=' . $dirEncode . $pages['paramater_1'] . '">Danh sách</a></li>
            </ul>';
        }

        include_once 'footer.php';
    } else {
        goURL('login.php');
    }

?>