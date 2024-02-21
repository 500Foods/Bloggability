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



-- Table: ACCOUNT 
CREATE TABLE ACCOUNT (
  account_id TEXT NOT NULL,
  account_status INTEGER NOT NULL,
  account_name TEXT NOT NULL,
  account_password_hash TEXT NOT NULL,  
  account_email TEXT NOT NULL,
  account_created_by TEXT NOT NULL,
  account_created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  account_updated_by TEXT NOT NULL,
  account_updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY(account_id)
);

-- Insert sample ACCOUNT data
INSERT INTO ACCOUNT (account_id, account_status, account_name, account_password_hash, account_email, account_created_by, account_updated_by)
VALUES
  ('admin', 1, 'Administrator', 'password', 'administrator@example.com', 'admin', 'admin');

-- Print number of rows inserted
SELECT 'ACCOUNT table created and sample data inserted (' || changes() || ' rows)';



-- Table: LOOKUP 
CREATE TABLE LOOKUP (
  lookup_id INTEGER NOT NULL,
  lookup_key INTEGER NOT NULL,
  lookup_sort INTEGER NOT NULL,
  lookup_value TEXT, 
  lookup_description TEXT, 
  lookup_created_by TEXT NOT NULL,
  lookup_created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  lookup_updated_by TEXT NOT NULL,
  lookup_updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY(lookup_id, lookup_key)
);

-- Insert sample LOOKUP data
INSERT INTO LOOKUP (lookup_id, lookup_key, lookup_sort, lookup_value, lookup_created_by, lookup_updated_by)
VALUES
  (1, -1, -1, 'Account Status', 'admin', 'admin'),
  (1,  0,  0, 'Disabled', 'admin', 'admin'),
  (1,  1,  1, 'Active', 'admin', 'admin'),
  (1,  2,  2, 'Subscriber', 'admin', 'admin');

INSERT INTO LOOKUP (lookup_id, lookup_key, lookup_sort, lookup_value, lookup_created_by, lookup_updated_by)
VALUES
  (2, -1, -1, 'Author Status', 'admin', 'admin'),
  (2,  0,  0, 'Disabled', 'admin', 'admin'),
  (2,  1,  1, 'Active', 'admin', 'admin');

INSERT INTO LOOKUP (lookup_id, lookup_key, lookup_sort, lookup_value, lookup_created_by, lookup_updated_by)
VALUES
  (3, -1, -1, 'Author Account Status', 'admin', 'admin'),
  (3,  0,  0, 'None', 'admin', 'admin'),
  (3,  1,  1, 'View', 'admin', 'admin'),
  (3,  2,  2, 'Edit', 'admin', 'admin'),
  (3,  3,  3, 'Publish', 'admin', 'admin'),
  (3,  4,  4, 'Manage', 'admin', 'admin');

INSERT INTO LOOKUP (lookup_id, lookup_key, lookup_sort, lookup_value, lookup_created_by, lookup_updated_by)
VALUES
  (4, -1, -1, 'Weblog Status', 'admin', 'admin'),
  (4,  0,  0, 'Disabled', 'admin', 'admin'),
  (4,  1,  1, 'Enabled', 'admin', 'admin');

INSERT INTO LOOKUP (lookup_id, lookup_key, lookup_sort, lookup_value, lookup_created_by, lookup_updated_by)
VALUES
  (5, -1, -1, 'Weblog Account Role', 'admin', 'admin'),
  (5,  0,  0, 'None', 'admin', 'admin'),
  (5,  1,  1, 'View', 'admin', 'admin'),
  (5,  2,  2, 'Edit', 'admin', 'admin'),
  (5,  3,  3, 'Publish', 'admin', 'admin'),
  (5,  4,  4, 'Manage', 'admin', 'admin');

INSERT INTO LOOKUP (lookup_id, lookup_key, lookup_sort, lookup_value, lookup_created_by, lookup_updated_by)
VALUES
  (6, -1, -1, 'Blog Status', 'admin', 'admin'),
  (6,  0,  0, 'Draft', 'admin', 'admin'),
  (6,  1,  1, 'Review', 'admin', 'admin'),
  (6,  2,  2, 'Approve', 'admin', 'admin'),
  (6,  3,  3, 'Published', 'admin', 'admin');

