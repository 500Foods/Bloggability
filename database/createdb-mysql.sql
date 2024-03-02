-- (Re)Initialize a MySQL Bloggability Database
-- This creates all the tables, keys, foreign keys, and sample data
--
-- WARNING: This will drop and recreate all tables, so do not run
-- against any database that has data that you don't want to lose.

USE blog;

-- Print initial message
SELECT 'INITIALIZE DATABASE';

-- Drop existing tables
DROP TABLE IF EXISTS ACCOUNT;
DROP TABLE IF EXISTS LOOKUP;
DROP TABLE IF EXISTS AUTHOR;
DROP TABLE IF EXISTS AUTHOR_ACCOUNT;
DROP TABLE IF EXISTS WEBLOG;
DROP TABLE IF EXISTS WEBLOG_ACCOUNT;
DROP TABLE IF EXISTS BLOG;
DROP TABLE IF EXISTS COMMENT;
DROP TABLE IF EXISTS ACTION;
DROP TABLE IF EXISTS OPTION;
DROP TABLE IF EXISTS NOTIFY;
DROP TABLE IF EXISTS HISTORY;
DROP TABLE IF EXISTS TOKEN;
DROP TABLE IF EXISTS APIKEY;

-- Table: ACCOUNT
CREATE TABLE ACCOUNT (
  account_id CHAR(36) NOT NULL,
  account_status INTEGER NOT NULL,
  account_name VARCHAR(100) NOT NULL,
  account_password_hash VARCHAR(255) NOT NULL,
  account_email VARCHAR(255) NOT NULL,
  account_created_by CHAR(36) NOT NULL,
  account_created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  account_updated_by CHAR(36) NOT NULL,
  account_updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (account_id)
);

-- Insert sample ACCOUNT data
INSERT INTO ACCOUNT (account_id, account_status, account_name, account_password_hash, account_email, account_created_by, account_updated_by)
VALUES
  ('admin', 1, 'Administrator', 'password', 'administrator@example.com', 'admin', 'admin');

-- Print number of rows inserted
SELECT CONCAT('ACCOUNT table created and sample data inserted (', COUNT(*), ' rows)') AS message FROM ACCOUNT;

-- Table: LOOKUP
CREATE TABLE LOOKUP (
  lookup_id INTEGER NOT NULL,
  lookup_key INTEGER NOT NULL,
  lookup_sort INTEGER NOT NULL,
  lookup_value VARCHAR(100),
  lookup_description TEXT,
  lookup_created_by CHAR(36) NOT NULL,
  lookup_created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  lookup_updated_by CHAR(36) NOT NULL,
  lookup_updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (lookup_id, lookup_key)
);

-- Insert sample LOOKUP data
INSERT INTO LOOKUP (lookup_id, lookup_key, lookup_sort, lookup_value, lookup_description, lookup_created_by, lookup_updated_by)
VALUES
  (1, -1, -1, 'Account Status', NULL, 'admin', 'admin'),
  (1, 0, 0, 'Disabled', NULL, 'admin', 'admin'),
  (1, 1, 1, 'Active', NULL, 'admin', 'admin'),
  (1, 2, 2, 'Subscriber', NULL, 'admin', 'admin');

INSERT INTO LOOKUP (lookup_id, lookup_key, lookup_sort, lookup_value, lookup_description, lookup_created_by, lookup_updated_by)
VALUES
  (2, -1, -1, 'Author Status', NULL, 'admin', 'admin'),
  (2, 0, 0, 'Disabled', NULL, 'admin', 'admin'),
  (2, 1, 1, 'Active', NULL, 'admin', 'admin');

INSERT INTO LOOKUP (lookup_id, lookup_key, lookup_sort, lookup_value, lookup_description, lookup_created_by, lookup_updated_by)
VALUES
  (3, -1, -1, 'Author Account Status', NULL, 'admin', 'admin'),
  (3, 0, 0, 'None', NULL, 'admin', 'admin'),
  (3, 1, 1, 'View', NULL, 'admin', 'admin'),
  (3, 2, 2, 'Edit', NULL, 'admin', 'admin'),
  (3, 3, 3, 'Publish', NULL, 'admin', 'admin'),
  (3, 4, 4, 'Manage', NULL, 'admin', 'admin');

