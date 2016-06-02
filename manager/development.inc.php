<?php

    if (!defined('ACCESS') || !defined('DEVELOPMENT'))
        die('Not access');

    define('DEVELOPMENT_FILE', 'development.count');
    define('DEVELOPMENT_INC', 'development.inc.php');
    define('VERSION_INC', 'version.inc.php');

    $files = array();
    $times = array();
    $count = 1;
    $version = '0.0.1';
    $isCreator = true;
    $isModifier = false;

    if (DEVELOPMENT) {
        $handler = @scandir(REALPATH);

        foreach ($handler AS $entry) {
            if ($entry != '.' &&
                $entry != '..' &&
                $entry != basename(PATH_CONFIG) &&
                $entry != basename(PATH_DATABASE) &&
                $entry != basename(DEVELOPMENT_FILE) &&
                $entry != basename(DEVELOPMENT_INC) &&
                $entry != basename(VERSION_INC) && is_file(REALPATH . '/' . $entry))
            {
                $files[] = $entry;
                $times[] = filemtime(REALPATH . '/' . $entry);
            }
        }

        unset($handler);

        if (is_file(REALPATH . '/' . DEVELOPMENT_FILE)) {
            $json = jsonDecode(file_get_contents(DEVELOPMENT_FILE), true);

            if ($json !== null) {
                $entryFiles = $json['files'];
                $entryTimes = $json['times'];
                $count = intval($json['count']);
                $version = $json['version'];
                $isCreator = false;

                if (count($files) != count($entryFiles) || count($times) != count($entryTimes)) {
                    $isModifier = true;
                } else {
                    for ($i = 0; $i < count($entryFiles); ++$i) {
                        $file = $entryFiles[$i];
                        $time = intval($entryTimes[$i]);

                        if (!in_array($file, $files) || intval($times[array_search($file, $files)]) > intval($time)) {
                            $isModifier = true;
                            break;
                        }
                    }
                }

                if ($isModifier) {
                    $count += 1;
                    $length = strlen($count);
                    $version = null;
                    $isCreator = true;

                    if ($length > 4)
                        $version = intval(substr($count, 0, $length - 4));
                    else
                        $version = 0;

                    if ($length > 2)
                        $version .= '.' . intval(substr($count, $length == 3 ? 0 : $length - 4, $length > 3 ? 2 : 1));
                    else
                        $version .= '.' . 0;

                    $version .= '.' . intval(substr($count, $length == 1 ? 0 : $length - 2, 2));
                } else if (!is_file(VERSION_INC)) {
                    $isModifier = true;
                }
            }
        } else if (is_file(VERSION_INC)) {
            require_once VERSION_INC;
        }

        if ($isCreator)
            file_put_contents(REALPATH . '/' . DEVELOPMENT_FILE, jsonEncode(array('files' => $files, 'times' => $times, 'count' => $count, 'version' => $version)));

        if ($isCreator || $isModifier)
            file_put_contents(REALPATH . '/' . VERSION_INC, '<?php if (!defined(\'ACCESS\')) { die(\'Not acces\'); } else { $count = ' . $count . '; $version = \'' . $version . '\'; } ?>');
    } else if (is_file(VERSION_INC)) {
        require_once VERSION_INC;
    }

    if (!DEVELOPMENT && is_file(REALPATH . '/' . DEVELOPMENT_FILE))
        @unlink(REALPATH . '/' . DEVELOPMENT_FILE);

    define('AUTHOR', 'Izero');
    define('VERSION', $version);

    unset($files);
    unset($times);
    unset($count);
    unset($version);

?>