<?php
/************************************************************
 * This is a file viewer to be put in root maven directory *
 ***********************************************************/

//Hide index.php
if(substr($_SERVER['REQUEST_URI'], -9) == "index.php") die(header("HTTP/1.0 404 Not Found"));

//Get the scripts directory name
$dir = dirname(__FILE__) . (isset($_GET['path']) ? "/" . $_GET['path'] : "");
if(substr($dir, -1) != "/") $dir = $dir . "/";

//Required on filehosts like mine where the www root is not labeled as '/'
$dirSplit = substr($dir, 19);

/**
 * Gets the files description. Returns 'Directory' if file is directory,
 * else the uppercased file extension + '-File', e.g. 'PHP-File'.
 * If the file has no extension, this will just return 'File'.
 * 
 * @param string $filename The file name
 * @param string $basedir The directory the file is in, ending with '/'
 * @return string The description for the file
 */
function findDescription($filename, $basedir) {
    if(is_dir($basedir . $filename)) return "Directory";
    if (strpos($filename, '.') !== false) {
        $splitName = explode(".", $filename);
        return strtoupper($splitName[count($splitName) - 1]) . "-File";
    }
    return "File";
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Index of <?php echo $dirSplit?></title>
<style type="text/css">
h1 {
    text-align: center;
}

table {
    width: 50%;
    margin: 0% 25%;
}

table, th, td {
    border: 1px solid black;
    border-collapse: collapse;
}

th, td {
    padding: 15px;
}

a:visited, a:link {
    color: blue;
}

th {
    text-align: left;
    font-weight: normal;
}

tr:nth-child(odd) {
   background-color:#fff;
}

tr:nth-child(even) {
    background-color: #eee;
}

thead tr {
    background-color: #aaa !important;
}
</style>
</head>
<body>
<h1>Index of <?php echo $dirSplit?></h1>
<table>
<thead>
<tr>
<th>Filename:</th>
<th>Description:</th>
<th>Filesize:</th>
<th>Last Modified:</th>
</tr>
</thead>
<tbody>
<?php foreach (scandir($dir) as $file) {
    // Do not show link to this directory, hide index.php
    if($file != "index.php" && $file != "." && $file != ".htaccess") {
        if(is_dir($dir . $file)) {
            // Directories end with '/', looks better
            $file = $file . "/";
            // Directories don't 'have' a size
            $size = "-";
        } else $size = filesize($dir . $file) . "b";
        echo "<tr>\n<td>\n<a href=\"$file\">$file</a>\n</td>\n<td>" . findDescription($file, $dir) . "</td>\n<td>$size</td>\n<td>" . date("Y-m-d H:i" , filemtime($dir . $file)) . "</td>\n</tr>\n";
    }
}?>
</tbody>
</table>
</body>
</html>