# YouTube Embed Helper

This Script enables you to embed YouTube Videos without using iframes.<br>
It also works with other Video sites.

You can try it out at [yt-dl.dhusch.de](https://yt-dl.dhusch.de)

You need to [Download yt-dlp](https://github.com/yt-dlp/yt-dlp/releases/latest/download/yt-dlp) and specify the path in the Script! (Line 3)<br>
If the Page isn't on the root of your (sub)domain, please add the uri to the URL Checker (Line 9)

## About Livestreams
The Script will return m3u8 files. The Video/Audio Tag is not able to Play m3u8 files. You might be able to get it working with special javascript code. You can still use URLs like "https://yt-dl.dhusch.de?vid=https://twitch.tv/monstercat" in VLC.