INSERT INTO LOOKUP (lookup_id, lookup_key, lookup_sort, lookup_value, lookup_description, lookup_created_by, lookup_updated_by)
VALUES
  (4, -1, -1, 'Weblog Status', NULL, 'admin', 'admin'),
  (4, 0, 0, 'Disabled', NULL, 'admin', 'admin'),
  (4, 1, 1, 'Enabled', NULL, 'admin', 'admin');

INSERT INTO LOOKUP (lookup_id, lookup_key, lookup_sort, lookup_value, lookup_description, lookup_created_by, lookup_updated_by)
VALUES
  (5, -1, -1, 'Weblog Account Role', NULL, 'admin', 'admin'),
  (5, 0, 0, 'None', NULL, 'admin', 'admin'),
  (5, 1, 1, 'View', NULL, 'admin', 'admin'),
  (5, 2, 2, 'Edit', NULL, 'admin', 'admin'),
  (5, 3, 3, 'Publish', NULL, 'admin', 'admin'),
  (5, 4, 4, 'Manage', NULL, 'admin', 'admin');

INSERT INTO LOOKUP (lookup_id, lookup_key, lookup_sort, lookup_value, lookup_description, lookup_created_by, lookup_updated_by)
VALUES
  (6, -1, -1, 'Blog Status', NULL, 'admin', 'admin'),
  (6, 0, 0, 'Draft', NULL, 'admin', 'admin'),
  (6, 1, 1, 'Review', NULL, 'admin', 'admin'),
  (6, 2, 2, 'Approve', NULL, 'admin', 'admin'),
  (6, 3, 3, 'Published', NULL, 'admin', 'admin');

INSERT INTO LOOKUP (lookup_id, lookup_key, lookup_sort, lookup_value, lookup_description, lookup_created_by, lookup_updated_by)
VALUES
  (7, -1, -1, 'Blog Tags', NULL, 'admin', 'admin'),
  (7, 1, 1, 'Tag One', NULL, 'admin', 'admin'),
  (7, 2, 2, 'Tag Two', NULL, 'admin', 'admin');

INSERT INTO LOOKUP (lookup_id, lookup_key, lookup_sort, lookup_value, lookup_description, lookup_created_by, lookup_updated_by)
VALUES
  (8, -1, -1, 'Blog Categories', NULL, 'admin', 'admin'),
  (8, 1, 1, 'Category One', NULL, 'admin', 'admin'),
  (8, 2, 2, 'Category Two', NULL, 'admin', 'admin');

INSERT INTO LOOKUP (lookup_id, lookup_key, lookup_sort, lookup_value, lookup_description, lookup_created_by, lookup_updated_by)
VALUES
  (9, -1, -1, 'Action Priority', NULL, 'admin', 'admin'),
  (9, 0, 0, 'Progress', NULL, 'admin', 'admin'),
  (9, 1, 1, 'Info', NULL, 'admin', 'admin'),
  (9, 2, 2, 'Event', NULL, 'admin', 'admin'),
  (9, 3, 3, 'Warning', NULL, 'admin', 'admin'),
  (9, 4, 4, 'Exception', NULL, 'admin', 'admin'),
  (9, 5, 5, 'Critical', NULL, 'admin', 'admin');

INSERT INTO LOOKUP (lookup_id, lookup_key, lookup_sort, lookup_value, lookup_description, lookup_created_by, lookup_updated_by)
VALUES
  (10, -1, -1, 'Comment Status', NULL, 'admin', 'admin'),
  (10, 0, 0, 'Draft', NULL, 'admin', 'admin'),
  (10, 1, 1, 'Published', NULL, 'admin', 'admin'),
  (10, 2, 2, 'Blocked', NULL, 'admin', 'admin');

INSERT INTO LOOKUP (lookup_id, lookup_key, lookup_sort, lookup_value, lookup_description, lookup_created_by, lookup_updated_by)
VALUES
  (11, -1, -1, 'Comment Flag', NULL, 'admin', 'admin'),
  (11, 0, 0, 'Thumbs Up', NULL, 'admin', 'admin'),
  (11, 1, 1, 'Thumbs Down', NULL, 'admin', 'admin'),
  (11, 2, 2, 'Love', NULL, 'admin', 'admin'),
  (11, 3, 3, 'Hate', NULL, 'admin', 'admin');