INSERT INTO LOOKUP (lookup_id, lookup_key, lookup_sort, lookup_value, lookup_created_by, lookup_updated_by)
VALUES
  (7, -1, -1, 'Blog Tags', 'admin', 'admin'),
  (7,  1,  1, 'Tag One', 'admin', 'admin'),
  (7,  2,  2, 'Tag Two', 'admin', 'admin');

INSERT INTO LOOKUP (lookup_id, lookup_key, lookup_sort, lookup_value, lookup_created_by, lookup_updated_by)
VALUES
  (8, -1, -1, 'Blog Categories', 'admin', 'admin'),
  (8,  1,  1, 'Category One', 'admin', 'admin'),
  (8,  2,  2, 'Category Two', 'admin', 'admin');

INSERT INTO LOOKUP (lookup_id, lookup_key, lookup_sort, lookup_value, lookup_created_by, lookup_updated_by)
VALUES
  (9, -1, -1, 'Action Priority', 'admin', 'admin'),
  (9,  0,  0, 'Progress', 'admin', 'admin'),
  (9,  1,  1, 'Info', 'admin', 'admin'),
  (9,  2,  2, 'Event', 'admin', 'admin'),
  (9,  3,  3, 'Warning', 'admin', 'admin'),
  (9,  4,  4, 'Exception', 'admin', 'admin'),
  (9,  5,  5, 'Critical', 'admin', 'admin');

INSERT INTO LOOKUP (lookup_id, lookup_key, lookup_sort, lookup_value, lookup_created_by, lookup_updated_by)
VALUES
  (10, -1, -1, 'Comment Status', 'admin', 'admin'),
  (10,  0,  0, 'Draft', 'admin', 'admin'),
  (10,  1,  1, 'Published', 'admin', 'admin'),
  (10,  2,  2, 'Blocked', 'admin', 'admin');

INSERT INTO LOOKUP (lookup_id, lookup_key, lookup_sort, lookup_value, lookup_created_by, lookup_updated_by)
VALUES
  (11, -1, -1, 'Comment Flag', 'admin', 'admin'),
  (11,  0,  0, 'Thumbs Up', 'admin', 'admin'),
  (11,  1,  1, 'Thumbs Down', 'admin', 'admin'),
  (11,  2,  2, 'Love', 'admin', 'admin'),
  (11,  3,  3, 'Hate', 'admin', 'admin');

INSERT INTO LOOKUP (lookup_id, lookup_key, lookup_sort, lookup_value, lookup_created_by, lookup_updated_by)
VALUES
  (12, -1, -1, 'Feature', 'admin', 'admin'),
  (12,  0,  0, 'Login Notify', 'admin', 'admin'),
  (12,  1,  1, 'Daily Summary', 'admin', 'admin');

INSERT INTO LOOKUP (lookup_id, lookup_key, lookup_sort, lookup_value, lookup_created_by, lookup_updated_by)
VALUES
  (13, -1, -1, 'Notify Status', 'admin', 'admin'),
  (13,  0,  0, 'Created', 'admin', 'admin'),
  (13,  1,  1, 'Sent', 'admin', 'admin'),
  (13,  2,  2, 'Received', 'admin', 'admin'),
  (13,  3,  3, 'Archived', 'admin', 'admin');

INSERT INTO LOOKUP (lookup_id, lookup_key, lookup_sort, lookup_value, lookup_created_by, lookup_updated_by)
VALUES
  (14, -1, -1, 'Notify Type', 'admin', 'admin'),
  (14,  0,  0, 'System', 'admin', 'admin'),
  (14,  1,  1, 'Account', 'admin', 'admin'),
  (14,  2,  2, 'Author', 'admin', 'admin'),
  (14,  3,  3, 'Weblog', 'admin', 'admin'),
  (14,  4,  4, 'Blog', 'admin', 'admin'),
  (14,  5,  5, 'Commentt', 'admin', 'admin');

