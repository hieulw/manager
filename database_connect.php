<?php

    if (!defined('ACCESS') || !defined('PHPMYADMIN') || !defined('REALPATH') || !defined('PATH_DATABASE'))
        die('Not access');

    define('PATH_JSON', REALPATH . '/json');
    define('PATH_MYSQL_COLLECTION', PATH_JSON . '/mysql_collection.json');
    define('PATH_MYSQL_ATTRIBUTES', PATH_JSON . '/mysql_attributes.json');
    define('PATH_MYSQL_FIELD_KEY', PATH_JSON . '/mysql_field_key.json');
    define('PATH_MYSQL_DATA_TYPE', PATH_JSON . '/mysql_data_type.json');
    define('PATH_MYSQL_ENGINE_STORAGE', PATH_JSON . '/mysql_engine_storage.json');

    define('MYSQL_DATA_TYPE_NONE', 'none');
    define('MYSQL_COLLECTION_NONE', 'none');
    define('MYSQL_COLLECTION_SPLIT', '-@-');
    define('MYSQL_ATTRIBUTES_NONE', 'none');
    define('MYSQL_FIELD_KEY_NONE', 'none');

    define('MYSQL_AFTER_POSITION', 'after');
    define('MYSQL_AFTER_FIRST', 'first');
    define('MYSQL_AFTER_LAST', 'last');
    define('MYSQL_AFTER_SPLIT', '-@-');

    $MYSQL_COLLECTION = array();
    $MYSQL_ATTRIBUTES = array();
    $MYSQL_FIELD_KEY = array();
    $MYSQL_DATA_TYPE = array();
    $MYSQL_ENGINE_STORAGE = array();

    if (@is_file(REALPATH . '/' . PATH_DATABASE)) {
        include PATH_DATABASE;

        if (isDatabaseVariable($databases)) {
            define('IS_VALIDATE', true);
            define('IS_DATABASE_ROOT', empty($databases['db_name']) || $databases['db_name'] == null);
            define('LINK_IDENTIFIER', @mysql_connect($databases['db_host'], $databases['db_username'], $databases['db_password']));

            if (LINK_IDENTIFIER != false) {
                define('ERROR_CONNECT', false);

                function printDataType($default = null)
                {
                    global $MYSQL_DATA_TYPE;

                    if (@is_file(PATH_MYSQL_DATA_TYPE) && count($MYSQL_DATA_TYPE) <= 0) {
                        $json = jsonDecode(@file_get_contents(PATH_MYSQL_DATA_TYPE), true);

                        if ($json != null && count($json) > 0)
                            $MYSQL_DATA_TYPE = $json;
                    }

                    $html = null;

                    if (is_array($MYSQL_DATA_TYPE) == false || count($MYSQL_DATA_TYPE) <= 0) {
                        $html .= '<option value="' . MYSQL_DATA_TYPE_NONE . '">Không có lựa chọn</option>';
                    } else if (is_array($MYSQL_DATA_TYPE) && count($MYSQL_DATA_TYPE) > 0) {
                        foreach ($MYSQL_DATA_TYPE['data'] AS $label => $type) {
                            $html .= '<optgroup label="' . $label . '">';

                            foreach ($type AS $entry)
                                $html .= '<option value="' . $entry . '"' . (($default != null && $default == $entry) || ($default == null && $MYSQL_DATA_TYPE['default'] != null && $MYSQL_DATA_TYPE['default'] == $entry) ? ' selected="selected"' : null) . '>' . $entry . '</option>';

                            $html .= '</optgroup>';
                        }
                    }

                    return $html;
                }

                function printCollection($default = null)
                {
                    global $MYSQL_COLLECTION;

                    if (@is_file(PATH_MYSQL_COLLECTION) && count($MYSQL_COLLECTION) <= 0) {
                        $json = jsonDecode(@file_get_contents(PATH_MYSQL_COLLECTION), true);

                        if ($json != null && count($json) > 0)
                            $MYSQL_COLLECTION = $json;
                    }

                    $html = null;

                    if (is_array($MYSQL_COLLECTION) == false || count($MYSQL_COLLECTION) <= 0) {
                        $html .= '<option value="' . MYSQL_COLLECTION_NONE . '">Không có lựa chọn</option>';
                    } else if (is_array($MYSQL_COLLECTION) && count($MYSQL_COLLECTION) > 0) {
                        $html .= '<option value="' . MYSQL_COLLECTION_NONE . '"' . (($default != null && $default == MYSQL_COLLECTION_NONE) || ($default == null && $MYSQL_COLLECTION['default'] != null && $MYSQL_COLLECTION['default'] == MYSQL_COLLECTION_NONE) ? ' selected="selected"' : null) . '></option>';

                        foreach ($MYSQL_COLLECTION['data'] AS $charset => $collection) {
                            $html .= '<optgroup label="' . $charset . '">';

                            foreach ($collection AS $entry)
                                $html .= '<option value="' . $charset . MYSQL_COLLECTION_SPLIT . $entry . '"' . (($default != null && $default == $entry) || ($default == null && $MYSQL_COLLECTION['default'] != null && $MYSQL_COLLECTION['default'] == $entry) ? ' selected="selected"' : null) . '>' . $entry . '</option>';

                            $html .= '</optgroup>';
                        }
                    }

                    return $html;
                }

                function printAttributes($default = null)
                {
                    global $MYSQL_ATTRIBUTES;

                    if (@is_file(PATH_MYSQL_ATTRIBUTES) && count($MYSQL_ATTRIBUTES) <= 0) {
                        $json = jsonDecode(@file_get_contents(PATH_MYSQL_ATTRIBUTES), true);

                        if ($json != null && count($json) > 0)
                            $MYSQL_ATTRIBUTES = $json;
                    }

                    $html = null;

                    if (is_array($MYSQL_ATTRIBUTES) == false || count($MYSQL_ATTRIBUTES) <= 0) {
                        $html .= '<option value="' . MYSQL_ATTRIBUTES_NONE . '">Không có lựa chọn</option>';
                    } else if (is_array($MYSQL_ATTRIBUTES) && count($MYSQL_ATTRIBUTES) > 0) {
                        $html .= '<option value="' . MYSQL_ATTRIBUTES_NONE . '"' . (($default != null && $default == MYSQL_ATTRIBUTES_NONE) || ($default == null && $MYSQL_ATTRIBUTES['default'] != null && $MYSQL_ATTRIBUTES['default'] == MYSQL_ATTRIBUTES_NONE) ? ' selected="selected"' : null) . '></option>';

                        foreach ($MYSQL_ATTRIBUTES['data'] AS $key => $attr)
                            $html .= '<option value="' . $key . '"' . (($default != null && $default == $key) || ($default == null && $MYSQL_ATTRIBUTES['default'] != null && $MYSQL_ATTRIBUTES['default'] == $key) ? ' selected="selected"' : null) . '>' . $attr . '</option>';
                    }

                    return $html;
                }

                function printFieldKey($name, $default = null)
                {
                    global $MYSQL_FIELD_KEY;

                    if (@is_file(PATH_MYSQL_FIELD_KEY) && count($MYSQL_FIELD_KEY) <= 0) {
                        $json = jsonDecode(@file_get_contents(PATH_MYSQL_FIELD_KEY), true);

                        if ($json != null && count($json) > 0)
                            $MYSQL_FIELD_KEY = $json;
                    }

                    $html = null;

                    if (is_array($MYSQL_FIELD_KEY) == false || count($MYSQL_FIELD_KEY) <= 0) {
                        $html .= '<input type="radio" name="' . $name . '" value="' . MYSQL_FIELD_KEY_NONE . '" checked="checked">Không có lựa chọn</option>';
                    } else if (is_array($MYSQL_FIELD_KEY) && count($MYSQL_FIELD_KEY) > 0) {
                        $html .= '<input type="radio" name="' . $name . '" value="' . MYSQL_FIELD_KEY_NONE . '"' . (($default != null && $default == MYSQL_FIELD_KEY_NONE) || ($default == null && $MYSQL_FIELD_KEY['default'] != null && $MYSQL_FIELD_KEY['default'] == MYSQL_FIELD_KEY_NONE) ? ' checked="checked"' : null) . '/>Trống';

                        foreach ($MYSQL_FIELD_KEY['data'] AS $key => $value)
                            $html .= '<br/><input type="radio" name="' . $name . '" value="' . $key . '"' . (($default != null && $default == $key) || ($default == null && $MYSQL_FIELD_KEY['default'] != null && $MYSQL_FIELD_KEY['default'] == $key) ? ' checked="checked"' : null) . '/>' . $value;
                    }

                    return $html;
                }

                function printEngineStorage($default = null)
                {
                    global $MYSQL_ENGINE_STORAGE;

                    if (@is_file(PATH_MYSQL_ENGINE_STORAGE) && count($MYSQL_ENGINE_STORAGE) <= 0) {
                        $json = jsonDecode(@file_get_contents(PATH_MYSQL_ENGINE_STORAGE), true);

                        if ($json != null && count($json) > 0)
                            $MYSQL_ENGINE_STORAGE = $json;
                    }

                    $html = null;

                    if (is_array($MYSQL_ENGINE_STORAGE) == false || count($MYSQL_ENGINE_STORAGE) <= 0) {
                        $html .= '<option value="' . MYSQL_ENGINE_STORAGE_NONE . '">Không có lựa chọn</option>';
                    } else if (is_array($MYSQL_ENGINE_STORAGE) && count($MYSQL_ENGINE_STORAGE) > 0) {
                        foreach ($MYSQL_ENGINE_STORAGE['data'] AS $engine)
                            $html .= '<option value="' . $engine . '"' . (($default != null && $default == $engine) || ($default == null && $MYSQL_ENGINE_STORAGE['default'] != null && $MYSQL_ENGINE_STORAGE['default'] == $engine) ? ' selected="selected"' : null) . '>' . $engine . '</option>';
                    }

                    return $html;
                }

                function isDatabaseExists($name, $igone = null, $isLowerCase = false, &$output = false)
                {
                    if ($isLowerCase) {
                        $name = strtolower($name);

                        if ($igone != null)
                            $igone = strtolower($igone);
                    }

                    $query = @mysql_query('SHOW DATABASES', LINK_IDENTIFIER);

                    if (is_resource($query)) {
                        while ($assoc = @mysql_fetch_assoc($query)) {
                            $db = $isLowerCase ? strtolower($assoc['Database']) : $assoc['Database'];

                            if ($name == $db) {
                                if ($assoc != false)
                                    $output = $assoc;

                                if ($igone == null || $igone != $db)
                                    return true;
                            }
                        }
                    }

                    return false;
                }

                function isTableExists($name, $igone = null, $isLowerCase = false, &$output = false)
                {
                    if ($isLowerCase) {
                        $name = strtolower($name);

                        if ($igone != null)
                            $igone = strtolower($igone);
                    }

                    $query = @mysql_query('SHOW TABLE STATUS', LINK_IDENTIFIER);

                    if (is_resource($query)) {
                        while ($assoc = @mysql_fetch_assoc($query)) {
                            $table = $isLowerCase ? strtolower($assoc['Name']) : $assoc['Name'];

                            if ($name == $table) {
                                if ($assoc != false)
                                    $output = $assoc;

                                if ($igone == null || $igone != $table)
                                    return true;
                            }
                        }
                    }

                    return false;
                }

                function isColumnsExists($name, $table, $igone = null, $isLowerCase = false, &$output = false)
                {
                    if ($isLowerCase) {
                        $name = strtolower($name);

                        if ($igone != null)
                            $igone = strtolower($igone);
                    }

                    $query = @mysql_query("SHOW COLUMNS FROM `$table`", LINK_IDENTIFIER);

                    if (is_resource($query)) {
                        while ($assoc = @mysql_fetch_assoc($query)) {
                            $field = $isLowerCase ? strtolower($assoc['Field']) : $assoc['Field'];

                            if ($name == $field) {
                                if ($assoc != false)
                                    $output = $assoc;

                                if ($igone == null || $igone != $field)
                                    return true;
                            }
                        }
                    }

                    return false;
                }

                function isDataTypeHasLength($type)
                {
                    return !preg_match('/^(DATE|DATETIME|TIME|TINYBLOB|TINYTEXT|BLOB|TEXT|MEDIUMBLOB|MEDIUMTEXT|LONGBLOB|LONGTEXT|SERIAL|BOOLEAN|UUID)$/i', $type);
                }

                function isDataTypeNumeric($type)
                {
                    global $MYSQL_DATA_TYPE;

                    if (@is_file(PATH_MYSQL_DATA_TYPE) && count($MYSQL_DATA_TYPE) <= 0) {
                        $json = jsonDecode(@file_get_contents(PATH_MYSQL_DATA_TYPE), true);

                        if ($json != null && count($json) > 0)
                            $MYSQL_DATA_TYPE = $json;
                    }

                    if ($MYSQL_DATA_TYPE != null && is_array($MYSQL_DATA_TYPE))
                        return in_array(strtoupper($type), $MYSQL_DATA_TYPE['data']['Numeric']);
                    else
                        return false;
                }

                function getColumnsKey($table)
                {
                    $query = @mysql_query("SHOW INDEXES FROM `$table` WHERE `Key_name`='PRIMARY'");
                    $key = null;

                    if (@mysql_num_rows($query) > 0) {
                        $key = @mysql_fetch_assoc($query);
                        $key = $key['Column_name'];
                    } else {
                        $query = @mysql_query("SHOW COLUMNS FROM `$table`");
                        $key = @mysql_fetch_assoc($query);
                        $key = $key['Field'];
                    }

                    return $key;
                }

                if (empty($databases['db_name']) || $databases['db_name'] == null) {
                    if (isset($_GET['db_name']) == false || empty($_GET['db_name']) == true) {
                        define('IS_CONNECT', true);
                        define('ERROR_SELECT_DB', false);
                    } else if (isset($_GET['db_name']) && empty($_GET['db_name']) == false && @mysql_select_db($_GET['db_name'], LINK_IDENTIFIER)) {
                        define('IS_CONNECT', true);
                        define('ERROR_SELECT_DB', false);
                        define('DATABASE_NAME', $_GET['db_name']);
                    }
                } else if (empty($databases['db_name']) == false && $databases['db_name'] != null && @mysql_select_db($databases['db_name'], LINK_IDENTIFIER)) {
                    define('IS_CONNECT', true);
                    define('ERROR_SELECT_DB', false);
                    define('DATABASE_NAME', $databases['db_name']);
                }
            }
        }
    }

    if (!defined('IS_CONNECT'))
        define('IS_CONNECT', false);

    if (!defined('IS_VALIDATE'))
        define('IS_VALIDATE', false);

    if (!defined('IS_DATABASE_ROOT'))
        define('IS_DATABASE_ROOT', false);

    if (!defined('ERROR_CONNECT'))
        define('ERROR_CONNECT', true);

    if (!defined('ERROR_SELECT_DB'))
        define('ERROR_SELECT_DB', true);

    if (!defined('DATABASE_NAME'))
        define('DATABASE_NAME', null);

    define('DATABASE_NAME_PARAMATER_0', IS_DATABASE_ROOT ? '?db_name=' . DATABASE_NAME : null);
    define('DATABASE_NAME_PARAMATER_1', IS_DATABASE_ROOT ? '&db_name=' . DATABASE_NAME : null);

?>