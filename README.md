# YouTube Embed Helper

This Script enables you to embed YouTube Videos without using iframes.<br>
It also works with other Video sites.

You can try it out at [yt.dhusch.de](https://yt.dhusch.de)

You need to [Download yt-dlp](https://github.com/yt-dlp/yt-dlp/releases/latest/download/yt-dlp) and specify the path in the Script! (Line 3)<br>
You can also specify a CORS Proxy (Line 4)<br>
And please set a Contact Mail (Line 5)

## Install
put the file index.php on the root of your (sub)Domain<br>
```
wget https://raw.githubusercontent.com/dhuschde/yt-dl-web/main/index.php
```
create cache dir and give permissions: <br>
```
mkdir cache
chmod 777 cache
```
put yt-dlp on your system<br>
```
wget https://github.com/yt-dlp/yt-dlp/releases/latest/download/yt-dlp
```
**specify the path to yt-dlp on Line 3**

## "Watch here"
Using the watch-here feature, you can somewhat use this as an alternative frontend for youtube

## About Livestreams
The Script will return m3u8 files. The Video/Audio Tag is not able to Play m3u8 files. You might be able to get it working with special javascript code. You can still use URLs like "https://yt-dl.dhusch.de?vid=https://twitch.tv/monstercat" in VLC.
