CREATE TABLE IF NOT EXISTS career_jobs (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 title VARCHAR(160) NOT NULL, department VARCHAR(100) NOT NULL,
 employment_type VARCHAR(40) NOT NULL, location VARCHAR(160) NOT NULL,
 summary VARCHAR(500) NOT NULL, description TEXT NOT NULL,
 responsibilities TEXT NOT NULL, qualifications TEXT NOT NULL,
 skills TEXT NOT NULL, benefits TEXT NOT NULL,
 deadline DATE NULL, status ENUM('draft','published','archived') NOT NULL DEFAULT 'draft',
 created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL,
 INDEX public_jobs (status, deadline)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS career_applications (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 job_id INT UNSIGNED NOT NULL, position_title VARCHAR(160) NOT NULL,
 full_name VARCHAR(160) NOT NULL, email VARCHAR(254) NOT NULL, phone VARCHAR(32) NOT NULL,
 message TEXT NOT NULL, status ENUM('New','Reviewing','Shortlisted','Interview','Hired','Rejected') NOT NULL DEFAULT 'New',
 internal_notes TEXT NOT NULL, archived_at DATETIME NULL,
 resume_name VARCHAR(200) NOT NULL, resume_mime VARCHAR(100) NOT NULL,
 resume_size INT UNSIGNED NOT NULL,
 request_key CHAR(64) NOT NULL UNIQUE, ip_hash CHAR(64) NOT NULL,
 created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL,
 CONSTRAINT career_application_job FOREIGN KEY (job_id) REFERENCES career_jobs(id) ON DELETE RESTRICT,
 INDEX application_job (job_id), INDEX application_status (archived_at,status),
 INDEX application_rate (ip_hash,created_at), INDEX application_email (email,created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS career_email_deliveries (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, application_id INT UNSIGNED NOT NULL,
 audience ENUM('hr','applicant') NOT NULL, recipient VARCHAR(254) NOT NULL,
 status ENUM('pending','sending','sent','failed') NOT NULL DEFAULT 'pending',
 attempts INT UNSIGNED NOT NULL DEFAULT 0, last_error VARCHAR(255) NULL,
 updated_at DATETIME NOT NULL,
 UNIQUE KEY delivery_recipient (application_id,audience,recipient),
 CONSTRAINT career_email_application FOREIGN KEY (application_id) REFERENCES career_applications(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS career_resume_chunks (
 application_id INT UNSIGNED NOT NULL, chunk_index SMALLINT UNSIGNED NOT NULL,
 content MEDIUMBLOB NOT NULL, PRIMARY KEY (application_id,chunk_index),
 CONSTRAINT career_resume_application FOREIGN KEY (application_id) REFERENCES career_applications(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;