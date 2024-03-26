<?php

$yt_dlp_path = "../bin/yt-dlp"; // where is yt-dlp installed?
if($_GET['proxy'] != "false") $proxy = ""; // enter CORS proxy if wanted (with trailing /)
$mail = "";

// get the URL, where this script is installed
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'];
$uri = $_SERVER['REQUEST_URI'];
$currentURL = $protocol . $host;

if (!empty($_GET['vid']) or !empty($_GET['v'])) { // vid id is given
  // Get the vidID from the query string parameter
	  if(!empty($_GET['vid'])){ // normal
	  $vidID = $_GET['vid'];
	  $action = $_GET['format'];
	  } elseif(!empty($_GET['v'])){ // for watch-here only
	  $vidID = $_GET['v'];
	  $action = 'watch-here';
	  }
  
  // Delete files older than one hour in the cache directory
$files = glob("cache/" . '*.json');
$currentTimestamp = time();
foreach ($files as $file) {
    $fileAge = $currentTimestamp - filemtime($file);
    if ($fileAge > 3600) {
        unlink($file);
    }
}
  
  // read cache and create if necessary 
  if (!file_exists("cache/" . md5($vidID) . ".json")) {
  $jsonCmd = shell_exec("$yt_dlp_path -j -f 'best/bestvideo+bestaudio' $vidID");
  file_put_contents("cache/" . md5($vidID) . ".json", $jsonCmd);
  }
  $jsonData = file_get_contents("cache/" . md5($vidID) . ".json");
  $data = json_decode($jsonData, true);
  
  
} else {
echo "
<head>
<title>YouTube Embed Helper</title>
    <style>
        body {
            background-color: #2d2d2d;
            color: white;
        }

        a {
            color: white;
            text-decoration: underline;
        }
    </style>
</head><body>";

  // Display the form for entering the video ID
  echo '<h1>YouTube Embed Helper</h1>';
  if(!empty($proxy))echo '<p>(including Media Proxy)</p>';
  echo '<form method="get">';
  echo '<input type="text" name="vid" placeholder="Video ID or URL"><br>';
  echo '<input type="radio" name="format" value="watch" checked> <label>Watch here</label>';
  echo '<input type="radio" name="format" value="html"> <label>Generate Embed Code</label>';
  echo '<input type="radio" name="format" value="video"> <label>Video</label>';
  echo '<input type="radio" name="format" value="audio"> <label>Audio</label>';
  echo '<input type="radio" name="format" value="image"> <label>Thumbnail</label>';
  echo '<input type="radio" name="format" value="json"> <label>Metadata/JSON</label>';
  echo '<br><input type="submit" value="Do it"></form>';
  echo '<div style="border:1px dotted;"><p style="margin:0;">This also works with other Video sites.<br>Contact Info for Issues: <a href="mailto:' . $mail . '">click here</a><br>Please send (DMCA) Takedowns directly to the Platform the video is hosted on!</p>';
  echo '</div><br><br><br>';
  echo '<a href="./?vid=none&format=source-code">Source Code</a><hr>';
  echo '<h2>Privacy Policy</h2>
<h3>Logs</h3>
<p>We keep logs for debug and testing purposes. These are the general nginx Logs</p>
<ol>
<li>IP Address</li>
<li>Browser Agent</li>
<li>Time of Access</li>
<li>Accessed URL</li>
</ol>
<h3>Cache</h3>
<p>We also keep a Videos MetaData for about one hour to reduce stress on our system</p>';
if(!empty($proxy))echo '<h3>What we send to third Party (like YouTube)</h3><p>We use a Proxy, therefore:</p><ol><li>OUR IP Address</li><li>YOUR Browser Agent</li><li>Time of Access</li><li>They might get additional Info - read their Privacy Policy for more Info</li></ol><p><a target="_blank" href="https://policies.google.com/privacy?hl=de">YouTubes Privacy Policy</a></p>';
echo "</body>";
  exit;
}