INSERT INTO LOOKUP (lookup_id, lookup_key, lookup_sort, lookup_value, lookup_description, lookup_created_by, lookup_updated_by)
VALUES
  (12, -1, -1, 'Feature', NULL, 'admin', 'admin'),
  (12, 0, 0, 'Login Notify', NULL, 'admin', 'admin'),
  (12, 1, 1, 'Daily Summary', NULL, 'admin', 'admin');

INSERT INTO LOOKUP (lookup_id, lookup_key, lookup_sort, lookup_value, lookup_description, lookup_created_by, lookup_updated_by)
VALUES
  (13, -1, -1, 'Notify Status', NULL, 'admin', 'admin'),
  (13, 0, 0, 'Created', NULL, 'admin', 'admin'),
  (13, 1, 1, 'Sent', NULL, 'admin', 'admin'),
  (13, 2, 2, 'Received', NULL, 'admin', 'admin'),
  (13, 3, 3, 'Archived', NULL, 'admin', 'admin');

INSERT INTO LOOKUP (lookup_id, lookup_key, lookup_sort, lookup_value, lookup_description, lookup_created_by, lookup_updated_by)
VALUES
  (14, -1, -1, 'Notify Type', NULL, 'admin', 'admin'),
  (14, 0, 0, 'System', NULL, 'admin', 'admin'),
  (14, 1, 1, 'Account', NULL, 'admin', 'admin'),
  (14, 2, 2, 'Author', NULL, 'admin', 'admin'),
  (14, 3, 3, 'Weblog', NULL, 'admin', 'admin'),
  (14, 4, 4, 'Blog', NULL, 'admin', 'admin'),
  (14, 5, 5, 'Comment', NULL, 'admin', 'admin');

INSERT INTO LOOKUP (lookup_id, lookup_key, lookup_sort, lookup_value, lookup_description, lookup_created_by, lookup_updated_by)
VALUES
  (15, -1, -1, 'History Type', NULL, 'admin', 'admin'),
  (15, 0, 0, 'System', NULL, 'admin', 'admin'),
  (15, 1, 1, 'Login', NULL, 'admin', 'admin'),
  (15, 2, 2, 'Page View', NULL, 'admin', 'admin'),
  (15, 3, 3, 'Weblog Count', NULL, 'admin', 'admin'),
  (15, 4, 4, 'Blog Count', NULL, 'admin', 'admin'),
  (15, 5, 5, 'Comment Count', NULL, 'admin', 'admin');

-- Print number of rows inserted
SELECT CONCAT('LOOKUP table created and sample data inserted (', COUNT(*), ' rows)') AS message FROM LOOKUP;

-- Table: AUTHOR
CREATE TABLE AUTHOR (
  author_id CHAR(36) NOT NULL,
  author_status INTEGER NOT NULL,
  author_name VARCHAR(100) NOT NULL,
  author_email VARCHAR(255) NOT NULL,
  author_bio TEXT,
  author_photo VARCHAR(255),
  author_created_by CHAR(36) NOT NULL,
  author_created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  author_updated_by CHAR(36) NOT NULL,
  author_updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (author_id),
  FOREIGN KEY (author_created_by) REFERENCES ACCOUNT(account_id),
  FOREIGN KEY (author_updated_by) REFERENCES ACCOUNT(account_id)
);

-- Insert sample AUTHOR data
INSERT INTO AUTHOR (author_id, author_status, author_name, author_email, author_bio, author_created_by, author_updated_by)
VALUES
  ('autho001', 1, 'John Doe', 'johndoe@example.com', 'Blogger', 'admin', 'admin');

-- Print number of rows inserted
SELECT CONCAT('AUTHOR table created and sample data inserted (', COUNT(*), ' rows)') AS message FROM AUTHOR;

