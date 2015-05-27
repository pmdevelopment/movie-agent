# movie-agent
In Progress.... there is nothing useful working at the moment!

## What's this?

I am using Amazon Fire TV and the Plex Media Server. This combination works really well for me, with 2 issues:

* No surround sound for most files
* Unstable app

There is a solution for the surround sound problem: Plex App Player "Force Direct Play" is able to play h264/AC3 videos using MP4 containers. (Native player and activated
 AC3 support won't work, wtf?). But most of my files are not using MP4, so i need to convert them.
 
To transcode all existing an new media to the usable encoding is *Part 1* of this software:

* Converting videos (Movies/Series) to h264/AC3
* Make sure its a MP4 container

At the moment i am working at this step. My next steps will be:

* Building a Amazon Fire TV App (based on the WebApp SDK), using the Plex Media Server API to browse and watch all media
* Extend the movie and series information, e.g. showing new episodes or movie sequels to existing media

The software is written in PHP and using Symfony2. Using a sqlite and the builtin server, the installation will be quick and easy.

## What's working?

**26.05.2015**

* Init folders: set type of content
* Scan folders: write all existing folder paths to database

**27.05.2015**

* Scan files: write all existing file paths to database
* Transcode files: analyzing all streams of video file

**Soon**

* Transcode files: stream mapping
* Transcode files: execute
* Cleanup: move originals in backup store, delete after 30 days