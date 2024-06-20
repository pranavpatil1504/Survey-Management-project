CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL UNIQUE,
    employee_id INT NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    registration_ip VARCHAR(45),
    security_question VARCHAR(255),
    security_answer VARCHAR(255)
);

CREATE TABLE user_login_history (
    history_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    login_timestamp DATETIME,
    ip_address VARCHAR(45),
    username VARCHAR(255) NOT NULL
);

CREATE TABLE user_session_token (
    user_session_token_id INT AUTO_INCREMENT PRIMARY KEY UNIQUE,
    username VARCHAR(255) NOT NULL UNIQUE,
    session_token VARCHAR(64) NOT NULL,
    expiration_time INT NOT NULL
);

CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_name VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    session_token VARCHAR(255) DEFAULT NULL
);

ALTER TABLE users
ADD COLUMN security_question VARCHAR(255) NOT NULL,
ADD COLUMN security_answer VARCHAR(255) NOT NULL;