-- Table: AUTHOR_ACCOUNT
CREATE TABLE AUTHOR_ACCOUNT (
  author_id CHAR(36) NOT NULL,
  account_id CHAR(36) NOT NULL,
  author_account_status INTEGER NOT NULL,
  author_account_created_by CHAR(36) NULL,
  author_account_created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  author_account_updated_by CHAR(36) NULL,
  author_account_updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (account_id, author_id),
  FOREIGN KEY (account_id) REFERENCES ACCOUNT(account_id),
  FOREIGN KEY (author_id) REFERENCES AUTHOR(author_id),
  FOREIGN KEY (author_account_created_by) REFERENCES ACCOUNT(account_id),
  FOREIGN KEY (author_account_updated_by) REFERENCES ACCOUNT(account_id)
);

-- Insert sample ACCOUNT_AUTHORS data
INSERT INTO AUTHOR_ACCOUNT (author_id, account_id, author_account_status, author_account_created_by, author_account_updated_by)
VALUES
  ('autho001', 'admin', 4, 'admin', 'admin');

-- Print number of rows inserted
SELECT CONCAT('AUTHOR_ACCOUNT table created and sample data inserted (', COUNT(*), ' rows)') AS message FROM AUTHOR_ACCOUNT;

-- Table: WEBLOG
CREATE TABLE WEBLOG (
  weblog_id CHAR(36) NOT NULL,
  weblog_status INTEGER NOT NULL,
  weblog_name VARCHAR(100) NOT NULL,
  weblog_description TEXT,
  weblog_url VARCHAR(255) NOT NULL,
  weblog_style TEXT NOT NULL,
  weblog_created_by CHAR(36) NOT NULL,
  weblog_created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  weblog_updated_by CHAR(36) NOT NULL,
  weblog_updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (weblog_id),
  FOREIGN KEY (weblog_created_by) REFERENCES ACCOUNT(account_id),
  FOREIGN KEY (weblog_updated_by) REFERENCES ACCOUNT(account_id)
);

-- Insert sample WEBLOG data
INSERT INTO WEBLOG (weblog_id, weblog_status, weblog_name, weblog_description, weblog_url, weblog_style, weblog_created_by, weblog_updated_by)
VALUES
  ('bloggability', 1, 'Bloggability', 'Bloggability tool for blogging about blogs', 'bloggability', '[]', 'admin', 'admin');

-- Print number of rows inserted
SELECT CONCAT('WEBLOG table created and sample data inserted (', COUNT(*), ' rows)') AS message FROM WEBLOG;

-- Table: WEBLOG_ACCOUNT
CREATE TABLE WEBLOG_ACCOUNT (
  weblog_id CHAR(36) NOT NULL,
  account_id CHAR(36) NOT NULL,
  weblog_account_role INTEGER NOT NULL,
  weblog_account_created_by CHAR(36) NOT NULL,
  weblog_account_created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  weblog_account_updated_by CHAR(36) NOT NULL,
  weblog_account_updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (weblog_id, account_id),
  FOREIGN KEY (weblog_id) REFERENCES WEBLOG(weblog_id),
  FOREIGN KEY (account_id) REFERENCES ACCOUNT(account_id),
  FOREIGN KEY (weblog_account_created_by) REFERENCES ACCOUNT(account_id),
  FOREIGN KEY (weblog_account_updated_by) REFERENCES ACCOUNT(account_id)
);

-- Insert sample WEBLOG_ACCOUNT data
INSERT INTO WEBLOG_ACCOUNT (weblog_id, account_id, weblog_account_role, weblog_account_created_by, weblog_account_updated_by)
VALUES
  ('bloggability', 'admin', 4, 'admin', 'admin');

-- Print number of rows inserted
SELECT CONCAT('WEBLOG_ACCOUNT table created and sample data inserted (', COUNT(*), ' rows)') AS message FROM WEBLOG_ACCOUNT;

