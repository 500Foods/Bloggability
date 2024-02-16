-- Drop existing tables
DROP TABLE IF EXISTS ACCOUNTS;
DROP TABLE IF EXISTS AUTHORS;
DROP TABLE IF EXISTS ACCOUNT_AUTHORS;
DROP TABLE IF EXISTS BLOGS;
DROP TABLE IF EXISTS ACTIONS;
DROP TABLE IF EXISTS COMMENTS;
DROP TABLE IF EXISTS WEBLOG; 
DROP TABLE IF EXISTS WEBLOG_ACCOUNTS;
DROP TABLE IF EXISTS LOOKUP;
DROP TABLE IF EXISTS SETTINGS;
DROP TABLE IF EXISTS NOTIFICATIONS;
DROP TABLE IF EXISTS ANALYTICS;
DROP TABLE IF EXISTS CATEGORIES;

-- ACCOUNTS table
CREATE TABLE ACCOUNTS (
  account_id TEXT PRIMARY KEY,
  account_name TEXT NOT NULL,
  account_password TEXT NOT NULL,  
  account_email TEXT NOT NULL,
  account_created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Insert sample ACCOUNTS data
INSERT INTO ACCOUNTS (account_id, account_name, account_password, account_email)
VALUES
  ('acc001', 'john', 'password123', 'john@example.com');

-- Print number of rows inserted
SELECT 'ACCOUNTS table created and sample data inserted (' || changes() || ' rows)';

-- AUTHORS table  
CREATE TABLE AUTHORS (
  author_id TEXT PRIMARY KEY,
  author_name TEXT NOT NULL,
  author_bio TEXT,
  author_photo TEXT,
  author_created_by TEXT NOT NULL,
  author_created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (author_created_by) REFERENCES ACCOUNTS(account_id)
);

-- Insert sample AUTHORS data
INSERT INTO AUTHORS (author_id, author_name, author_bio, author_created_by)
VALUES
  ('auth001', 'John Doe', 'Travel blogger', 'acc001');
  
-- Print number of rows inserted  
SELECT 'AUTHORS table created and sample data inserted (' || changes() || ' rows)';

-- ACCOUNT_AUTHORS table
CREATE TABLE ACCOUNT_AUTHORS (
  account_id TEXT NOT NULL,
  author_id TEXT NOT NULL,
  account_author_modified_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (account_id, author_id),
  FOREIGN KEY (account_id) REFERENCES ACCOUNTS(account_id),
  FOREIGN KEY (author_id) REFERENCES AUTHORS(author_id)  
);

-- Insert sample ACCOUNT_AUTHORS data
INSERT INTO ACCOUNT_AUTHORS (account_id, author_id)
VALUES
  ('acc001', 'auth001');

-- Print number of rows inserted
SELECT 'ACCOUNT_AUTHORS table created and sample data inserted (' || changes() || ' rows)';

-- BLOGS table
CREATE TABLE BLOGS (
  blog_id TEXT PRIMARY KEY, 
  blog_title TEXT NOT NULL,
  blog_summary TEXT,
  blog_photo TEXT,
  blog_author TEXT NOT NULL,
  blog_tags TEXT, 
  blog_content TEXT NOT NULL,
  blog_url TEXT NOT NULL,  
  blog_created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  blog_updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  blog_published_at TEXT,
  blog_category TEXT NOT NULL,
  weblog_id TEXT NOT NULL,
  FOREIGN KEY (blog_author) REFERENCES AUTHORS(author_id),
  FOREIGN KEY (weblog_id) REFERENCES WEBLOG(weblog_id)
);

-- Insert sample BLOGS data
INSERT INTO BLOGS (blog_id, blog_title, blog_summary, blog_author, blog_content, blog_url, blog_category, weblog_id)
VALUES
  ('blog001', 'My Trip to Paris', 'Highlights from my recent trip to Paris', 'auth001', 'Blog content here...', '/blog/paris-trip', 'Travel', 'web001');

-- Print number of rows inserted  
SELECT 'BLOGS table created and sample data inserted (' || changes() || ' rows)';

-- ACTIONS table
CREATE TABLE ACTIONS (
  action_id TEXT PRIMARY KEY,
  action_description TEXT NOT NULL, 
  action_timestamp TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  action_address TEXT   
);

-- Insert sample ACTIONS data
INSERT INTO ACTIONS (action_id, action_description, action_address)
VALUES
  ('act001', 'Page view', '127.0.0.1');

-- Print number of rows inserted
SELECT 'ACTIONS table created and sample data inserted (' || changes() || ' rows)';
  
-- COMMENTS table
CREATE TABLE COMMENTS (
  comment_id TEXT PRIMARY KEY,
  comment_blog TEXT NOT NULL,
  comment_author TEXT NOT NULL,
  comment_content TEXT NOT NULL, 
  comment_created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  comment_updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (comment_blog) REFERENCES BLOGS(blog_id),
  FOREIGN KEY (comment_author) REFERENCES AUTHORS(author_id)
);

-- Insert sample COMMENTS data
INSERT INTO COMMENTS (comment_id, comment_blog, comment_author, comment_content) 
VALUES
  ('comm001', 'blog001', 'auth001', 'Great post about Paris!');
  
-- Print number of rows inserted
SELECT 'COMMENTS table created and sample data inserted (' || changes() || ' rows)';

-- WEBLOG table
CREATE TABLE WEBLOG (
  weblog_id TEXT PRIMARY KEY,
  weblog_name TEXT NOT NULL,
  weblog_description TEXT,
  weblog_url TEXT NOT NULL,
  weblog_created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  weblog_updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Insert sample WEBLOG data
INSERT INTO WEBLOG (weblog_id, weblog_name, weblog_description, weblog_url)
VALUES
  ('web001', 'My Travel Blog', 'John Doe''s travel blog', 'https://www.mytravelblog.com');
  
-- Print number of rows inserted  
SELECT 'WEBLOG table created and sample data inserted (' || changes() || ' rows)';

-- WEBLOG_ACCOUNTS table
CREATE TABLE WEBLOG_ACCOUNTS (
  weblog_id TEXT NOT NULL,
  account_id TEXT NOT NULL,
  weblog_account_role TEXT NOT NULL,
  weblog_account_created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (weblog_id, account_id),
  FOREIGN KEY (weblog_id) REFERENCES WEBLOG(weblog_id),
  FOREIGN KEY (account_id) REFERENCES ACCOUNTS(account_id)
);

-- Insert sample WEBLOG_ACCOUNTS data 
INSERT INTO WEBLOG_ACCOUNTS (weblog_id, account_id, weblog_account_role)
VALUES
  ('web001', 'acc001', 'Owner');

-- Print number of rows inserted
SELECT 'WEBLOG_ACCOUNTS table created and sample data inserted (' || changes() || ' rows)';

-- LOOKUP table
CREATE TABLE LOOKUP (
  lookup_id INTEGER PRIMARY KEY,
  lookup_key TEXT NOT NULL,
  lookup_value TEXT NOT NULL, 
  lookup_created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  lookup_updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP  
);

-- Insert sample LOOKUP data
INSERT INTO LOOKUP (lookup_id, lookup_key, lookup_value)
VALUES
  (1, 'blog_status', 'published');

-- Print number of rows inserted
SELECT 'LOOKUP table created and sample data inserted (' || changes() || ' rows)';
  
-- SETTINGS table  
CREATE TABLE SETTINGS (
  account_id TEXT NOT NULL,
  setting_key TEXT NOT NULL, 
  setting_value TEXT,
  setting_updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (account_id, setting_key),
  FOREIGN KEY (account_id) REFERENCES ACCOUNTS(account_id)
);

-- Insert sample SETTINGS data
INSERT INTO SETTINGS (account_id, setting_key, setting_value)
VALUES
  ('acc001', 'theme', 'dark');
  
-- Print number of rows inserted
SELECT 'SETTINGS table created and sample data inserted (' || changes() || ' rows)';

-- NOTIFICATIONS table
CREATE TABLE NOTIFICATIONS (
  notification_id TEXT PRIMARY KEY,
  notification_account TEXT NOT NULL,
  notification_type TEXT NOT NULL,
  notification_data TEXT, 
  notification_read INT DEFAULT 0, 
  notification_created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (notification_account) REFERENCES ACCOUNTS(account_id)
); 

-- Insert sample NOTIFICATIONS data
INSERT INTO NOTIFICATIONS (notification_id, notification_account, notification_type)
VALUES 
  ('notif001', 'acc001', 'comment');
  
-- Print number of rows inserted  
SELECT 'NOTIFICATIONS table created and sample data inserted (' || changes() || ' rows)';

-- ANALYTICS table
CREATE TABLE ANALYTICS (
  blog_id TEXT NOT NULL,
  analytic_key TEXT NOT NULL,
  analytic_value INT DEFAULT 0,
  analytic_created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (blog_id, analytic_key),
  FOREIGN KEY (blog_id) REFERENCES BLOGS(blog_id)
);

-- Insert sample ANALYTICS data
INSERT INTO ANALYTICS (blog_id, analytic_key, analytic_value) 
VALUES
  ('blog001', 'views', 10);
  
-- Print number of rows inserted
SELECT 'ANALYTICS table created and sample data inserted (' || changes() || ' rows)';

-- CATEGORIES table
CREATE TABLE CATEGORIES (
  category_id TEXT PRIMARY KEY,
  category_name TEXT NOT NULL UNIQUE 
);

-- Insert sample CATEGORIES data
INSERT INTO CATEGORIES (category_id, category_name)
VALUES
  ('cat001', 'Travel');
  
-- Print number of rows inserted  
SELECT 'CATEGORIES table created and sample data inserted (' || changes() || ' rows)';
```
