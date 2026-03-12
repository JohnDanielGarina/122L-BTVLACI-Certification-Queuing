-- BTVLACI Database Schema

-- Applicants
CREATE TABLE IF NOT EXISTS applicants (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    email TEXT UNIQUE NOT NULL,
    password_hash TEXT NOT NULL,
    contact_number TEXT,
    role TEXT CHECK(role IN ('applicant','admin')) DEFAULT 'applicant',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Applications
CREATE TABLE IF NOT EXISTS applications (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    applicant_id INTEGER NOT NULL REFERENCES applicants(id) ON DELETE CASCADE,
    qualification TEXT CHECK(qualification IN ('NCII','NCIII')),
    status TEXT CHECK(status IN ('Pending','Incomplete','Approved','Rejected','Scheduled')) DEFAULT 'Pending',
    application_code TEXT UNIQUE NOT NULL,
    batch_id INTEGER REFERENCES batches(id),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    doc_deadline DATETIME
);

-- Documents
CREATE TABLE IF NOT EXISTS documents (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    application_id INTEGER NOT NULL REFERENCES applications(id) ON DELETE CASCADE,
    type TEXT CHECK(type IN ('id_card','photo_2x2','nc2_certificate')),
    file_path TEXT NOT NULL,
    mime_type TEXT,
    size_bytes INTEGER,
    uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    delete_after DATETIME
);

-- Batches
CREATE TABLE IF NOT EXISTS batches (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    batch_code TEXT UNIQUE NOT NULL,
    qualification TEXT CHECK(qualification IN ('NCII','NCIII')),
    schedule_datetime DATETIME,
    min_size INTEGER DEFAULT 10,
    max_size INTEGER,
    status TEXT CHECK(status IN ('Open','Scheduled','Completed','Cancelled')) DEFAULT 'Open',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Activity Log
CREATE TABLE IF NOT EXISTS activity_log (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    admin_id INTEGER NOT NULL REFERENCES applicants(id),
    action_type TEXT,
    target_type TEXT,
    target_id INTEGER,
    description TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Indexes
CREATE INDEX IF NOT EXISTS idx_applications_status ON applications(status);
CREATE INDEX IF NOT EXISTS idx_applications_applicant ON applications(applicant_id);
CREATE INDEX IF NOT EXISTS idx_applications_code ON applications(application_code);
CREATE INDEX IF NOT EXISTS idx_batches_code ON batches(batch_code);
CREATE INDEX IF NOT EXISTS idx_documents_app ON documents(application_id);

-- Initial Admin User (admin@btvlaci-portal.local / admin123)
INSERT OR IGNORE INTO applicants (email, password_hash, role, name) VALUES 
('admin@btvlaci-portal.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'Admin User'); -- password_hash('admin123')

-- Trigger for updated_at
CREATE TRIGGER IF NOT EXISTS update_applications_updated_at 
AFTER UPDATE ON applications 
FOR EACH ROW BEGIN
    UPDATE applications SET updated_at = CURRENT_TIMESTAMP WHERE id = NEW.id;
END;

