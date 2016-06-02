<?php define('ACCESS', true);

    include_once 'function.php';

    if (IS_LOGIN) {
        $title = 'Tải lên tập tin';

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
                $isEmpty = true;

                foreach ($_FILES['file']['name'] AS $entry) {
                    if (!empty($entry)) {
                        $isEmpty = false;
                        break;
                    }
                }

                if ($isEmpty) {
                    echo '<div class="notice_failure">Chưa chọn tập tin</div>';
                } else {
                    for ($i = 0; $i < count($_FILES['file']['name']); ++$i) {
                        if (!empty($_FILES['file']['name'][$i])) {
                            if ($_FILES['file']['error'] == UPLOAD_ERR_INI_SIZE) {
                                echo '<div class="notice_failure">Tập tin <strong class="file_name_upload">' . $_FILES['file']['name'][$i] . '</strong> vượt quá kích thước cho phép</div>';
                            } else {
                                if (copy($_FILES['file']['tmp_name'][$i], $dir . '/' . str_replace(array('_jar', '.jar1', '.jar2'), '.jar', $_FILES['file']['name'][$i])))
                                    echo '<div class="notice_succeed">Tải lên tập tin <strong class="file_name_upload">' . $_FILES['file']['name'][$i] . '</strong>, <span class="file_size_upload">' . size($_FILES['file']['size'][$i]) . '</span> thành công</div>';
                                else
                                    echo '<div class="notice_failure">Tải lên tập tin <strong class="file_name_upload">' . $_FILES['file']['name'][$i] . '</strong> thất bại</div>';
                            }
                        }
                    }
                }
            }

            echo '<div class="list">
                <span>' . printPath($dir, true) . '</span><hr/>
                <form action="upload.php?dir=' . $dirEncode . $pages['paramater_1'] . '" method="post" enctype="multipart/form-data">
                    <span class="bull">&bull;</span>Tập tin 1:<br/>
                    <input type="file" name="file[]" size="18"/><br/>
                    <span class="bull">&bull;</span>Tập tin 2:<br/>
                    <input type="file" name="file[]" size="18"/><br/>
                    <span class="bull">&bull;</span>Tập tin 3:<br/>
                    <input type="file" name="file[]" size="18"/><br/>
                    <span class="bull">&bull;</span>Tập tin 4:<br/>
                    <input type="file" name="file[]" size="18"/><br/>
                    <span class="bull">&bull;</span>Tập tin 5:<br/>
                    <input type="file" name="file[]" size="18"/><br/>
                    <input type="submit" name="submit" value="Tải lên"/>
                </form>
            </div>
            <div class="title">Chức năng</div>
            <ul class="list">
                <li><img src="icon/create.png"/> <a href="create.php?dir=' . $dirEncode . $pages['paramater_1'] . '">Tạo mới</a></li>
                <li><img src="icon/import.png"/> <a href="import.php?dir=' . $dirEncode . $pages['paramater_1'] . '">Nhập khẩu tập tin</a></li>
                <li><img src="icon/list.png"/> <a href="index.php?dir=' . $dirEncode . $pages['paramater_1'] . '">Danh sách</a></li>
            </ul>';
        }

        include_once 'footer.php';
    } else {
        goURL('login.php');
    }

?>