INSERT INTO LOOKUP (lookup_id, lookup_key, lookup_sort, lookup_value, lookup_created_by, lookup_updated_by)
VALUES
  (15, -1, -1, 'History Type', 'admin', 'admin'),
  (15,  0,  0, 'System', 'admin', 'admin'),
  (15,  1,  1, 'Login', 'admin', 'admin'),
  (15,  2,  2, 'Page View', 'admin', 'admin'),
  (15,  3,  3, 'Weblog Count', 'admin', 'admin'),
  (15,  4,  4, 'Blog Count', 'admin', 'admin'),
  (15,  5,  5, 'Comment Count', 'admin', 'admin');

-- Print number of rows inserted
SELECT 'LOOKUP table created and sample data inserted (' || count(*) || ' rows)' FROM LOOKUP;
  


-- Table: AUTHOR   
CREATE TABLE AUTHOR (
  author_id TEXT NOT NULL,
  author_status INTEGER NOT NULL,
  author_name TEXT NOT NULL,
  author_email TEXT NOT NULL,
  author_bio TEXT,
  author_photo TEXT,
  author_created_by TEXT NOT NULL,
  author_created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  author_updated_by TEXT NOT NULL,
  author_updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (author_id),
  FOREIGN KEY (author_created_by) REFERENCES ACCOUNT(account_id),
  FOREIGN KEY (author_updated_by) REFERENCES ACCOUNT(account_id)
);

-- Insert sample AUTHOR data
INSERT INTO AUTHOR (author_id, author_status, author_name, author_email, author_bio, author_created_by, author_updated_by)
VALUES
  ('auth001', 1, 'John Doe','johndoe@example.com', 'Blogger', 'admin', 'admin');
  
-- Print number of rows inserted  
SELECT 'AUTHOR table created and sample data inserted (' || changes() || ' rows)';



-- Table: AUTHOR_ACCOUNT 
CREATE TABLE AUTHOR_ACCOUNT (
  author_id TEXT NOT NULL,
  account_id TEXT NOT NULL,
  author_account_status INTEGER NOT NULL,
  author_account_created_by TEXT NUL NULL, 
  author_account_created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  author_account_updated_by TEXT NUL NULL, 
  author_account_updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (account_id, author_id),
  FOREIGN KEY (account_id) REFERENCES ACCOUNT(account_id),
  FOREIGN KEY (author_id) REFERENCES AUTHORS(author_id),
  FOREIGN KEY (author_account_created_by) REFERENCES ACCOUNT(account_id),
  FOREIGN KEY (author_account_updated_by) REFERENCES ACCOUNT(account_id)
);

-- Insert sample ACCOUNT_AUTHORS data
INSERT INTO AUTHOR_ACCOUNT (author_id, account_id, author_account_status, author_account_created_by, author_account_updated_by)
VALUES
  ('autho001', 'admin', 4, 'admin', 'admin');

-- Print number of rows inserted
SELECT 'AUTHOR_ACCOUNT table created and sample data inserted (' || changes() || ' rows)';



-- Table: WEBLOG 
CREATE TABLE WEBLOG (
  weblog_id TEXT NOT NULL,
  weblog_status INTEGER NOT NULL,
  weblog_name TEXT NOT NULL,
  weblog_description TEXT,
  weblog_url TEXT NOT NULL,
  weblog_style TEXT NOT NULL,
  weblog_created_by TEXT NOT NULL,
  weblog_created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  weblog_updated_by TEXT NOT NULL,
  weblog_updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY(weblog_id),
  FOREIGN KEY (weblog_created_by) REFERENCES ACCOUNT(account_id),
  FOREIGN KEY (weblog_updated_by) REFERENCES ACCOUNT(account_id)
);

-- Insert sample WEBLOG data
INSERT INTO WEBLOG (weblog_id, weblog_status, weblog_name, weblog_description, weblog_url, weblog_style, weblog_created_by, weblog_updated_by)
VALUES
  ('bloggable', 1, 'Bloggable', 'Bloggable tool for blogging about blogs', 'bloggable', '[]', 'admin', 'admin');
  
-- Print number of rows inserted  
SELECT 'WEBLOG table created and sample data inserted (' || changes() || ' rows)';



