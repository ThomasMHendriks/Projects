DROP DATABASE IF EXISTS reminderList;
CREATE DATABASE reminderList;
USE reminderList;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL,
    pass_hash VARCHAR(255) NOT NULL
);

CREATE TABLE books (
    userid INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    information TEXT,
    FOREIGN KEY (userid) REFERENCES users(id)
);
CREATE TABLE series (
    userid INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    information TEXT,
    FOREIGN KEY (userid) REFERENCES users(id)
);
CREATE TABLE movies (
    userid INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    information TEXT,
    FOREIGN KEY (userid) REFERENCES users(id)
);