-- Table: BLOG
CREATE TABLE BLOG (
  blog_id CHAR(36) NOT NULL,
  blog_weblog_id CHAR(36) NOT NULL,
  blog_author_id CHAR(36) NOT NULL,
  blog_status INTEGER NOT NULL,
  blog_tags VARCHAR(500),
  blog_categories VARCHAR(500),
  blog_title VARCHAR(255) NOT NULL,
  blog_url VARCHAR(255) NOT NULL,
  blog_photo VARCHAR(255),
  blog_summary TEXT,
  blog_content TEXT NOT NULL,
  blog_created_by CHAR(36) NOT NULL,
  blog_created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  blog_updated_by CHAR(36) NOT NULL,
  blog_updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  blog_publish_by CHAR(36) NOT NULL,
  blog_publish_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (blog_id),
  FOREIGN KEY (blog_weblog_id) REFERENCES WEBLOG(weblog_id),
  FOREIGN KEY (blog_author_id) REFERENCES AUTHOR(author_id),
  FOREIGN KEY (blog_created_by) REFERENCES ACCOUNT(account_id),
  FOREIGN KEY (blog_updated_by) REFERENCES ACCOUNT(account_id),
  FOREIGN KEY (blog_publish_by) REFERENCES ACCOUNT(account_id)
);

-- Insert sample BLOGS data
INSERT INTO BLOG (blog_id, blog_weblog_id, blog_author_id, blog_status, blog_tags, blog_categories, blog_title, blog_url, blog_summary, blog_content, blog_created_by, blog_updated_by, blog_publish_by)
VALUES
  ('b101', 'bloggability', 'autho001', 3, ';1;2;', ';1;2;', 'My First Blog', 'my_first_blog', 'First blog summary', 'First blog content', 'admin', 'admin', 'admin');

-- Print number of rows inserted
SELECT CONCAT('BLOG table created and sample data inserted (', COUNT(*), ' rows)') AS message FROM BLOG;

-- Table: COMMENT
CREATE TABLE COMMENT (
  comment_id CHAR(36) NOT NULL,
  comment_blog_id CHAR(36) NOT NULL,
  comment_author_id CHAR(36) NOT NULL,
  comment_status INTEGER NOT NULL,
  comment_flag INTEGER,
  comment_content TEXT NOT NULL,
  comment_created_by CHAR(36) NOT NULL,
  comment_created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  comment_updated_by CHAR(36) NOT NULL,
  comment_updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (comment_id),
  FOREIGN KEY (comment_blog_id) REFERENCES BLOG(blog_id),
  FOREIGN KEY (comment_author_id) REFERENCES AUTHOR(author_id),
  FOREIGN KEY (comment_created_by) REFERENCES ACCOUNT(account_id),
  FOREIGN KEY (comment_updated_by) REFERENCES ACCOUNT(account_id)
);

-- Insert sample COMMENT data
INSERT INTO COMMENT (comment_id, comment_blog_id, comment_author_id, comment_status, comment_flag, comment_content, comment_created_by, comment_updated_by)
VALUES
  ('c101', 'b101', 'autho001', 1, 0, 'Epic first post!', 'admin', 'admin');

-- Print number of rows inserted
SELECT CONCAT('COMMENT table created and sample data inserted (', COUNT(*), ' rows)') AS message FROM COMMENT;

-- Table: ACTION
CREATE TABLE ACTION (
  action_timestamp TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  action_priority INTEGER NOT NULL,
  action_source VARCHAR(100),
  action_account_id CHAR(36),
  action_author_id CHAR(36),
  action_weblog_id CHAR(36),
  action_blog_id CHAR(36),
  action_app_id VARCHAR(100),
  action_execution_time INTEGER NOT NULL,
  action_ip_address VARCHAR(45) NOT NULL,
  action_description TEXT
);

-- Insert sample ACTION data
INSERT INTO ACTION (action_priority, action_source, action_account_id, action_ip_address, action_description, action_execution_time)
VALUES
  (1, 'System', 'admin', 'localhost: console', 'Database initialization', 0);

-- Print number of rows inserted
SELECT CONCAT('ACTION table created and sample data inserted (', COUNT(*), ' rows)') AS message FROM ACTION;

-- Table: OPTION
CREATE TABLE OPTION (
  option_account_id CHAR(36) NOT NULL,
  option_key INTEGER NOT NULL,
  option_value TEXT,
  option_created_by CHAR(36) NOT NULL,
  option_created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  option_updated_by CHAR(36) NOT NULL,
  option_updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (option_account_id, option_key),
  FOREIGN KEY (option_account_id) REFERENCES ACCOUNT(account_id),
  FOREIGN KEY (option_created_by) REFERENCES ACCOUNT(account_id),
  FOREIGN KEY (option_updated_by) REFERENCES ACCOUNT(account_id)
);

