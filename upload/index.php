<?php
/****************************************************************
 * This file handles uploading of maven artifacts to the server *
 ***************************************************************/

 /**
 * Validates the overall filename to prevent wrong things being uploaded or viewed
 *
 * @param string $input The input file path
 * @param bool $isUploaded Whether the file is being uploaded (true) or viewed (false) 
 * @return bool Whether the name is valid
 */
function isValid($input, $isUploaded) {
    if($isUploaded === true) $res = preg_match_all('/.+\/(maven-metadata\.xml|.*\/(.+\.(jar|pom)(\.asc|)))(\.(sha1|md5)|)$/', $input);
    else $res = preg_match_all('/.+\/(maven-metadata\.xml(\.(sha1|md5)|))$/', $input);
    return $res != false && $res > 0;
}

/**
 * Gets the extension of a file
 * 
 * @param string $input The input file path
 * @return string The file extension
 */
function getFileExtension($input) {
    $arr = explode(".", $input);
    return $arr[count($arr) - 1];
}

/**
 * Gets the name of a file
 *
 * @param string $input The input file path
 * @return string The file name
 */
function getFileName($input) {
    $dirs = explode("/", $input);
    return $dirs[count($dirs) - 1];
}

if(!isset($_GET['path']) || !isValid($_GET['path'], true)) die("HTTP/1.0 404 Not Found");
$path = $_GET['path'];
if($_SERVER['REQUEST_METHOD'] == "PUT") {
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
            die(header("HTTP/1.1 424 Failed Dependency"));
        }
        if($ext == "md5") {
            if(file_get_contents("php://input") != md5_file($origname)) {
                // MD5 violated, delete original file as it was likely tinkered with
                unlink($origname);
                die(header("HTTP/1.1 424 Failed Dependency"));
            }
        } else if(file_get_contents("php://input") != sha1_file($origname)) {
            // SHA1 violated, delete original file as it was likely tinkered with
            unlink($origname);
            die(header("HTTP/1.1 424 Failed Dependency"));
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
    die(header("HTTP/1.1 201 Created"));
} else if($_SERVER['REQUEST_METHOD'] == "GET" && isValid($path, false)) {
    // Search for maven-metadata.xml
    if(file_exists("../maven/" . $path)) {
        if(substr($path, -3) == "xml") header('Content-type: application/xml');
        echo file_get_contents('../maven/' . $path);
    }
    else die(header("HTTP/1.0 404 Not Found"));
} else die(header("HTTP/1.0 404 Not Found"));
?>