-- Table: WEBLOG_ACCOUNT
CREATE TABLE WEBLOG_ACCOUNT (
  weblog_id TEXT NOT NULL,
  account_id TEXT NOT NULL,
  weblog_account_role INTEGER NOT NULL,
  weblog_account_created_by TEXT NOT NULL,
  weblog_account_created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  weblog_account_updated_by TEXT NOT NULL,
  weblog_account_updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (weblog_id, account_id),
  FOREIGN KEY (weblog_id) REFERENCES WEBLOG(weblog_id),
  FOREIGN KEY (account_id) REFERENCES ACCOUNT(account_id),
  FOREIGN KEY (weblog_account_created_by) REFERENCES ACCOUNT(account_id),
  FOREIGN KEY (weblog_account_updated_by) REFERENCES ACCOUNT(account_id)
);

-- Insert sample WEBLOG_ACCOUNT data 
INSERT INTO WEBLOG_ACCOUNT (weblog_id, account_id, weblog_account_role, weblog_account_created_by, weblog_account_updated_by)
VALUES
  ('bloggable', 'admin', 4, 'admin', 'admin');

-- Print number of rows inserted
SELECT 'WEBLOG_ACCOUNT table created and sample data inserted (' || changes() || ' rows)';



-- Table: BLOG 
CREATE TABLE BLOG (
  blog_id TEXT NOT NULL,  
  blog_weblog_id TEXT NOT NULL,
  blog_author_id TEXT NOT NULL,
  blog_status INTEGER NOT NULL,
  blog_tags TEXT, 
  blog_categories TEXT,
  blog_title TEXT NOT NULL,
  blog_url TEXT NOT NULL,  
  blog_photo TEXT,
  blog_summary TEXT,
  blog_content TEXT NOT NULL,
  blog_created_by TEXT NOT NULL,
  blog_created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  blog_updated_by TEXT NOT NULL,
  blog_updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  blog_publish_by TEXT NOT NULL,
  blog_publish_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (blog_id),
  FOREIGN KEY (blog_weblog_id) REFERENCES WEBLOG(weblog_id),
  FOREIGN KEY (blog_author_id) REFERENCES AUTHORS(author_id),
  FOREIGN KEY (blog_created_by) REFERENCES ACCOUNT(account_id),
  FOREIGN KEY (blog_updated_by) REFERENCES ACCOUNT(account_id),
  FOREIGN KEY (blog_publish_by) REFERENCES ACCOUNT(account_id)
);

-- Insert sample BLOGS data
INSERT INTO BLOG (blog_id, blog_weblog_id, blog_author_id, blog_status, blog_tags, blog_categories, blog_title, blog_url, blog_summary, blog_content, blog_created_by, blog_updated_by, blog_publish_by)
VALUES
  ('first', 'bloggable', 'auth001', 4, ';1;2;', ';1;2;', 'My First Blog', 'my_first_blog', 'First blog summary', 'First blog content', 'admin', 'admin', 'admin');

-- Print number of rows inserted  
SELECT 'BLOG table created and sample data inserted (' || changes() || ' rows)';



-- Table: COMMENT 
CREATE TABLE COMMENT (
  comment_id TEXT NOT NULL,
  comment_blog_id TEXT NOT NULL,
  comment_author_id TEXT NOT NULL,
  comment_status INTEGER NOT NULL,
  comment_flag INTEGER,
  comment_content TEXT NOT NULL, 
  comment_created_by TEXT NOT NULL,
  comment_created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  comment_updated_by TEXT NOT NULL,
  comment_updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (comment_id),
  FOREIGN KEY (comment_blog_id) REFERENCES BLOG(blog_id),
  FOREIGN KEY (comment_author_id) REFERENCES AUTHOR(author_id),
  FOREIGN KEY (comment_created_by) REFERENCES ACCOUNT(account_id),
  FOREIGN KEY (comment_updated_by) REFERENCES ACCOUNT(account_id)
);

-- Insert sample COMMENT data
INSERT INTO COMMENT (comment_id, comment_blog_id, comment_author_id, comment_status, comment_flag, comment_content, comment_created_by, comment_updated_by) 
VALUES
  ('comm001', 'first', 'auth001', 1, 0, 'Epic first post!', 'admin', 'admin');
  
-- Print number of rows inserted
SELECT 'COMMENT table created and sample data inserted (' || changes() || ' rows)';



