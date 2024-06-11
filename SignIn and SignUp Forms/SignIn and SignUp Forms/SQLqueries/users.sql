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
    ip_address VARCHAR(45)
);