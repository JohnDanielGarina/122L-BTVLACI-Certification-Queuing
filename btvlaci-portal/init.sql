-- BTVLACI Queue System Database Schema

-- Queues (replaces applications + applicants)
CREATE TABLE IF NOT EXISTS queues (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    email TEXT NOT NULL,
    phone TEXT,
    certificate TEXT NOT NULL,
    queue_code TEXT UNIQUE NOT NULL,
    status TEXT CHECK(status IN ('Pending','Approved','Rejected','Scheduled')) DEFAULT 'Pending',
    schedule_date DATETIME,
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Admins (separate from applicants now)
CREATE TABLE IF NOT EXISTS admins (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    email TEXT UNIQUE NOT NULL,
    password_hash TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Activity Log
CREATE TABLE IF NOT EXISTS activity_log (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    admin_id INTEGER NOT NULL REFERENCES admins(id),
    action_type TEXT,
    target_id INTEGER,
    description TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Indexes
CREATE INDEX IF NOT EXISTS idx_queues_status ON queues(status);
CREATE INDEX IF NOT EXISTS idx_queues_code ON queues(queue_code);
CREATE INDEX IF NOT EXISTS idx_queues_email ON queues(email);

-- Trigger for updated_at
CREATE TRIGGER IF NOT EXISTS update_queues_updated_at
AFTER UPDATE ON queues
FOR EACH ROW BEGIN
    UPDATE queues SET updated_at = CURRENT_TIMESTAMP WHERE id = NEW.id;
END;
