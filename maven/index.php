<?php
/************************************************************************************************
 * This file handles uploading of maven artifacts to the server, also serves as directory index *
 ***********************************************************************************************/

/**
 * Validates the overall filename to prevent wrong things being uploaded or viewed
 *
 * @param string $input The input file path
 *
 * @return bool Whether the name is valid
 */
function isValid(string $input): bool {
  $res = preg_match_all('/.+\/(maven-metadata\.xml|.*\/(.+\.(jar|pom)(\.asc|)))(\.(sha1|md5)|)$/', $input);
  return $res != false && $res > 0;
}

/**
 * Gets the extension of a file
 *
 * @param string $input The input file path
 *
 * @return string The file extension
 */
function getFileExtension(string $input): string {
  $arr = explode(".", $input);
  return $arr[count($arr) - 1];
}

/**
 * Gets the name of a file
 *
 * @param string $input The input file path
 *
 * @return string The file name
 */
function getFileName(string $input): string {
  $dirs = explode("/", $input);
  return $dirs[count($dirs) - 1];
}

/**
 * Gets the files description. Returns 'Directory' if file is directory,
 * else the uppercased file extension + '-File', e.g. 'PHP-File'.
 * If the file has no extension, this will just return 'File'.
 *
 * @param string $filename The file name
 * @param string $basedir the directory to scan from
 * @return string The description for the file
 */
function findDescription(string $filename, string $basedir): string {
  if(is_dir($basedir . $filename)) return "Directory";
  if (strpos($filename, '.') !== false) {
    $splitName = explode(".", $filename);
    return strtoupper($splitName[count($splitName) - 1]) . "-File";
  }
  return "File";
}


if($_SERVER['REQUEST_METHOD'] == "PUT") {
  if(!isset($_GET['path']) || !isValid($_GET['path'])) {
    header("HTTP/1.0 404 Not Found");
    die();
  }
  $path = $_GET['path'];
  //Get The Actual Filename
  $filename = getFileName($path);
  $ext = getFileExtension($filename);

  /*********************
   * Checksum checking *
   ********************/
  if($ext == "md5" || $ext == "sha1") {
    //The filename that this checksum checks
    $origname = substr($path, 0, (strlen($ext) + 1) * -1);
    //If original file does not exist, cancel checksum upload
    if(!file_exists($origname)) {
      header("HTTP/1.1 424 Failed Dependency");
      die();
    }
    if($ext == "md5") {
      if(file_get_contents("php://input") != md5_file($origname)) {
        // MD5 violated, delete original file as it was likely tinkered with
        unlink($origname);
        header("HTTP/1.1 424 Failed Dependency");
        die();
      }
    } else if(file_get_contents("php://input") != sha1_file($origname)) {
      // SHA1 violated, delete original file as it was likely tinkered with
      unlink($origname);
      header("HTTP/1.1 424 Failed Dependency");
      die();
    }
  }

  // Create Directory
  mkdir(str_replace($filename, "", $path), 0777, true);
  // Create File
  $res = fopen($path, "w");
  // Write Content
  fwrite($res, file_get_contents('php://input'));
  // Close File
  fclose($res);
  header("HTTP/1.1 201 Created");
  die();
} else {
  $dir = str_replace("\\", "/", dirname(__FILE__)) . (isset($_GET['path']) ? "/" . $_GET['path'] : "");
  if(substr($dir, -1) != "/") $dir = $dir . "/";

  //Required on filehosts like mine where the www root is not labeled as '/'
  $dirSplit = substr($dir, 19);
}?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title>Index of <?php echo $dirSplit?></title>
  <style type="text/css">
    h1 {
      text-align: center;
    }

    table {
      width: 50%;
      margin: 0 25%;
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
    // Do not show link to this directory, hide index.php & .htaccess
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