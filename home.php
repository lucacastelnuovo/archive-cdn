<?php

require $_SERVER['DOCUMENT_ROOT'] . '/includes/init.php';

loggedin();

/********************************
Simple PHP File Manager
Copyright John Campbell (jcampbell1)

Liscense: MIT
********************************/

//Disable error report for undefined superglobals
error_reporting( error_reporting() & ~E_NOTICE );

$allow_delete = true;
$allow_upload = true;
$allow_create_folder = true;
$allow_direct_link = true;
$allow_show_folders = true;

// Blocked files
$disallowed_extensions = ['php'];
$hidden_extensions = ['php'];

setlocale(LC_ALL,'en_US.UTF-8');

$tmp_dir = dirname($_SERVER['SCRIPT_FILENAME']);
if(DIRECTORY_SEPARATOR==='\\') $tmp_dir = str_replace('/',DIRECTORY_SEPARATOR,$tmp_dir);
$tmp = get_absolute_path($tmp_dir . '/' .$_REQUEST['file']);

if($tmp === false)
    err(404,'File or Directory Not Found');
if(substr($tmp, 0,strlen($tmp_dir)) !== $tmp_dir)
    err(403,"Forbidden");
if(strpos($_REQUEST['file'], DIRECTORY_SEPARATOR) === 0)
    err(403,"Forbidden");


if(!$_COOKIE['_sfm_xsrf'])
    setcookie('_sfm_xsrf',bin2hex(openssl_random_pseudo_bytes(16)));
if($_POST) {
    if($_COOKIE['_sfm_xsrf'] !== $_POST['xsrf'] || !$_POST['xsrf'])
        err(403,"XSRF Failure");
}

$file = $_REQUEST['file'] ?: '.';
if($_GET['do'] == 'list') {
    if (is_dir($file)) {
    $directory = $file;
    $result = [];
    $files = array_diff(scandir($directory), ['.','..']);
    foreach ($files as $entry) if (!is_entry_ignored($entry, $allow_show_folders, $hidden_extensions)) {
    $i = $directory . '/' . $entry;
    $stat = stat($i);
            $result[] = [
                'mtime' => $stat['mtime'],
                'size' => $stat['size'],
                'name' => basename($i),
                'path' => preg_replace('@^\./@', '', $i),
                'is_dir' => is_dir($i),
                'is_deleteable' => $allow_delete && ((!is_dir($i) && is_writable($directory)) ||
                                                           (is_dir($i) && is_writable($directory) && is_recursively_deleteable($i))),
                'is_readable' => is_readable($i),
                'is_writable' => is_writable($i),
                'is_executable' => is_executable($i),
            ];
        }
    } else {
        err(412,"Not a Directory");
    }
    echo json_encode(['success' => true, 'is_writable' => is_writable($file), 'results' =>$result]);
    exit;
} elseif ($_POST['do'] == 'delete') {
    if($allow_delete) {
        rmrf($file);
    }
    exit;
} elseif ($_POST['do'] == 'mkdir' && $allow_create_folder) {
    // don't allow actions outside root. we also filter out slashes to catch args like './../outside'
    $dir = $_POST['name'];
    $dir = str_replace('/', '', $dir);
    if(substr($dir, 0, 2) === '..')
        exit;
    chdir($file);
    @mkdir($_POST['name']);
    exit;
} elseif ($_POST['do'] == 'upload' && $allow_upload) {
    foreach($disallowed_extensions as $ext)
        if(preg_match(sprintf('/\.%s$/',preg_quote($ext)), $_FILES['file_data']['name']))
            err(403,"Files of this type are not allowed.");

    $res = move_uploaded_file($_FILES['file_data']['tmp_name'], $file.'/'.$_FILES['file_data']['name']);
    exit;
} elseif ($_GET['do'] == 'download') {
    $filename = basename($file);
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    header('Content-Type: ' . finfo_file($finfo, $file));
    header('Content-Length: '. filesize($file));
    header(sprintf('Content-Disposition: attachment; filename=%s',
        strpos('MSIE',$_SERVER['HTTP_REFERER']) ? rawurlencode($filename) : "\"$filename\"" ));
    ob_flush();
    readfile($file);
    exit;
}

function is_entry_ignored($entry, $allow_show_folders, $hidden_extensions) {
    if ($entry === basename(__FILE__)) {
        return true;
    }

    if (is_dir($entry) && !$allow_show_folders) {
        return true;
    }

    $ext = strtolower(pathinfo($entry, PATHINFO_EXTENSION));
    if (in_array($ext, $hidden_extensions)) {
        return true;
    }

    return false;
}

function rmrf($dir) {
    if(is_dir($dir)) {
        $files = array_diff(scandir($dir), ['.','..']);
        foreach ($files as $file)
            rmrf("$dir/$file");
        rmdir($dir);
    } else {
        unlink($dir);
    }
}
    function is_recursively_deleteable($d) {
    $stack = [$d];
    while($dir = array_pop($stack)) {
        if(!is_readable($dir) || !is_writable($dir))
            return false;
        $files = array_diff(scandir($dir), ['.','..']);
        foreach($files as $file) if(is_dir($file)) {
            $stack[] = "$dir/$file";
        }
    }
    return true;
}

function get_absolute_path($path) {
        $path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
        $parts = explode(DIRECTORY_SEPARATOR, $path);
        $absolutes = [];
        foreach ($parts as $part) {
            if ('.' == $part) continue;
            if ('..' == $part) {
                array_pop($absolutes);
            } else {
                $absolutes[] = $part;
            }
        }
        return implode(DIRECTORY_SEPARATOR, $absolutes);
    }

function err($code,$msg) {
    http_response_code($code);
    echo json_encode(['error' => ['code'=>intval($code), 'msg' => $msg]]);
    exit;
}

function asBytes($ini_v) {
    $ini_v = trim($ini_v);
    $s = ['g'=> 1<<30, 'm' => 1<<20, 'k' => 1<<10];
    return intval($ini_v) * ($s[strtolower(substr($ini_v,-1))] ?: 1);
}
$MAX_UPLOAD_SIZE = min(asBytes(ini_get('post_max_size')), asBytes(ini_get('upload_max_filesize')));
?>

<!DOCTYPE html>
<html>
<head>
    <!-- Config -->
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <link rel="manifest" href="/manifest.json"></link>
    <title>Home || CDN</title>

    <!-- SEO -->
    <link href="https://test.lucacastelnuovo.nl" rel="canonical">
    <meta content="A system to develop your quick ideas" name="description">

    <!-- Icons -->
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">

    <!-- Styles -->
    <link rel="stylesheet" type="text/css" href="https://cdn.lucacastelnuovo.nl/cdn.lucacastelnuovo.nl/css/main.css">
</head>
<body>
    <div id="top">
        <form action="?" method="post" id="mkdir" />
            <label for=dirname>Create New Folder</label>
            <input id=dirname type=text name=name value="" />
            <input type="submit" value="create" />
        </form>
        <div id="file_drop_target">
            Drag Files Here To Upload
            <b>or</b>
            <input type="file" multiple />
        </div>
        <div id="breadcrumb">&nbsp;</div>
    </div>

    <div id="upload_progress"></div>
    <table id="table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Size</th>
                <th>Modified</th>
                <th>Permissions</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="list">
        </tbody>
    </table>
    <a href="/?logout">Logout</a>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
    <script src="https://cdn.lucacastelnuovo.nl/cdn.lucacastelnuovo.nl/js/main.js"></script>
</body>
</html>
