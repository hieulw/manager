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

                foreach ($_POST['url'] AS $entry) {
                    if (!empty($entry)) {
                        $isEmpty = false;
                        break;
                    }
                }

                if ($isEmpty) {
                    echo '<div class="notice_failure">Chưa nhập url nào cả</div>';
                } else {
                    for ($i = 0; $i < count($_POST['url']); ++$i) {
                        if (!empty($_POST['url'][$i])) {
                            $_POST['url'][$i] = processImport($_POST['url'][$i]);

                            if (!isURL($_POST['url'][$i]))
                                echo '<div class="notice_failure">URL <strong class="url_import">' . $_POST['url'][$i] . '</strong> không hợp lệ</div>';
                            else if (import($_POST['url'][$i], $dir . '/' . basename($_POST['url'][$i])))
                                echo '<div class="notice_succeed">Nhập khẩu tập tin <strong class="file_name_import">' . basename($_POST['url'][$i]) . '</strong>, <span class="file_size_import">' . size(filesize($dir . '/' . basename($_POST['url'][$i]))) . '</span> thành công</div>';
                            else
                                echo '<div class="notice_failure">Nhập khẩu tập tin <strong class="file_name_import">' . basename($_POST['url'][$i]) . '</strong> thất bại</div>';
                        }
                    }
                }
            }

            echo '<div class="list">
                <span>' . printPath($dir, true) . '</span><hr/>
                <form action="import.php?dir=' . $dirEncode . $pages['paramater_1'] . '" method="post">
                    <span class="bull">&bull;</span>URL 1:<br/>
                    <input type="text" name="url[]" size="18"/><br/>
                    <span class="bull">&bull;</span>URL:<br/>
                    <input type="text" name="url[]" size="18"/><br/>
                    <span class="bull">&bull;</span>URL 3:<br/>
                    <input type="text" name="url[]" size="18"/><br/>
                    <span class="bull">&bull;</span>URL 4:<br/>
                    <input type="text" name="url[]" size="18"/><br/>
                    <span class="bull">&bull;</span>URL 5:<br/>
                    <input type="text" name="url[]" size="18"/><br/>
                    <input type="submit" name="submit" value="Nhập khẩu"/>
                </form>
            </div>

            <div class="tips"><img src="icon/tips.png"/> Không có http:// đứng trước cũng được, nếu có https:// phải nhập vào</div>

            <div class="title">Chức năng</div>
            <ul class="list">
                <li><img src="icon/create.png"/> <a href="create.php?dir=' . $dirEncode . $pages['paramater_1'] . '">Tạo mới</a></li>
                <li><img src="icon/upload.png"/> <a href="upload.php?dir=' . $dirEncode . $pages['paramater_1'] . '">Tải lên tập tin</a></li>
                <li><img src="icon/list.png"/> <a href="index.php?dir=' . $dirEncode . $pages['paramater_1'] . '">Danh sách</a></li>
            </ul>';
        }

        include_once 'footer.php';
    } else {
        goURL('login.php');
    }

?>