-- Insert sample OPTION data
INSERT INTO OPTION (option_account_id, option_key, option_value, option_created_by, option_updated_by)
VALUES
  ('admin', 0, 'True', 'admin', 'admin'),
  ('admin', 1, 'True', 'admin', 'admin');

-- Print number of rows inserted
SELECT CONCAT('OPTION table created and sample data inserted (', COUNT(*), ' rows)') AS message FROM OPTION;

-- Table: NOTIFY
CREATE TABLE NOTIFY (
  notify_id CHAR(36) NOT NULL,
  notify_status INTEGER NOT NULL,
  notify_account_id CHAR(36) NOT NULL,
  notify_type INTEGER NOT NULL,
  notify_message TEXT,
  notify_created_by CHAR(36) NOT NULL,
  notify_created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  notify_updated_by CHAR(36) NOT NULL,
  notify_updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (notify_id),
  FOREIGN KEY (notify_account_id) REFERENCES ACCOUNT(account_id),
  FOREIGN KEY (notify_created_by) REFERENCES ACCOUNT(account_id),
  FOREIGN KEY (notify_updated_by) REFERENCES ACCOUNT(account_id)
);

-- Insert sample NOTIFY data
INSERT INTO NOTIFY (notify_id, notify_status, notify_account_id, notify_type, notify_message, notify_created_by, notify_updated_by)
VALUES
  ('n101', 0, 'admin', 0, 'Database initialized', 'admin', 'admin');

-- Print number of rows inserted
SELECT CONCAT('NOTIFY table created and sample data inserted (', COUNT(*), ' rows)') AS message FROM NOTIFY;

-- Table: HISTORY
CREATE TABLE HISTORY (
  history_id CHAR(36) NOT NULL,
  history_type INTEGER NOT NULL,
  history_period VARCHAR(100) NOT NULL,
  history_count INTEGER NOT NULL,
  history_account_id CHAR(36),
  history_author_id CHAR(36),
  history_weblog_id CHAR(36),
  history_blog_id CHAR(36),
  history_comment_id CHAR(36),
  history_created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Insert sample HISTORY data
INSERT INTO HISTORY (history_id, history_type, history_period, history_count)
VALUES
  ('h101', 0, 'Initialization', 1);

-- Print number of rows inserted
SELECT CONCAT('HISTORY table created and sample data inserted (', COUNT(*), ' rows)') AS message FROM HISTORY;

-- Table: TOKEN
CREATE TABLE TOKEN (
  token VARCHAR(8000) NOT NULL,
  expires_at TIMESTAMP NOT NULL,
  issued_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  issued_by CHAR(36) NOT NULL,
  issued_for CHAR(36) NOT NULL,
  FOREIGN KEY (issued_for) REFERENCES ACCOUNT(account_id)
);

-- Insert sample TOKEN data
INSERT INTO TOKEN (token, expires_at, issued_by, issued_for)
VALUES
  ('Sample JWT', DATE_ADD(NOW(), INTERVAL 1 HOUR), 'admin', 'admin');

-- Print number of rows inserted
SELECT CONCAT('TOKEN table created and sample data inserted (', COUNT(*), ' rows)') AS message FROM TOKEN;

-- Table: APIKEY
CREATE TABLE APIKEY (
  apikey VARCHAR(40) NOT NULL,
  app_id VARCHAR(100) NOT NULL,
  issued_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  issued_by CHAR(36) NOT NULL,
  expires_at TIMESTAMP NOT NULL,
  expires_by CHAR(36) NOT NULL,
  PRIMARY KEY (apikey),
  FOREIGN KEY (issued_by) REFERENCES ACCOUNT(account_id)
);

-- Insert sample APIKEY data
INSERT INTO APIKEY (apikey, app_id, issued_by, expires_at, expires_by)
VALUES
  ('Test-APIKEY', 'bloggability', 'admin', DATE_ADD(NOW(), INTERVAL 1 YEAR), 'admin');

-- Print number of rows inserted
SELECT CONCAT('APIKEY table created and sample data inserted (', COUNT(*), ' rows)') AS message FROM APIKEY;

-- Print final message
SELECT 'DATABASE INITIALIZED';
