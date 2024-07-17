CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL UNIQUE,
    employee_id INT NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    registration_ip VARCHAR(45),
    security_question VARCHAR(255),
    security_answer VARCHAR(255)
);

CREATE TABLE privilege (
    privilege_id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL UNIQUE,
    is_admin BOOLEAN NOT NULL,
    FOREIGN KEY (employee_id) REFERENCES users(employee_id)
        ON DELETE CASCADE  
);

CREATE TABLE user_login_history (
    history_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    login_timestamp DATETIME,
    ip_address VARCHAR(45),
    username VARCHAR(255) NOT NULL
);

CREATE TABLE user_session_token (
    user_session_token_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL UNIQUE,
    session_token VARCHAR(64) NOT NULL,
    expiration_time INT 
);

CREATE TABLE survey_questions (
    question_id INT AUTO_INCREMENT PRIMARY KEY,
    question_text VARCHAR(255) NOT NULL,
    question_type ENUM('single', 'multiple', 'limit', 'string') NOT NULL,
    other_included BOOLEAN NOT NULL DEFAULT FALSE
);

CREATE TABLE survey_options (
    option_id INT AUTO_INCREMENT PRIMARY KEY,
    question_id INT NOT NULL,
    option_text VARCHAR(255) NOT NULL,
    limit_value INT,
    FOREIGN KEY (question_id) REFERENCES survey_questions(question_id) ON DELETE CASCADE
);

CREATE TABLE temp_response_table (
    temp_response_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL,
    question_id INT NOT NULL,
    option_id INT,
    limit_value INT,
    other_response TEXT,
    string_response TEXT,
    FOREIGN KEY (username) REFERENCES user_session_token(username) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES survey_questions(question_id) ON DELETE CASCADE,
    FOREIGN KEY (option_id) REFERENCES survey_options(option_id) ON DELETE CASCADE
);

CREATE TABLE survey_responses (
    response_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL,
    question_id INT NOT NULL,
    option_id INT,
    limit_value INT,
    other_response TEXT,
    string_response TEXT,
    FOREIGN KEY (username) REFERENCES user_session_token(username) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES survey_questions(question_id) ON DELETE CASCADE,
    FOREIGN KEY (option_id) REFERENCES survey_options(option_id) ON DELETE CASCADE
);

CREATE TABLE users_submitted (
    submission_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) UNIQUE NOT NULL,
    submission_timestamp DATETIME NOT NULL
);  

CREATE TABLE complques (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL UNIQUE,
    is_member ENUM('Yes', 'No') NOT NULL,
    reason TEXT DEFAULT NULL,
    submission_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE personal_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    libraryFeedback TEXT DEFAULT NULL,
    username VARCHAR(100) DEFAULT NULL,
    userDivision VARCHAR(100) DEFAULT NULL,
    userDesignation VARCHAR(100) DEFAULT NULL,
    userTel INT DEFAULT NULL,
    userEmail VARCHAR(100) DEFAULT NULL,
    userInterests TEXT DEFAULT NULL,
    submissionDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (username) REFERENCES user_session_token(username) ON DELETE CASCADE
);
