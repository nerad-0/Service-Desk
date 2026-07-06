SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS audit_logs;
DROP TABLE IF EXISTS ticket_status_history;
DROP TABLE IF EXISTS ticket_comments;
DROP TABLE IF EXISTS tickets;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS roles;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE roles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(30) NOT NULL UNIQUE,
    label VARCHAR(80) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    role_id INT UNSIGNED NOT NULL,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    phone VARCHAR(40) NULL,
    department VARCHAR(120) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_users_email (email),
    KEY idx_users_role (role_id),
    KEY idx_users_active (is_active),
    CONSTRAINT fk_users_role FOREIGN KEY (role_id) REFERENCES roles(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description VARCHAR(255) NULL,
    color VARCHAR(20) NOT NULL DEFAULT '#2563eb',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_categories_name (name),
    KEY idx_categories_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE tickets (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id INT UNSIGNED NOT NULL,
    author_id INT UNSIGNED NOT NULL,
    assigned_to INT UNSIGNED NULL,
    title VARCHAR(160) NOT NULL,
    description TEXT NOT NULL,
    status ENUM('new', 'open', 'in_progress', 'waiting_for_user', 'resolved', 'closed') NOT NULL DEFAULT 'new',
    priority ENUM('low', 'medium', 'high', 'urgent') NOT NULL DEFAULT 'medium',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    resolved_at DATETIME NULL,
    closed_at DATETIME NULL,
    KEY idx_tickets_category (category_id),
    KEY idx_tickets_author (author_id),
    KEY idx_tickets_assigned (assigned_to),
    KEY idx_tickets_status (status),
    KEY idx_tickets_priority (priority),
    KEY idx_tickets_created_at (created_at),
    FULLTEXT KEY ft_tickets_title_description (title, description),
    CONSTRAINT fk_tickets_category FOREIGN KEY (category_id) REFERENCES categories(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT fk_tickets_author FOREIGN KEY (author_id) REFERENCES users(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT fk_tickets_assigned FOREIGN KEY (assigned_to) REFERENCES users(id)
        ON UPDATE CASCADE
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE ticket_comments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ticket_id INT UNSIGNED NOT NULL,
    author_id INT UNSIGNED NOT NULL,
    body TEXT NOT NULL,
    is_internal TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_comments_ticket (ticket_id),
    KEY idx_comments_author (author_id),
    CONSTRAINT fk_comments_ticket FOREIGN KEY (ticket_id) REFERENCES tickets(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT fk_comments_author FOREIGN KEY (author_id) REFERENCES users(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE ticket_status_history (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ticket_id INT UNSIGNED NOT NULL,
    changed_by INT UNSIGNED NOT NULL,
    old_status VARCHAR(40) NULL,
    new_status VARCHAR(40) NOT NULL,
    note VARCHAR(255) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_history_ticket (ticket_id),
    KEY idx_history_changed_by (changed_by),
    CONSTRAINT fk_history_ticket FOREIGN KEY (ticket_id) REFERENCES tickets(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT fk_history_user FOREIGN KEY (changed_by) REFERENCES users(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE audit_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    actor_id INT UNSIGNED NULL,
    action VARCHAR(80) NOT NULL,
    entity_type VARCHAR(80) NOT NULL,
    entity_id INT UNSIGNED NULL,
    metadata JSON NULL,
    ip_address VARCHAR(45) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_audit_actor (actor_id),
    KEY idx_audit_entity (entity_type, entity_id),
    KEY idx_audit_created_at (created_at),
    CONSTRAINT fk_audit_actor FOREIGN KEY (actor_id) REFERENCES users(id)
        ON UPDATE CASCADE
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

