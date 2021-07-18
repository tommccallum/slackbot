CREATE DATABASE IF NOT EXISTS slackbot;

USE slackbot;

-- Really simple flat database structure.
-- We are only interested in quickly looking up the
-- history of the current message and responding to it.
CREATE TABLE IF NOT EXISTS events (
    member_id           INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id             VARCHAR(10),            -- slack user id
    ts                  VARCHAR,                -- every message has one of these  
    plain_text          TEXT,                   -- text of the message
    json_msg            TEXT,                   -- full json message
    parent_thread_id    VARCHAR,                -- NULL for parent threads
    parent_user_id      VARCHAR,                -- NULL for parent threads
    created             TIMESTAMP
);

