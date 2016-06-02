<?php if (!defined('ACCESS')) die('Not access'); ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="vi">
    <head>
        <title><?php echo $title; ?> - File Manager <?php echo VERSION; ?></title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" type="text/css" href="style.css" media="all,handheld" />
        <link rel="icon" type="image/png" href="icon/icon.png">
        <link rel="icon" type="image/x-icon" href="icon/icon.ico" />
        <link rel="shortcut icon" type="image/x-icon" href="icon/icon.ico" />
    </head>
    <body>
        <div id="header">
            <ul>
                <li><a href="index.php"><img src="icon/home.png"/></a></li>
                <?php if (!IS_INSTALL_ROOT_DIRECTORY && isset($_SESSION[SESS])) { ?>
                    <?php if (!defined('IS_CONNECT')) { ?>
                        <li><a href="database.php"><img src="icon/database.png"/></a></li>
                    <?php } else { ?>
                        <li><a href="database_disconnect.php"><img src="icon/disconnect.png"/></a></li>
                    <?php } ?>
                    <li><a href="setting.php"><img src="icon/setting.png"/></a></li>
                    <li><a href="exit.php"><img src="icon/exit.png"/></a></li>
                <?php } ?>
            </ul>
        </div>
        <div id="container">
