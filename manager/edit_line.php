<?php define('ACCESS', true);

    include_once 'function.php';

    if (IS_LOGIN) {
        $title = 'Sửa dòng';
        $page = array('current' => 0, 'paramater_0' => null, 'paramater_1' => null);
        $page['current'] = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $page['current'] = $page['current'] <= 0 ? 1 : $page['current'];

        include_once 'header.php';

        echo '<div class="title">' . $title . '</div>';

        if ($dir == null || $name == null || !is_file(processDirectory($dir . '/' . $name))) {
            echo '<div class="list"><span>Đường dẫn không tồn tại</span></div>
            <div class="title">Chức năng</div>
            <ul class="list">
                <li><img src="icon/list.png"/> <a href="index.php' . $pages['paramater_0'] . '">Danh sách</a></li>
            </ul>';
        } else if (!isFormatText($name) && !isFormatUnknown($name)) {
            echo '<div class="list"><span>Tập tin này không phải dạng văn bản</span></div>
            <div class="title">Chức năng</div>
            <ul class="list">
                <li><img src="icon/list.png"/> <a href="index.php?dir=' . $dirEncode . $pages['paramater_1'] . '">Danh sách</a></li>
            </ul>';
        } else {
            function process()
            {
                global $content, $lines, $count, $path;

                $content = file_get_contents($path);

                if (strlen($content) > 0) {
                    $content = str_replace("\r\n", "\n", $content);
                    $content = str_replace("\r", "\n", $content);

                    if (strpos($content, "\n") !== false)
                        $lines = explode("\n", $content);
                    else
                        $lines[] = $content;
                } else {
                    $lines[] = $content;
                }

                $count = count($lines);
            }

            $path = $dir . '/' . $name;
            $line = isset($_GET['line']) ? intval($_GET['line']) : 0;
            $lines = array();
            $content = null;
            $notice = null;
            $count = 0;

            if ($page['current'] > 1) {
                $page['paramater_0'] = '?page=' . $page['current'];
                $page['paramater_1'] = '&page=' . $page['current'];
            }

            process();

            if (isset($_POST['continue']) || isset($_POST['save'])) {
                $data = null;
                $con = stripslashes($_POST['content']);

                if ($con != null && !empty($con)) {
                    $con = str_replace("\r\n", "\n", $con);
                    $con = str_replace("\r", "\n", $con);
                }

                if ($count > 1) {
                    if ($line > 0) {
                        for ($i = 0; $i < $line; ++$i)
                            $data .= $lines[$i] . "\n";
                    }

                    $data .= $con;

                    if ($line < $count - 1) {
                        for ($i = ($line + 1); $i < $count; ++$i)
                            $data .= "\n" . $lines[$i];
                    }
                } else {
                    $data = $con;
                }

                if (file_put_contents($path, $data)) {
                    $notice = '<div class="notice_succeed">Lưu lại thành công</div>';

                    if (isset($_POST['save']))
                        goURL('edit_text_line.php?dir=' . $dirEncode . '&name=' . $name . $pages['paramater_1'] . $page['paramater_1'] . '#line_number_' . $line);
                } else {
                    $notice = '<div class="notice_failure">Lưu lại thất bại</div>';
                }

                process();
            }

            $isGO = false;

            if (isset($_POST['go']) && !empty($_POST['line']) && preg_match('#\\b[0-9]+\\b#', $_POST['line'])) {
                $li = intval($_POST['line']);

                if ($li >= 0 && $li <= $count - 1) {
                    $line = $li;
                    $isGO = true;
                }
            }

            if ($line < 0)
                goURL('edit_line.php?dir=' . $dirEncode . '&name=' . $name . $pages['paramater_1'] . '&line=0'  . $page['paramater_1']);

            if ($line > $count - 1)
                goURL('edit_line.php?dir=' . $dirEncode . '&name=' . $name . $pages['paramater_1'] . '&line='  . ($count - 1) . $page['paramater_1']);

            $page['current'] = $line + 1 > $configs['page_file_edit_line'] ? ceil(($line + 1) / $configs['page_file_edit_line']) : 1;

            if ($page['current'] > 1) {
                $page['paramater_0'] = '?page=' . $page['current'];
                $page['paramater_1'] = '&page=' . $page['current'];
            }

            if ($isGO)
                goURL('edit_line.php?dir=' . $dirEncode . '&name=' . $name . $pages['paramater_1'] . '&line=' . $line . $page['paramater_1']);

            $url = array('action' => null, 'prev' => null, 'next' => null);
            $url['action'] = 'edit_line.php?dir=' . $dirEncode . '&name=' . $name . $pages['paramater_1'] . '&line=' . $line . $page['paramater_1'] . '#line_label';
            $url['prev'] = $line > 0 ? '<a href="edit_line.php?dir=' . $dirEncode . '&name=' . $name . $pages['paramater_1'] . '&line=' . ($line - 1) . ($configs['page_file_edit_line'] > 0 && $line <= $configs['page_file_edit_line'] ? null : '&page=' . ceil($line / $configs['page_file_edit_line'])) . '#line_label"><img src="icon/arrow_left.png"/></a>' : '<img src="icon/arrow_left.png"/>';
            $url['next'] = $line < $count - 1 ? '<a href="edit_line.php?dir=' . $dirEncode . '&name=' . $name . $pages['paramater_1'] . '&line=' . ($line + 1) . ($configs['page_file_edit_line'] > 0 && $line <= $configs['page_file_edit_line'] ? null : '&page=' . ceil(($line + 2) / $configs['page_file_edit_line'])) . '#line_label"><img src="icon/arrow_right.png"/></a>' : '<img src="icon/arrow_right.png"/>';

            echo $notice;
            echo '<div class="list">
                <span class="bull">&bull;</span><span>' . printPath($dir, true) . '</span><hr/>
                <div class="ellipsis break-word">
                    <span class="bull">&bull;</span>Tập tin: <strong class="file_name_edit">' . $name . '</strong>
                </div><hr/>
                <form action="edit_line.php?dir=' . $dirEncode . '&name=' . $name . $pages['paramater_1'] . '&line=' . $line . $page['paramater_1'] . '#line_label" method="post">
                    <span class="bull" id="line_label">&bull;</span>Dòng [<strong class="line_number_form">' . $line . '</strong>/<strong class="line_number_form">' . ($count - 1) . '</strong>]:<br/>
                    <div class="parent_box_edit">
                        <textarea class="box_edit_normal" name="content" rows="10">' . htmlspecialchars($lines[$line]) . '</textarea>
                    </div>
                    <div style="margin-left: -4px">
                        <input type="submit" name="continue" value="Tiếp tục"/>
                        <input type="submit" name="save" value="Lưu"/>
                        <a href="delete_line.php?dir=' . $dirEncode . '&name=' . $name . $pages['paramater_1'] . '&line=' . $line . $page['paramater_1'] . '" id="href_line_edit">Xóa</a>
                    </div>
                </form><hr/>
                <form action="' . $url['action'] . '" method="post">
                    <table id="action_page">
                        <tr>
                            <td id="prev">' . $url['prev'] . '</td>
                            <td id="input">
                                <input type="text" name="line" value="' . $line . '"/>
                            </td>
                            <td id="submit">
                                <input type="submit" name="go" value="Đến"/>
                            </td>
                            <td id="next">' . $url['next'] . '</td>
                        </tr>
                    </table>
                </form>
            </div>
            <div class="tips">
                <img src="icon/tips.png"/>
                <span>Ấn tiếp tục để lưu lại ở lại trang và ấn lưu để lưu lại và quay về danh sách dòng</span>
            </div>
            <div class="title">Chức năng</div>
            <ul class="list">
                <li><img src="icon/delete.png"/> <a href="delete_line.php?dir=' . $dirEncode . '&name=' . $name . $pages['paramater_1'] . '&line=' . $line . $page['paramater_1'] . '">Xóa dòng</a></li>
                <li><img src="icon/edit_text_line.png"/> <a href="edit_text_line.php?dir=' . $dirEncode . '&name=' . $name . $pages['paramater_1'] . $page['paramater_1'] . '#line_number_' . $line . '">Sửa theo dòng</a></li>
                <li><img src="icon/edit.png"/> <a href="edit_text.php?dir=' . $dirEncode . '&name=' . $name . $pages['paramater_1'] . '">Sửa văn bản</a></li>
                <li><img src="icon/download.png"/> <a href="file_download.php?dir=' . $dirEncode . '&name=' . $name . $pages['paramater_1'] . '">Tải về</a></li>
                <li><img src="icon/info.png"/> <a href="file.php?dir=' . $dirEncode . '&name=' . $name . $pages['paramater_1'] . '">Thông tin</a></li>
                <li><img src="icon/rename.png"/> <a href="file_rename.php?dir=' . $dirEncode . '&name=' . $name . $pages['paramater_1'] . '">Đổi tên</a></li>
                <li><img src="icon/copy.png"/> <a href="file_copy.php?dir=' . $dirEncode . '&name=' . $name . $pages['paramater_1'] . '">Sao chép</a></li>
                <li><img src="icon/move.png"/> <a href="file_move.php?dir=' . $dirEncode . '&name=' . $name . $pages['paramater_1'] . '">Di chuyển</a></li>
                <li><img src="icon/delete.png"/> <a href="file_delete.php?dir=' . $dirEncode . '&name=' . $name . $pages['paramater_1'] . '">Xóa</a></li>
                <li><img src="icon/access.png"/> <a href="file_chmod.php?dir=' . $dirEncode . '&name=' . $name . $pages['paramater_1'] . '">Chmod</a></li>
                <li><img src="icon/list.png"/> <a href="index.php?dir=' . $dirEncode . $pages['paramater_1'] . '">Danh sách</a></li>
            </ul>';
        }
    } else {
        goURL('login.php');
    }

    include_once 'footer.php';

?>