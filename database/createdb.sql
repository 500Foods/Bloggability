-- Drop tables if they exist
DROP TABLE IF EXISTS WEBLOG_ACCOUNTS;
DROP TABLE IF EXISTS WEBLOG;
DROP TABLE IF EXISTS COMMENTS;
DROP TABLE IF EXISTS ACTIONS;
DROP TABLE IF EXISTS LOOKUP;
DROP TABLE IF EXISTS ACCOUNT_AUTHORS;
DROP TABLE IF EXISTS ACCOUNTS;
DROP TABLE IF EXISTS BLOGS;
DROP TABLE IF EXISTS AUTHORS;

-- Create the AUTHORS table
CREATE TABLE AUTHORS (
  author_id TEXT PRIMARY KEY,
  author_name TEXT NOT NULL,
  author_bio TEXT,
  author_photo TEXT,
  created_by TEXT NOT NULL,
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (created_by) REFERENCES ACCOUNTS(user_id)
);

-- Create the ACCOUNTS table
CREATE TABLE ACCOUNTS (
  user_id TEXT PRIMARY KEY,
  user_login TEXT NOT NULL UNIQUE,
  user_password_hash TEXT NOT NULL,
  user_contact TEXT,
  user_created_by TEXT NOT NULL,
  user_created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_created_by) REFERENCES ACCOUNTS(user_id)
);

-- Create the ACCOUNT_AUTHORS table
CREATE TABLE ACCOUNT_AUTHORS (
  user_id TEXT NOT NULL,
  author_id TEXT NOT NULL,
  modified_by TEXT NOT NULL,
  modified_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (user_id, author_id),
  FOREIGN KEY (user_id) REFERENCES ACCOUNTS(user_id),
  FOREIGN KEY (author_id) REFERENCES AUTHORS(author_id),
  FOREIGN KEY (modified_by) REFERENCES ACCOUNTS(user_id)
);

-- Create the BLOGS table
CREATE TABLE BLOGS (
  blog_id TEXT,
  blog_revision INTEGER,
  blog_title TEXT NOT NULL,
  blog_summary TEXT,
  blog_photo TEXT,
  blog_author TEXT NOT NULL,
  blog_tags TEXT,
  blog_data TEXT,
  blog_created_by TEXT NOT NULL,
  blog_created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  blog_modified_by TEXT NOT NULL,
  blog_modified_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  blog_published_by TEXT NOT NULL,
  blog_published_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  weblog_id TEXT NOT NULL,
  PRIMARY KEY (blog_id, blog_revision),
  FOREIGN KEY (blog_author) REFERENCES AUTHORS(author_id),
  FOREIGN KEY (blog_created_by) REFERENCES ACCOUNTS(user_id),
  FOREIGN KEY (blog_modified_by) REFERENCES ACCOUNTS(user_id),
  FOREIGN KEY (blog_published_by) REFERENCES AUTHORS(author_id),
  FOREIGN KEY (weblog_id) REFERENCES WEBLOG(weblog_id)
);

-- Create the ACTIONS table
CREATE TABLE ACTIONS (
  actions_modified_by TEXT NOT NULL,
  actions_modified_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actions_category TEXT NOT NULL,
  actions_description TEXT NOT NULL,
  actions_duration INTEGER,
  actions_address TEXT,
  FOREIGN KEY (actions_modified_by) REFERENCES ACCOUNTS(user_id)
);

-- Create the COMMENTS table
CREATE TABLE COMMENTS (
  comment_id TEXT PRIMARY KEY,
  comment_blog TEXT NOT NULL,
  comment_user_id TEXT NOT NULL,
  comment_created_by TEXT NOT NULL,
  comment_created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  comment_modified_by TEXT NOT NULL,
  comment_modified_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  comment_data TEXT NOT NULL,
  comment_flags TEXT,
  FOREIGN KEY (comment_blog) REFERENCES BLOGS(blog_id),
  FOREIGN KEY (comment_user_id) REFERENCES AUTHORS(author_id),
  FOREIGN KEY (comment_created_by) REFERENCES ACCOUNTS(user_id),
  FOREIGN KEY (comment_modified_by) REFERENCES ACCOUNTS(user_id)
);

-- Create the WEBLOG table
CREATE TABLE WEBLOG (
  weblog_id TEXT PRIMARY KEY,
  weblog_name TEXT NOT NULL,
  weblog_description TEXT,
  weblog_photo TEXT,
  weblog_created_by TEXT NOT NULL,
  weblog_created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  weblog_modified_by TEXT NOT NULL,
  weblog_modified_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (weblog_created_by) REFERENCES ACCOUNTS(user_id),
  FOREIGN KEY (weblog_modified_by) REFERENCES ACCOUNTS(user_id)
);

-- Create the WEBLOG_ACCOUNTS table
CREATE TABLE WEBLOG_ACCOUNTS (
  weblog_id TEXT NOT NULL,
  author_id TEXT NOT NULL,
  access_level INTEGER NOT NULL,
  modified_by TEXT NOT NULL,
  modified_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (weblog_id, author_id),
  FOREIGN KEY (weblog_id) REFERENCES WEBLOG(weblog_id),
  FOREIGN KEY (author_id) REFERENCES AUTHORS(author_id),
  FOREIGN KEY (modified_by) REFERENCES ACCOUNTS(user_id)
);

-- Create the LOOKUP table
CREATE TABLE LOOKUP (
  lookup_id INTEGER NOT NULL,
  lookup_key INTEGER NOT NULL,
  lookup_value TEXT NOT NULL,
  lookup_sort INTEGER,
  lookup_description TEXT,
  modified_by TEXT NOT NULL,
  modified_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (lookup_id, lookup_key),
  FOREIGN KEY (modified_by) REFERENCES ACCOUNTS(user_id)
);

-- Create USER_SETTINGS table
CREATE TABLE USER_SETTINGS (
  user_id TEXT NOT NULL,
  setting_key TEXT NOT NULL,
  setting_value TEXT,
  modified_by TEXT NOT NULL,
  modified_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (user_id, setting_key),
  FOREIGN KEY (user_id) REFERENCES ACCOUNTS(user_id),
  FOREIGN KEY (modified_by) REFERENCES ACCOUNTS(user_id)
);

-- Create NOTIFICATIONS table
CREATE TABLE NOTIFICATIONS (
  notification_id TEXT PRIMARY KEY,
  notification_user TEXT NOT NULL,
  notification_type TEXT NOT NULL,
  notification_data TEXT,
  notification_read INTEGER DEFAULT 0,
  notification_created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (notification_user) REFERENCES ACCOUNTS(user_id)
);

-- Create BLOG_ANALYTICS table
CREATE TABLE BLOG_ANALYTICS (
  blog_id TEXT NOT NULL,
  blog_revision INTEGER NOT NULL,
  analytic_key TEXT NOT NULL,
  analytic_value INTEGER DEFAULT 0,
  analytic_created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (blog_id, blog_revision, analytic_key),
  FOREIGN KEY (blog_id, blog_revision) REFERENCES BLOGS(blog_id, blog_revision)
);
~