if ($action == "get-chat") { // get Link to Live Chat (only YouTube)
 $command = "$yt_dlp_path --get-id {$vidID}";
 $output = shell_exec($command);
 if (empty($output)) {
 echo "There was an Error<br>Tries again in 15 Seconds<br><meta http-equiv='refresh' content='15'>";
 exit;
 }
 $output = "https://studio.youtube.com/live_chat?is_popout=1&v=$output";

} else if ($action == "audio") { // get Link to audio (cache not used for reason: getting video, not audio)
 $command = "$yt_dlp_path --prefer-free-formats -x --get-url {$vidID}";
 $output = shell_exec($command);

} else if ($action == "image") { // get Link to thumbnail
 $output = $data['thumbnail'];

} else if ($action == "html") { // show how to embed on website
echo "
<head>
<title>YouTube Embed Code</title>
    <style>
        body {
            background-color: #2d2d2d;
            color: white;
        }

        a {
            color: white;
            text-decoration: underline;
        }
    </style>
</head><body>";
 echo "<p>You can use this Code to embed the Video on your Website:</p>";
 echo "<h1>Video:</h1>";
 echo "<code>&lt;video preload='none' controls src='$currentURL?vid=$vidID' poster='$currentURL?vid=$vidID&format=image'&gt;&lt;/video&gt;</code><br>";
 echo "<video preload='none' controls src='$currentURL?vid=$vidID' poster='$currentURL?vid=$vidID&format=image'></video>";
 echo "<h1>Audio:</h1>";
 echo "<code>&lt;audio preload='none' controls src='$currentURL?vid=$vidID&format=audio'&gt;&lt;/audio&gt;</code><br>";
 echo "<audio preload='none' controls src='$currentURL?vid=$vidID&format=audio'></audio>";
 echo "<h2>About Livestreams</h2>YouTube, Twitch and other Livestreaming platforms will return m3u8 files. The Video/Audio Tag is not able to Play m3u8 files. You might be able to get it working with special javascript code. You can still use URLs like '$currentURL?vid=$vidID' in VLC.";
 echo "</body>";
 exit;

} else if ($action == "json") { // give out metadata about video
 header('Content-Type: application/json');
 echo "$jsonData";
 exit;

} else if ($action == "watch") { // prettyfy link a bit
if ($data['extractor'] == "youtube") {
 $vidID = $data['id'];
}
header("Location: $currentURL?v=$vidID"); // not using below header bc: you might use a proxy

} else if ($action == "source-code") { // show source code
    $content = file_get_contents(__FILE__);
    echo '<pre>', htmlspecialchars($content), '</pre>';
   exit;

} else if ($action == "sub_proxy") { // subtitle Proxy (Cross Origin is bad)

header("Content-Type: text/vtt; charset=UTF-8");

if (empty($vidID)) {
    die('Error: VTT URL is missing.');
}

$ch = curl_init($vidID);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_ENCODING, ''); // Automatically decode gzip
$vttContent = curl_exec($ch);
curl_close($ch);

