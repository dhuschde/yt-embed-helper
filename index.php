<?php

$yt_dlp_path = "../bin/yt-dlp"; // where is yt-dlp installed?
$proxy = ""; // enter proxy if wanted (with trailing /)

// get the URL, where this script is installed
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'];
$uri = $_SERVER['REQUEST_URI'];
$currentURL = $protocol . $host; // . $uri; 
// !!! (you need to add the uri if this script isn't installed on the root of your (sub)domain)

if (!empty($_GET['vid'])) {
  // Get the vidID from the query string parameter
  $vidID = $_GET['vid'];
  $action = $_GET['format'];
} else if(!empty($_GET['v'])) {
  $vidID = $_GET['v'];
  $action = 'watch-here';
} else {
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
  echo '<br><input type="submit" value="Download"></form>';
  echo '<div style="border:1px dotted;"><p style="margin:0;">This also works with other Video sites.<br>Contact Info for Issues: <a target="_blank" href="https://dhusch.de/kontakt">click here</a><br>Please send (DMCA) Takedowns directly to the Platform the video is hosted on!</p>';
  echo '</div><br><br><br>';
  echo '<a href="./?vid=none&format=source-code">Source Code</a>';
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

} else if ($action == "audio") { // get Link to audio
 $command = "$yt_dlp_path --prefer-free-formats -x --get-url {$vidID}";
 $output = shell_exec($command);

} else if ($action == "image") { // get Link to thumbnail
 $command = "$yt_dlp_path --get-thumbnail '-f best' {$vidID}";
 $output = shell_exec($command);
 $output = trim($output);

} else if ($action == "html") { // show how to embed on website
 echo "<p>You can use this Code to embed the Video on your Website:</p>";
 echo "<h1>Video:</h1>";
 echo "<code>&lt;video preload='none' controls src='$currentURL?vid=$vidID' poster='$currentURL?vid=$vidID&format=image'&gt;&lt;/video&gt;</code><br>";
 echo "<video preload='none' controls src='$currentURL?vid=$vidID' poster='$currentURL?vid=$vidID&format=image'></video>";
 echo "<h1>Audio:</h1>";
 echo "<code>&lt;audio preload='none' controls src='$currentURL?vid=$vidID&format=audio'&gt;&lt;/audio&gt;</code><br>";
 echo "<audio preload='none' controls src='$currentURL?vid=$vidID&format=audio'></audio>";
 echo "<h2>About Livestreams</h2>YouTube, Twitch and other Livestreaming platforms will return m3u8 files. The Video/Audio Tag is not able to Play m3u8 files. You might be able to get it working with special javascript code. You can still use URLs like '$currentURL?vid=$vidID' in VLC.";
 exit;

} else if ($action == "json") { // give out metadata about video
 $command = "$yt_dlp_path -j -f 'best/bestvideo+bestaudio' $vidID";
 $output = shell_exec($command);
 header('Content-Type: application/json');
 echo "$output";
 exit;

} else if ($action == "watch") { // prettyfy link a bit
if (strpos($vidID, "youtu") !== false) {
$command = "$yt_dlp_path --get-id {$vidID}";
 $vidID = shell_exec($command);
}
header("Location: $currentURL?v=$vidID"); // not using below header bc: you might use a proxy

} else if ($action == "source-code") { // show source code
    $content = file_get_contents(__FILE__);
    echo '<pre>', htmlspecialchars($content), '</pre>';
   exit;

} else if ($action == "watch-here") { // show video on front end
  $command = "$yt_dlp_path -j -f 'best/bestvideo+bestaudio' $vidID";
  $output = shell_exec($command);
  $data = json_decode($output, true);

// Video Infos
  $title = $data['title'];
  $uploaderName = $data['channel'];
  $uploaderUrl = $data['channel_url'];
  $channelSubs = $data['channel_follower_count'];
  if($data['channel_is_verified']) $verify = "✓";
  $vidUrl = $data['url'];
  $thumbnail = $data['thumbnail'];
  $description = nl2br(htmlspecialchars($data['description'], ENT_QUOTES, 'UTF-8'));
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
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta property='og:title' content='$title - $uploaderName - $platform'>
    <meta property='og:description' content='$description'>
    <meta property='og:image' content='$proxy$thumbnail'>
    <meta property='og:video' content='$proxy$vidUrl'>
    <meta property='og:video:secure_url' content='$proxy$vidUrl'>
    <meta property='og:video:type' content='video/mp4'>
    <meta property='og:video:width' content='vidWidth'>
    <meta property='og:video:height' content='$vidHeight'>
    <meta property='og:video:poster' content='$proxy$thumbnail'>
    <title>$title - $uploaderName - $platform</title>
    <style>
        body {
            background-color: #2d2d2d;
            color: white;
	    align-items: center;
	    justify-content: center;

        }

        a {
            color: white;
            text-decoration: underline;
        }
    </style>
</head>
<body>";

echo "
<div style='width: 80%; margin-left: 10%;'>
<h1>$title</h1>
<video controls poster='$proxy$thumbnail' src='$proxy$vidUrl' width='100%'></video>
<p><a href='$uploaderUrl' target='_blank'>$uploaderName</a>$verify($channelSubs) | $vidDate | 👀 $vidViews | 👍 $vidLikes | 💬 $vidComments</p>
<h2>Description</h2>
<p>$description</p>
</div><br>
<small>Disclaimer: this site doesn't host any of the above content. It is hosted on $platform. $proxyText Go watch on <a target='_blank' href='$vidShare'>$platform</a>. Please send (DMCA) Takedown requests directly to $platform!</small>
";

echo "</body></html>";
  exit;

} else { // get the Link to the Video
 $command = "$yt_dlp_path --prefer-free-formats -f 'best/bestvideo+bestaudio' --get-url {$vidID}";
 $output = shell_exec($command);
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