-- Table: ACTION 
CREATE TABLE ACTION (
  action_timestamp TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  action_priority INTEGER NOT NULL,
  action_source TEXT,
  account_id TEXT,
  author_id TEXT,
  weblog_id TEXT,
  blog_id TEXT,
  app_id TEXT,
  action_ip_address TEXT NOT NULL,
  action_description 
);

-- Insert sample ACTION data
INSERT INTO ACTION (action_priority, action_source, account_id, action_ip_address, action_description)
VALUES
  (1, 'System', 'admin', 'localhost: console', 'Database initialization');

-- Print number of rows inserted
SELECT 'ACTION table created and sample data inserted (' || changes() || ' rows)';



-- Table: OPTION   
CREATE TABLE OPTION (
  option_account_id TEXT NOT NULL,
  option_key INTEGER NOT NULL, 
  option_value TEXT,
  option_created_by TEXT NOT NULL,
  option_created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  option_updated_by TEXT NOT NULL,
  option_updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (option_account_id, option_key),
  FOREIGN KEY (option_account_id) REFERENCES ACCOUNT(account_id)
  FOREIGN KEY (option_created_by) REFERENCES ACCOUNT(account_id),
  FOREIGN KEY (option_updated_by) REFERENCES ACCOUNT(account_id)
);

-- Insert sample OPTION data
INSERT INTO OPTION (option_account_id, option_key, option_value, option_created_by, option_updated_by)
VALUES
  ('admin', 0, 'True', 'admin', 'admin'), 
  ('admin', 1, 'True', 'admin', 'admin');
  
-- Print number of rows inserted
SELECT 'OPTION table created and sample data inserted (' || changes() || ' rows)';



-- Table: NOTIFY 
CREATE TABLE NOTIFY (
  notify_id TEXT TEXT NOT NULL,
  notify_status INTEGER NOT NULL,
  notify_account_id TEXT NOT NULL,
  notify_type INTEGER NOT NULL,
  notify_message TEXT, 
  notify_created_by TEXT NOT NULL,
  notify_created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  notify_updated_by TEXT NOT NULL,
  notify_updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (notify_id),
  FOREIGN KEY (notify_account_id) REFERENCES ACCOUNT(account_id)
  FOREIGN KEY (notify_created_by) REFERENCES ACCOUNT(account_id)
  FOREIGN KEY (notify_updated_by) REFERENCES ACCOUNT(account_id)
); 

-- Insert sample NOTIFY data
INSERT INTO NOTIFY (notify_id, notify_status, notify_account_id, notify_type, notify_message, notify_created_by, notify_updated_by)
VALUES 
  ('notif001', 0, 'admin', 0, 'Database initialized', 'admin', 'admin');
  
-- Print number of rows inserted  
SELECT 'NOTIFY table created and sample data inserted (' || changes() || ' rows)';



-- Table: HISTORY 
CREATE TABLE HISTORY (
  history_id TEXT NOT NULL,
  history_type INTEGER NOT NULL,
  history_period TEXT NOT NULL,
  history_count INTEGER NOT NULL,
  history_account_id TEXT,
  history_author_id TEXT,
  history_weblog_id TEXT,
  history_blog_id TEXT,
  history_comment_id TEXT,
  history_created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (history_id)
);

-- Insert sample HISTORY data
INSERT INTO HISTORY (history_id, history_type, history_period, history_count) 
VALUES
  ('history', 0, 'Initialization', 1);
  
-- Print number of rows inserted
SELECT 'HISTORY table created and sample data inserted (' || changes() || ' rows)';



-- Table: TOKEN
CREATE TABLE TOKEN (
  token TEXT NOT NULL,
  expires_at TEXT NOT NULL,
  issued_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  issued_by TEXT NOT NULL,
  issued_for TEXT NOT NULL,
  PRIMARY KEY (token),
  FOREIGN KEY (issued_for) REFERENCES ACCOUNT(account_id)
);

-- Insert sample TOKEN data
INSERT INTO TOKEN (token, expires_at, issued_at, issued_by, issued_for)
VALUES
  ('Sample JWT', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 'admin', 'admin');

-- Print number of rows inserted
SELECT 'TOKEN table created and sample data inserted (' || changes() || ' rows)';



