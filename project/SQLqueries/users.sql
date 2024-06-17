CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL UNIQUE,
    hashed_pass VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    registration_ip VARCHAR(45) NOT NULL,
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_username (username)
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


