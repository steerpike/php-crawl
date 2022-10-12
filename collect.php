<?php
error_reporting(E_ALL ^ (E_NOTICE | E_WARNING | E_DEPRECATED));
require "vendor/autoload.php";
use PHPHtmlParser\Dom;

$db = new SQLite3('music.sqlite', SQLITE3_OPEN_CREATE | SQLITE3_OPEN_READWRITE);

$db->query('CREATE TABLE IF NOT EXISTS "artists" (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    "name" VARCHAR,
    "url" VARCHAR UNIQUE,
    "path" VARCHAR,
    "crawled" INTEGER,
    "error" INTEGER
)');

$db->query('CREATE TABLE IF NOT EXISTS "tags" (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    "name" VARCHAR UNIQUE
)');

$db->query('CREATE TABLE IF NOT EXISTS "tracks" (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    "youtube_id" VARCHAR UNIQUE,
    "name" VARCHAR,
    "url" VARCHAR UNIQUE
)');

$db->query('CREATE TABLE IF NOT EXISTS "artists_tracks" (
    "artist_id" INTEGER,
    "track_id" INTEGER,
    PRIMARY KEY ("artist_id", "track_id")
)');

$db->query('CREATE TABLE IF NOT EXISTS "artists_tags" (
    "artist_id" INTEGER,
    "tag_id" INTEGER,
    PRIMARY KEY ("artist_id", "tag_id")
)');

$db->query('CREATE TABLE IF NOT EXISTS "related_artists" (
  "artist_id" INTEGER,
  "related_id" INTEGER,
  PRIMARY KEY ("artist_id", "related_id")
)');



//$url = "https://www.last.fm/music/Florence+%252B+the+Machine";
$url = "https://www.last.fm/music/Gordi";
//$url = "https://www.last.fm/music/Built+for+the+Sea";
//$url = 0;
//$url = "https://www.last.fm/music/Taylor+Swift";
//$url = "https://www.last.fm/music/Stars";

try {

  if($url) {
    complete_single_artist($url, $db);
  } else {
    $results = $db->query('SELECT * FROM artists WHERE crawled=0 AND error=0 LIMIT 5');
    while ($row = $results->fetchArray()) {
      $url = $row['url'];
      complete_single_artist($url, $db);
    }
  }
} catch (Exception $e) {
  $db->exec("UPDATE artists SET error=1 WHERE url='".$url."'");
  echo "Error getting ".$url;
}
function complete_single_artist($url, $db) {
  $insert_artist = $db->prepare('INSERT INTO "artists"
("name", "url", "path", "crawled", "error")
VALUES (:artist_name, :artist_url, :artist_path, :crawled, 0)');

$insert_track = $db->prepare('INSERT INTO "tracks"
("youtube_id", "name", "url")
VALUES (:track_youtube_id, :track_name, :track_url)');

$insert_tag = $db->prepare('INSERT INTO "tags"
("name")
VALUES (:tag_name)');

$insert_artist_track = $db->prepare('INSERT INTO "artists_tracks"
("artist_id", "track_id")
VALUES (:artist_id, :track_id)');

$insert_artist_tag = $db->prepare('INSERT INTO "artists_tags"
("artist_id", "tag_id")
VALUES (:artist_id, :tag_id)');

$insert_related_artists = $db->prepare('INSERT INTO "related_artists"
("artist_id", "related_id")
VALUES (:artist_id, :related_id)');


  $dom = new Dom;
  $dom->loadFromUrl($url);
  $html = $dom->outerHtml;

  // $tags = ??
  // $artist = ??
  // $tracks = ??
  $header = $dom->find('.header-new-title');
  $artist_name = $header->text();
  //$headerLink = $dom->find('.header-new-content');
  $urlArray = parse_url($url, PHP_URL_PATH);
  $segments = explode('/', $urlArray);
  $artist_path = "/music/".end($segments);
  $artist_url = $url;
  $insert_artist->bindValue(':artist_name', $artist_name);
  $insert_artist->bindValue(':artist_url', $artist_url);
  $insert_artist->bindValue(':artist_path', $artist_path);
  $insert_artist->bindValue(':crawled', 1);
  $insert_artist->execute();
  $result = $db->querySingle("SELECT id FROM artists WHERE path='".$artist_path."'", true);
  $artist_id = $result["id"];
  echo $artist_id." ".$artist_name." ".$artist_path." ".$artist_url;
  if($artist_id !== 0) {
    $trackList = $dom->find('.chartlist-play-button');
    if($trackList !== null && count($trackList) !== 0) {
      foreach($trackList as $track) {
        $track_name = $track->{'data-track-name'};
        $track_url = $track->href;
        $track_youtube_id = $track->{'data-youtube-id'};
        $insert_track->bindValue(':track_youtube_id', $track_youtube_id);
        $insert_track->bindValue(':track_name', $track_name);
        $insert_track->bindValue(':track_url', $track_url);
        $insert_track->execute();
        $result = $db->querySingle("SELECT id FROM tracks WHERE url='".$track_url."'", true);
        $track_id = $result["id"];
        //$track_id = $db->lastInsertRowID();
        if($track_id !== 0 && $artist_id !== 0) {
          $insert_artist_track->bindValue(':artist_id', $artist_id);
          $insert_artist_track->bindValue(':track_id', $track_id);
          $insert_artist_track->execute();
        }
      }
    } else {
      echo "No tracks found.";
    }
    $tags = $dom->find('.tag > a');
    foreach($tags as $tag) {
      $tag_name = $tag->text();
      $insert_tag->bindValue(':tag_name', $tag_name);
      $insert_tag->execute();
      $result = $db->querySingle("SELECT id FROM tags WHERE name='".$tag_name."'", true);
      $tag_id = $result["id"];
      //$tag_id = $db->lastInsertRowID();
      if($tag_id !== 0 && $artist_id !== 0) {
        $insert_artist_tag->bindValue(':artist_id', $artist_id);
        $insert_artist_tag->bindValue(':tag_id', $tag_id);
        $insert_artist_tag->execute();
      }
      //echo $tag_name;
    }

    $relatedArtists = $dom->find('.artist-similar-artists-sidebar-item-name > a');
    foreach($relatedArtists as $artist) {
      $related_artist_name = $artist->text();
      $related_artist_path = $artist->href;
      $related_artist_url = "https://www.last.fm".$artist->href;
      $insert_artist->bindValue(':artist_name', $related_artist_name);
      $insert_artist->bindValue(':artist_url', $related_artist_url);
      $insert_artist->bindValue(':artist_path', $related_artist_path);
      $insert_artist->bindValue(':crawled', 0);
      $insert_artist->execute();
      $result = $db->querySingle("SELECT id FROM artists WHERE url='".$related_artist_url."'", true);
      $related_id = $result["id"];
      //$related_id = $db->lastInsertRowID();
      if($related_id !== 0 && $artist_id !== 0) {
        $insert_related_artists->bindValue(':artist_id', $artist_id);
        $insert_related_artists->bindValue(':related_id', $related_id);
        $insert_related_artists->execute();
      }
      $db->exec('UPDATE artists SET crawled=1 WHERE id='.$artist_id);
      //echo $related_artist_name." ".$related_artist_path." ".$related_artist_url;
    }
  }
}
