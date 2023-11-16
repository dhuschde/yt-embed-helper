// A Proxy for vtt Subtitles on YouTube (bc cross origin error while using watch-here)
<?php
header("Content-Type: text/vtt; charset=UTF-8");

// Get the VTT URL from the query parameters
$vttUrl = isset($_GET['url']) ? $_GET['url'] : '';

// Ensure the URL is not empty
if (empty($vttUrl)) {
    die('Error: VTT URL is missing.');
}

// Use cURL to download the gzipped contents of the VTT file
$ch = curl_init($vttUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_ENCODING, ''); // Automatically decode gzip
$vttContent = curl_exec($ch);
curl_close($ch);

// Display the VTT content
echo $vttContent;
?>
