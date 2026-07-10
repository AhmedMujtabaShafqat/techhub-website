-- ============================================================
--  TechHub Website — MySQL Database Schema
--  File: database/schema.sql
--
--  Run this in phpMyAdmin or MySQL CLI:
--    mysql -u root -p < database/schema.sql
-- ============================================================

CREATE DATABASE IF NOT EXISTS techhub_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE techhub_db;

-- ── Contact Form Submissions ──────────────────────────────────
CREATE TABLE IF NOT EXISTS contact_submissions (
    id              INT UNSIGNED     AUTO_INCREMENT PRIMARY KEY,
    first_name      VARCHAR(100)     NOT NULL,
    last_name       VARCHAR(100)     NOT NULL,
    email           VARCHAR(255)     NOT NULL,
    company         VARCHAR(200)     DEFAULT NULL,
    enquiry_type    ENUM(
                        'General Enquiry',
                        'Sales & Pricing',
                        'Technical Support',
                        'Partnership',
                        'Press & Media',
                        'Career Opportunity'
                    ) NOT NULL,
    message         TEXT             NOT NULL,
    consent         TINYINT(1)       NOT NULL DEFAULT 1,
    status          ENUM('new','read','replied','closed')
                                     NOT NULL DEFAULT 'new',
    ip_address      VARCHAR(45)      DEFAULT NULL,
    created_at      DATETIME         NOT NULL,
    updated_at      DATETIME         DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email      (email),
    INDEX idx_status     (status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Newsletter Subscribers ────────────────────────────────────
CREATE TABLE IF NOT EXISTS newsletter_subscribers (
    id                  INT UNSIGNED  AUTO_INCREMENT PRIMARY KEY,
    email               VARCHAR(255)  NOT NULL UNIQUE,
    status              ENUM('active','unsubscribed') NOT NULL DEFAULT 'active',
    unsubscribe_token   VARCHAR(64)   NOT NULL,
    ip_address          VARCHAR(45)   DEFAULT NULL,
    created_at          DATETIME      NOT NULL,
    updated_at          DATETIME      DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email      (email),
    INDEX idx_status     (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Blog Posts ────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS blog_posts (
    id              INT UNSIGNED     AUTO_INCREMENT PRIMARY KEY,
    title           VARCHAR(300)     NOT NULL,
    slug            VARCHAR(300)     NOT NULL UNIQUE,
    excerpt         TEXT             DEFAULT NULL,
    content         LONGTEXT         NOT NULL,
    category        ENUM('AI & ML','Cloud','Security','Development','Data')
                                     NOT NULL,
    author_name     VARCHAR(150)     NOT NULL,
    author_role     VARCHAR(150)     DEFAULT NULL,
    featured        TINYINT(1)       NOT NULL DEFAULT 0,
    status          ENUM('draft','published','archived')
                                     NOT NULL DEFAULT 'draft',
    published_at    DATETIME         DEFAULT NULL,
    created_at      DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME         DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_slug        (slug),
    INDEX idx_category    (category),
    INDEX idx_status      (status),
    INDEX idx_published   (published_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Team Members ──────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS team_members (
    id              INT UNSIGNED     AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(150)     NOT NULL,
    role            VARCHAR(150)     NOT NULL,
    department      ENUM('Leadership','Engineering','Product','Design','Business')
                                     NOT NULL,
    bio             TEXT             DEFAULT NULL,
    email           VARCHAR(255)     DEFAULT NULL,
    linkedin_url    VARCHAR(500)     DEFAULT NULL,
    twitter_url     VARCHAR(500)     DEFAULT NULL,
    github_url      VARCHAR(500)     DEFAULT NULL,
    display_order   INT              NOT NULL DEFAULT 0,
    active          TINYINT(1)       NOT NULL DEFAULT 1,
    created_at      DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_department (department),
    INDEX idx_active     (active),
    INDEX idx_order      (display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Job Listings ──────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS job_listings (
    id              INT UNSIGNED     AUTO_INCREMENT PRIMARY KEY,
    title           VARCHAR(200)     NOT NULL,
    department      ENUM('Engineering','Product & Design','Business & Operations')
                                     NOT NULL,
    location_type   ENUM('Remote','Hybrid','On-site') NOT NULL DEFAULT 'Remote',
    employment_type ENUM('Full-time','Part-time','Contract') NOT NULL DEFAULT 'Full-time',
    salary_range    VARCHAR(100)     DEFAULT NULL,
    description     TEXT             NOT NULL,
    requirements    TEXT             DEFAULT NULL,
    status          ENUM('open','closed','paused') NOT NULL DEFAULT 'open',
    created_at      DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME         DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_department (department),
    INDEX idx_status     (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Seed: Sample Blog Posts ───────────────────────────────────
INSERT INTO blog_posts (title, slug, excerpt, content, category, author_name, author_role, featured, status, published_at) VALUES
('The Rise of Agentic AI: How Autonomous Agents Are Reshaping Enterprise Workflows',
 'rise-of-agentic-ai',
 'AI agents that can plan, reason, and act are moving from research labs into production deployments.',
 'Full article content here...',
 'AI & ML', 'Dr. James Park', 'Head of AI Research', 1, 'published', '2025-11-15 09:00:00'),

('Kubernetes Cost Optimisation: 7 Strategies That Actually Work',
 'kubernetes-cost-optimisation',
 'Kubernetes clusters can quickly become expensive if left unmanaged. Here are seven impactful strategies.',
 'Full article content here...',
 'Cloud', 'Priya Sharma', 'Head of Cloud Engineering', 0, 'published', '2025-11-08 09:00:00'),

('Zero Trust Security: A Practical Implementation Guide for 2025',
 'zero-trust-security-guide',
 'Zero Trust is an architecture you build, not a product you buy.',
 'Full article content here...',
 'Security', 'Sofia Martinez', 'Head of Cybersecurity', 0, 'published', '2025-11-02 09:00:00');

-- ── Seed: Sample Team Members ─────────────────────────────────
INSERT INTO team_members (name, role, department, bio, display_order) VALUES
('Marcus Reynolds',  'Chief Executive Officer',  'Leadership',   '20+ years in enterprise technology. Previously VP Engineering at Microsoft EMEA.', 1),
('Dr. Aisha Nwosu',  'Chief Technology Officer', 'Leadership',   'PhD from Oxford. Former AI researcher at DeepMind.', 2),
('Tom Hargreaves',   'Chief Operating Officer',  'Leadership',   'Scaled three SaaS companies to Series C.', 3),
('Priya Sharma',     'Head of Cloud Engineering','Engineering',  'AWS Solutions Architect. Leads cloud migration practice.', 4),
('Dr. James Park',   'Head of AI Research',      'Engineering',  'ML researcher and author. Leads all LLM fine-tuning.', 5),
('Sofia Martinez',   'Head of Cybersecurity',    'Engineering',  'CISM certified. Former GCHQ analyst.', 6);

-- ── Seed: Sample Job Listings ─────────────────────────────────
INSERT INTO job_listings (title, department, location_type, salary_range, description, status) VALUES
('Senior Cloud Engineer (AWS)',    'Engineering',          'Remote', '₨2,500,000–₨3,400,000/yr', 'Lead cloud infrastructure design and migration projects.', 'open'),
('ML Engineer — LLM Specialisation','Engineering',         'Remote', '₨2,800,000–₨3,900,000/yr', 'Build and deploy fine-tuned large language models.', 'open'),
('Senior Full-Stack Engineer',     'Engineering',          'Remote', '₨2,300,000–₨3,200,000/yr', 'React/Node.js full-stack development for client products.', 'open'),
('Senior Product Manager',         'Product & Design',     'Remote', '₨2,500,000–₨3,400,000/yr', 'Drive product strategy across TechHub SaaS products.', 'open'),
('Account Executive (Enterprise)', 'Business & Operations','Hybrid', '₨1,900,000 + OTE',          'Build and manage enterprise client relationships.', 'open');

-- ── Done ──────────────────────────────────────────────────────
SELECT 'TechHub database schema installed successfully!' AS message;
