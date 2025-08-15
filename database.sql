CREATE DATABASE IF NOT EXISTS expense_manager;

USE expense_manager;

-- 创建用户表（无需修改）
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    nickname VARCHAR(50) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 创建报销分类表（无需修改）
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) UNIQUE NOT NULL
);

-- 创建新的报销单表，用于存储报销记录的头部信息
CREATE TABLE IF NOT EXISTS expense_reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 修改原有的 expenses 表，作为报销单中的具体费用项目
-- 移除 user_id, status, created_at 列，并添加 report_id 外键
CREATE TABLE IF NOT EXISTS expenses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    report_id INT NOT NULL,
    category_id INT NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    expense_date DATE NOT NULL,
    approver_project VARCHAR(255) NOT NULL,
    description TEXT,
    FOREIGN KEY (report_id) REFERENCES expense_reports(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

-- 插入一些初始分类数据
INSERT INTO categories (name) VALUES ('交通');
INSERT INTO categories (name) VALUES ('餐饮');
INSERT INTO categories (name) VALUES ('住宿');
INSERT INTO categories (name) VALUES ('办公用品');
INSERT INTO categories (name) VALUES ('其他');

-- 插入一个管理员用户
-- 将密码改为明文 'admin123'
INSERT INTO users (username, nickname, password_hash, role) VALUES ('admin', '管理员', 'admin123', 'admin');

-- 插入一个普通用户
-- 将密码改为明文 'user123'
INSERT INTO users (username, nickname, password_hash, role) VALUES ('user', '普通用户', 'user123', 'user');