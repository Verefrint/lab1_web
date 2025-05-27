-- DROP TABLE IF EXISTS legal_services.services

USE legal_services;

-- Services table (equivalent to products table in the example)
CREATE TABLE IF NOT EXISTS services (
    id int(11)  AUTO_INCREMENT,
    category_id smallint(6) ,
    name varchar(255) ,
    alias varchar(255) ,
    short_description text ,
    description text ,
    price decimal(20,2) ,
    image varchar(255) ,
    available smallint(1)  DEFAULT '1',
    meta_keywords varchar(255) ,
    meta_description varchar(255) ,
    meta_title varchar(255) ,
    PRIMARY KEY (id),
    UNIQUE KEY id (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Service categories table
CREATE TABLE IF NOT EXISTS service_categories (
    id int(11)  AUTO_INCREMENT,
    name varchar(255) ,
    description text,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Service properties table (for additional service options)
CREATE TABLE IF NOT EXISTS service_properties (
    id int(11)  AUTO_INCREMENT,
    service_id int(11) ,
    property_name varchar(255) ,
    property_value varchar(255) ,
    property_price decimal(20,2) ,
    PRIMARY KEY (id),
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Service images table
CREATE TABLE IF NOT EXISTS service_images (
    id int(11)  AUTO_INCREMENT,
    service_id int(11) ,
    image varchar(255) ,
    title varchar(255) ,
    PRIMARY KEY (id),
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Contacts table (for contact form submissions)
CREATE TABLE IF NOT EXISTS contacts (
    id int(11)  AUTO_INCREMENT,
    name varchar(255) ,
    email varchar(255) ,
    phone varchar(50),
    subject varchar(255) ,
    message text ,
    submission_date datetime  DEFAULT CURRENT_TIMESTAMP,
    status enum('new','in_progress','completed')  DEFAULT 'new',
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Newsletter subscriptions
CREATE TABLE IF NOT EXISTS newsletter (
    id int(11)  AUTO_INCREMENT,
    email varchar(255) ,
    active tinyint(1)  DEFAULT '1',
    PRIMARY KEY (id),
    UNIQUE KEY email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- User accounts for login
CREATE TABLE IF NOT EXISTS users (
    id int(11)  AUTO_INCREMENT,
    username varchar(50) ,
    password varchar(255) ,
    email varchar(100), 
    created_at datetime DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS feedback (
    id int(11) AUTO_INCREMENT,
    name varchar(255),
    email varchar(255),
    rating tinyint(1),
    services varchar(255),
    lawyer varchar(50),
    message text,
    improvements varchar(255),
    submission_date datetime DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
