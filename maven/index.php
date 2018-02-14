<?php
/************************************************************
 * This is a file viewer to be put in every maven directory *
 ***********************************************************/

//Hide index.php
if(substr($_SERVER['REQUEST_URI'], -9) == "index.php") die(header("HTTP/1.0 404 Not Found"));

//Get the directory name
$dir = dirname(__FILE__);
//Required on filehosts like mine where the www root is not labeled as '/'
$dirSplit = substr($dir, 19);

/**
 * Gets the files description. Returns 'Directory' if file is directory or contains no dot,
 * else the uppercased file extension + '-File', e.g. 'PHP-File'.
 * 
 * @param string $file
 * @return string The description for the file
 */
function findDescription($file) {
    if(is_dir($file)) return "Directory";
    $splitName = explode(".", $file);
    return strtoupper($splitName[count($splitName) - 1]) . "-File";
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
    if($file != "index.php" && $file != ".") {
        if(is_dir($file)) {
            // Directories end with '/', looks better
            $file = $file . "/";
            // Directories don't 'have' a size
            $size = "-";
        } else $size = filesize($file) . "b";
        echo "<tr>\n<td>\n<a href=\"$file\">$file</a>\n</td>\n<td>" . findDescription($file) . "</td>\n<td>$size</td>\n<td>". date("Y-m-d H:i" ,filemtime($file)) ."</td>\n</tr>\n";
    }
}?>
</tbody>
</table>
</body>
</html>