echo $vttContent;
exit;

} else if ($action == "watch-here") { // show video on front end
// Video Infos
  $title = $data['title'];
  $uploaderName = $data['channel'];
  $uploaderUrl = $data['channel_url'];
  $channelSubs = $data['channel_follower_count'];
  if($data['channel_is_verified']) $verify = "✓";
  $vidUrl = $data['url'];
  $thumbnail = $data['thumbnail'];
  $description = nl2br(htmlspecialchars($data['description'], ENT_QUOTES, 'UTF-8'));
  if(empty($description)) $description = "This videos Description is empty.";
  $vidLikes = $data['like_count'];
  $vidDate = $data['upload_date'];
  $vidDate = DateTime::createFromFormat('Ymd', $vidDate);
  $vidDate = $vidDate->format('F j, Y');
  $vidViews = $data['view_count'];
  $vidComments = $data['comment_count'];
  $vidHeight = $data['height'];
  $vidWidth = $data['width'];
  $platform = $data['extractor_key'];
  $vidShare = $data['webpage_url'];
  if(!empty($proxy)) $proxyText = "This site proxies every connection to $platform.";
// Building site
  echo "<!DOCTYPE html> 
<html lang='{$data['language']}'>
<head>
    <meta charset='UTF-8'>
    <meta property='og:title' content='$title - $uploaderName - $platform'>
    <meta property='og:description' content='$description'>
    <meta property='og:image' content='$proxy$thumbnail'>
    <meta property='og:video' content='{$currentURL}?vid=$vidID'>
    <meta property='og:video:secure_url' content='{$currentURL}?vid=$vidID'>
    <meta property='og:video:type' content='video/mp4'>
    <meta property='og:video:width' content='vidWidth'>
    <meta property='og:video:height' content='$vidHeight'>
    <meta property='og:video:poster' content='$proxy$thumbnail'>
    <title>$title - $uploaderName - $platform</title>
    <style>
        body {
            background-color: #2d2d2d;
            color: white;
        }

        a {
            color: white;
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div style='width: 80%; margin-left: 10%;'>
<h1>$title</h1>
<video class='center' controls poster='$proxy$thumbnail' src='$proxy$vidUrl' style='margin-left:auto;margin-right:auto;display:block; max-height:80vh;max-width:100%'>
";
//Subtitles TODO: Optimize!
if (isset($data['subtitles']) && is_array($data['subtitles'])) {
    // Loop through each language
    foreach ($data['subtitles'] as $langCode => $languageSubtitles) {
        foreach ($languageSubtitles as $subtitle) {
	    if($subtitle['ext']=="vtt"){
		$url=urlencode($subtitle['url']);
		echo "<track label='{$subtitle['name']}' kind='subtitles' srclang='$langCode' src='./?format=sub_proxy&vid=$url'/>";
	    }
        }
    }
}

// Auto Subtitles (Original)
if (isset($data['automatic_captions']) && is_array($data['automatic_captions'])) {
    // Loop through each language
    foreach ($data['automatic_captions'] as $langCode => $languageSubtitles) {
        foreach ($languageSubtitles as $subtitle) {
            if($subtitle['ext']=="vtt" && !empty($subtitle['name']) && stripos($subtitle['name'], 'original')){
                $url=urlencode($subtitle['url']);
                echo "<track label='Auto-{$subtitle['name']}' kind='subtitles' srclang='$langCode' src='./?format=sub_proxy&vid=$url'/>";
            }
        }
    }
}

// Auto Subtitles
if (isset($data['automatic_captions']) && is_array($data['automatic_captions'])) {
    // Loop through each language
    foreach ($data['automatic_captions'] as $langCode => $languageSubtitles) {
        foreach ($languageSubtitles as $subtitle) {
            if($subtitle['ext']=="vtt" && !empty($subtitle['name']) && !stripos($subtitle['name'], 'original')){
                $url=urlencode($subtitle['url']);
                echo "<track label='Auto-{$subtitle['name']}' kind='subtitles' srclang='$langCode' src='./?format=sub_proxy&vid=$url'/>";
            }
        }
    }
}


echo "
</video>
<p><a href='$uploaderUrl' target='_blank'>$uploaderName</a>$verify($channelSubs) | $vidDate | 👀 $vidViews | 👍 $vidLikes | 💬 $vidComments</p>
<hr><p>Availability: {$data['availability']} | Age Limit: {$data['age_limit']}<br>Category: ";
foreach($data['categories'] as $tag) echo "$tag ";
if(!empty($data['tags']))echo "<br>Tags: ";
foreach($data['tags'] as $tag) echo "$tag, ";
echo "</p>
<p><a href='$vidShare' target='_blank'>Open on $platform</a> | <a href='.?vid=$vidID&format=html' target='_blank'>Embed Code</a></p>
<hr>
<h2>Description</h2>
<p>$description</p>
</div><br>
<small>Disclaimer: this site doesn't host any of the above content. It is hosted on $platform. $proxyText Please send (DMCA) Takedown requests directly to $platform!</small>
</body>
</html>";
  exit;

} else { // get the Link to the Video
 $output = $data['url'];
}

if (!empty($output)) {
  // Redirect to the YouTube video with the specified ID (Through my proxy to further reduce tracking.)
  header("Location: $proxy$output"); // you can add a proxy by putting it infront the $output
  exit;
} else {
  // Display an error message if the command failed
  echo '<h1>Error</h1>';
  echo '<p>The specified video ID could not be downloaded.</p>';
  echo '<p>Please try again.</p>';
  exit;
}
?>

