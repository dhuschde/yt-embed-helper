<?php

$yt_dlp_path = "../bin/yt-dlp"; // where is yt-dlp installed?

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
} else {
  // Display the form for entering the video ID
  echo '<h1>YouTube Embed Helper</h1>';
  echo '<form method="get">';
  echo '<input type="text" name="vid" placeholder="Video ID or URL"><br>';
  echo '<input type="radio" name="format" value="html" checked> <label>HTML</label>';
  echo '<input type="radio" name="format" value="video"> <label>Video</label>';
  echo '<input type="radio" name="format" value="audio"> <label>Audio</label>';
  echo '<input type="radio" name="format" value="image"> <label>Thumbnail</label>';
  echo '<br><input type="submit" value="Download"></form>';
  echo '<div style="border:1px dotted;"><p style="margin:0;">HTML will generate HTML Code that you can use to embed a Video/Audio on a Website.<br>This will use the video/audio tag instead of an iframe.<br>This also works with other Video sites.</p>';
  echo '</div><br><br><br>';
  echo '<a href="https://github.com/dhuschde/yt-dl-web">Source Code</a>';
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
 $command = "$yt_dlp_path -x --get-url {$vidID}";
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
 exit;

} else { // get the Link to the Video
 $command = "$yt_dlp_path -f 'best/bestvideo+bestaudio' --get-url {$vidID}";
 $output = shell_exec($command);
}

if (!empty($output)) {
  // Redirect to the YouTube video with the specified ID
  header("Location: $output");
  exit;
} else {
  // Display an error message if the command failed
  echo '<h1>Error</h1>';
  echo '<p>The specified video ID could not be downloaded.</p>';
  echo '<p>Please try again.</p>';
  exit;
}
?>
