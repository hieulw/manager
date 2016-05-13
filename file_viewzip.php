<?php define('ACCESS', true);

    include_once 'function.php';

    if (IS_LOGIN) {
        $title = 'Xem tập tin nén';
        $format = getFormat($name);

        if ($dir == null || $name == null || !is_file(processDirectory($dir . '/' . $name))) {
            include_once 'header.php';

            echo '<div class="title">' . $title . '</div>
            <div class="list"><span>Đường dẫn không tồn tại</span></div>
            <div class="title">Chức năng</div>
            <ul class="list">
                <li><img src="icon/list.png"/> <a href="index.php' . $pages['paramater_0'] . '">Danh sách</a></li>
            </ul>';
        } else if (!in_array($format, array('zip', 'jar'))) {
            include_once 'header.php';

            echo '<div class="title">' . $title . '</div>
            <div class="list"><span>Tập tin không phải zip</span></div>
            <div class="title">Chức năng</div>
            <ul class="list">
                <li><img src="icon/list.png"/> <a href="index.php?dir=' . $dirEncode . $pages['paramater_1'] . '">Danh sách</a></li>
            </ul>';
        } else {
            $title .= ':' . $name;

            include_once 'header.php';
            include_once 'pclzip.class.php';

            $path = isset($_GET['path']) && !empty($_GET['path']) ? processPathZip($_GET['path']) : null;
            $dir = processDirectory($dir);
            $format = getFormat($name);
            $zip = new PclZIP($dir . '/' . $name);
            $lists = $zip->listContent();
            $arrays = array('folders' => array(), 'files' => array());

            if (!$lists) {
                echo '<div class="title">' . $title . '</div>
                <div class="list">
                    <span>' . printPath($dir . '/' . $name) . '</span><hr/>
                    <span>Tập tin nén bị lỗi không mở được</span>
                </div>';
            } else {
                $base = $path == null || empty($path) ? null : $path . '/';

                foreach ($lists AS $entry) {
                    $filename = $entry['filename'];

                    if (strpos($filename, '/') === false && $base == null) {
                        $arrays['files'][$filename] = array('path' => $filename, 'name' => $filename, 'folder' => false, 'size' => $entry['size']);
                    } else if (preg_match('#(' . $base . '(.+?))(/|$)+#', $filename, $matches)) {
                        if ($matches[3] == '/' && !isset($arrays['folders'][$matches[2]]))
                            $arrays['folders'][$matches[2]] = array('path' => $matches[1], 'name' => $matches[2], 'folder' => true);
                        else if ($matches[3] != '/' && !$entry['folder'])
                            $arrays['files'][$matches[2]] = array('path' => $matches[1], 'name' => $matches[2], 'folder' => false, 'size' => $entry['size']);
                    }
                }

                $sorts = array();

                if (count($arrays['folders']) > 0) {
                    ksort($arrays['folders']);

                    foreach ($arrays['folders'] AS $entry)
                        $sorts[] = $entry;
                }

                if (count($arrays['files']) > 0) {
                    ksort($arrays['files']);

                    foreach ($arrays['files'] AS $entry)
                        $sorts[] = $entry;
                }

                array_splice($arrays, 0, count($arrays));

                $arrays = $sorts;
                $count = count($arrays);
                $root = 'root';
                $html = null;

                array_splice($sorts, 0, count($sorts));
                unset($sorts);

                if ($path != null && strpos($path, '/') !== false) {
                    $array = explode('/', preg_replace('|^/(.*?)$|', '\1', $path));
                    $html = '/<a href="file_viewzip.php?dir=' . $dirEncode . '&name=' . $name . $pages['paramater_1'] . '">' . $root . '</a>';
                    $item = null;
                    $url = null;

                    foreach ($array AS $key => $entry) {
                        if ($key === 0) {
                            $seperator = preg_match('|^\/(.*?)$|', $path) ? '/' : null;
                            $item = $seperator . $entry;
                        } else {
                            $item = '/' . $entry;
                        }

                        if ($key < count($array) - 1)
                            $html .= '/<a href="file_viewzip.php?dir=' . $dirEncode . '&name=' . $name . '&path=' . rawurlencode($url . $item) . $pages['paramater_1'] . '">';
                        else
                            $html .= '/';

                        $url .= $item;

                        if (strlen($entry) <= 8)
                            $html .= $entry;
                        else
                            $html .= substr($entry, 0, 8) . '...';

                        if ($key < count($array) - 1)
                            $html .= '</a>';
                    }
                } else {
                    if ($path == null)
                        $html = '/' . $root;
                    else
                        $html = '/<a href="file_viewzip.php?dir=' . $dirEncode . '&name=' . $name . $pages['paramater_1'] . '">' . $root . '</a>/' . $path;
                }

                echo '<script language="javascript" src="checkbox.js"></script>';
                echo '<div class="title">' . $html . '</div>';
                echo '<ul class="list_file">';
                echo '<li class="normal">
                    <span>' . printPath($dir . '/' . $name) . '</span>
                </li>';

                if ($path != null) {
                    $back = strrchr($path, '/');

                    if ($back !== false)
                        $back = 'file_viewzip.php?dir=' . $dirEncode . '&name=' . $name . '&path=' . rawurlencode(substr($path, 0, strlen($path) - strlen($back))) . $pages['paramater_1'];
                    else
                        $back = 'file_viewzip.php?dir=' . $dirEncode . '&name=' . $name . $pages['paramater_1'];

                    echo '<li class="normal">
                        <img src="icon/back.png" style="margin-left: 5px; margin-right: 5px"/> 
                        <a href="' . $back . '">
                            <strong class="back">...</strong>
                        </a>
                    </li>';
                }

                if ($count <= 0) {
                    echo '<li class="normal"><img src="icon/empty.png"/> <span class="empty">Không có thư mục hoặc tập tin</span></li>';
                } else {
                    foreach ($arrays AS $key => $value) {
                        $pathEncode = rawurlencode($value['path']);

                        if ($value['folder']) {
                            echo '<li class="folder">
                                <div>
                                    <img src="icon/folder.png" style="margin-left: 5px"/>
                                    <a href="file_viewzip.php?dir=' . $dirEncode . '&name=' . $name . '&path=' . $pathEncode . $pages['paramater_1'] . '">' . $value['name'] . '</a>
                                </div>
                            </li>';
                        } else {
                            $icon = 'unknown';
                            $type = getFormat($value['name']);

                            if (in_array($type, $formats['other']))
                                $icon = $type;
                            else if (in_array($type, $formats['text']))
                                $icon = $type;
                            else if (in_array($type, $formats['archive']))
                                $icon = $type;
                            else if (in_array($type, $formats['audio']))
                                $icon = $type;
                            else if (in_array($type, $formats['font']))
                                $icon = $type;
                            else if (in_array($type, $formats['binary']))
                                $icon = $type;
                            else if (in_array($type, $formats['document']))
                                $icon = $type;
                            else if (in_array($type, $formats['image']))
                                $icon = 'image';
                            else if (in_array(strtolower(strpos($name, '.') !== false ? substr($name, 0, strpos($name, '.')) : $name), $formats['source']))
                                $icon = strtolower(strpos($name, '.') !== false ? substr($name, 0, strpos($name, '.')) : $name);

                            echo '<li class="file">
                                <p>
                                    <img src="icon/mime/' . $icon . '.png" style="margin-left: 5px"/>
                                    <span>' . $value['name'] . '</span>
                                </p>
                                <p>
                                    <span class="size">' . size($value['size']) . '</span>
                                </p>
                            </li>';
                        }
                    }
                }

                echo '</ul>';
            }

            echo '<div class="title">Chức năng</div>
            <ul class="list">
                <li><img src="icon/info.png"/> <a href="file.php?dir=' . $dirEncode . '&name=' . $name . $pages['paramater_1'] . '">Thông tin</a></li>
                <li><img src="icon/unzip.png"/> <a href="file_unzip.php?dir=' . $dirEncode . '&name=' . $name . $pages['paramater_1'] . '">Giải nén</a></li>
                <li><img src="icon/download.png"/> <a href="file_download.php?dir=' . $dirEncode . '&name=' . $name . $pages['paramater_1'] . '">Tải về</a></li>
                <li><img src="icon/rename.png"/> <a href="file_rename.php?dir=' . $dirEncode . '&name=' . $name . $pages['paramater_1'] . '">Đổi tên</a></li>
                <li><img src="icon/copy.png"/> <a href="file_copy.php?dir=' . $dirEncode . '&name=' . $name . $pages['paramater_1'] . '">Sao chép</a></li>
                <li><img src="icon/move.png"/> <a href="file_move.php?dir=' . $dirEncode . '&name=' . $name . $pages['paramater_1'] . '">Di chuyển</a></li>
                <li><img src="icon/delete.png"/> <a href="file_delete.php?dir=' . $dirEncode . '&name=' . $name . $pages['paramater_1'] . '">Xóa</a></li>
                <li><img src="icon/access.png"/> <a href="file_chmod.php?dir=' . $dirEncode . '&name=' . $name . $pages['paramater_1'] . '">Chmod</a></li>
                <li><img src="icon/list.png"/> <a href="index.php?dir=' . $dirEncode . $pages['paramater_1'] . '">Danh sách</a></li>
            </ul>';
        }

        include_once 'footer.php';
    } else {
        goURL('login.php');
    }

?>