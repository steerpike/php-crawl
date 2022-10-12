PRAGMA foreign_keys=OFF;
BEGIN TRANSACTION;
CREATE TABLE IF NOT EXISTS Artists(
    "id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    "name" VARCHAR,
    "url" VARCHAR UNIQUE,
    "path" VARCHAR,
    "crawled" INTEGER,
    "error" INTEGER
);
INSERT INTO artists VALUES(1,'Gordi','https://www.last.fm/music/Gordi','/music/Gordi',1,0);
INSERT INTO artists VALUES(2,'sad13','https://www.last.fm/music/sad13','/music/sad13',0,0);
INSERT INTO artists VALUES(3,'Stars and Rabbit','https://www.last.fm/music/Stars+and+Rabbit','/music/Stars+and+Rabbit',0,0);
INSERT INTO artists VALUES(4,'Wesley Gonzalez','https://www.last.fm/music/Wesley+Gonzalez','/music/Wesley+Gonzalez',0,0);
CREATE TABLE IF NOT EXISTS Tags(
    "id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    "name" VARCHAR UNIQUE
);
INSERT INTO tags VALUES(1,'indie pop');
INSERT INTO tags VALUES(2,'indie');
INSERT INTO tags VALUES(3,'female vocalists');
INSERT INTO tags VALUES(4,'heavy metal');
INSERT INTO tags VALUES(5,'australia');
CREATE TABLE IF NOT EXISTS Tracks(
    "id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    "youtube_id" VARCHAR UNIQUE,
    "name" VARCHAR,
    "url" VARCHAR UNIQUE
);
INSERT INTO tracks VALUES(1,'G1j-Pq3HWZE','Way I Go','https://www.youtube.com/watch?v=G1j-Pq3HWZE');
INSERT INTO tracks VALUES(2,'o1vq0bsONbc','Extraordinary Life','https://www.youtube.com/watch?v=o1vq0bsONbc');
INSERT INTO tracks VALUES(3,'tnbVTZ8jRRY','Heaven I Know','https://www.youtube.com/watch?v=tnbVTZ8jRRY');
INSERT INTO tracks VALUES(4,'QgeZFh887-E','Avant Gardener','https://www.youtube.com/watch?v=QgeZFh887-E');
INSERT INTO tracks VALUES(5,'oI9s6RPssEo','Can We Work It Out','https://www.youtube.com/watch?v=oI9s6RPssEo');
INSERT INTO tracks VALUES(6,'sBUDAmDf-DY','Aeroplane Bathroom','https://www.youtube.com/watch?v=sBUDAmDf-DY');
INSERT INTO tracks VALUES(7,'LTxEaeymYbo','Grass Is Blue','https://www.youtube.com/watch?v=LTxEaeymYbo');
CREATE TABLE IF NOT EXISTS Artists_Tracks(
    "artist_id" INTEGER,
    "track_id" INTEGER,
    PRIMARY KEY ("artist_id", "track_id")
    FOREIGN KEY ("artist_id") REFERENCES Artists("id")
    FOREIGN KEY ("track_id") REFERENCES Tracks("id")
);
INSERT INTO artists_tracks VALUES(1,1);
INSERT INTO artists_tracks VALUES(1,2);
INSERT INTO artists_tracks VALUES(1,3);
INSERT INTO artists_tracks VALUES(1,4);
INSERT INTO artists_tracks VALUES(1,5);
INSERT INTO artists_tracks VALUES(1,6);
INSERT INTO artists_tracks VALUES(1,7);
CREATE TABLE IF NOT EXISTS Artists_Tags(
    "artist_id" INTEGER,
    "tag_id" INTEGER,
    PRIMARY KEY ("artist_id", "tag_id"),
    FOREIGN KEY ("artist_id") REFERENCES Artists("id")
    FOREIGN KEY ("tag_id") REFERENCES Tags("id")
);
INSERT INTO artists_tags VALUES(1,1);
INSERT INTO artists_tags VALUES(1,2);
INSERT INTO artists_tags VALUES(1,3);
INSERT INTO artists_tags VALUES(1,4);
INSERT INTO artists_tags VALUES(1,5);
CREATE TABLE IF NOT EXISTS Related_Artists(
  "artist_id" INTEGER,
  "related_id" INTEGER,
  PRIMARY KEY ("artist_id", "related_id")
  FOREIGN KEY ("artist_id") REFERENCES Artists("id")
  FOREIGN KEY ("related_id") REFERENCES Artists("id")
);
INSERT INTO related_artists VALUES(1,2);
INSERT INTO related_artists VALUES(1,3);
INSERT INTO related_artists VALUES(1,4);
DELETE FROM sqlite_sequence;
INSERT INTO sqlite_sequence VALUES('artists',4);
INSERT INTO sqlite_sequence VALUES('tracks',7);
INSERT INTO sqlite_sequence VALUES('tags',5);
COMMIT;
