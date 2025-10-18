-- สร้างฐานข้อมูล
CREATE DATABASE IF NOT EXISTS equipment_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE equipment_management;

-- ตารางประเภทอุปกรณ์
CREATE TABLE equipment_types (
    type_id INT PRIMARY KEY AUTO_INCREMENT,
    type_name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ตารางอุปกรณ์
CREATE TABLE equipments (
    equipment_id INT PRIMARY KEY AUTO_INCREMENT,
    equipment_code VARCHAR(50) UNIQUE NOT NULL,
    equipment_name VARCHAR(200) NOT NULL,
    type_id INT,
    brand VARCHAR(100),
    model VARCHAR(100),
    quantity INT DEFAULT 1,
    available_quantity INT DEFAULT 1,
    status ENUM('available', 'borrowed', 'maintenance', 'damaged') DEFAULT 'available',
    description TEXT,
    image_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (type_id) REFERENCES equipment_types(type_id)
);

-- ตารางผู้ใช้
CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(20),
    role ENUM('admin', 'staff', 'user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ตารางการยืมอุปกรณ์
CREATE TABLE borrowing (
    borrow_id INT PRIMARY KEY AUTO_INCREMENT,
    equipment_id INT,
    user_id INT,
    borrow_date DATETIME NOT NULL,
    expected_return_date DATETIME NOT NULL,
    actual_return_date DATETIME,
    return_request_date DATETIME,
    quantity INT DEFAULT 1,
    status ENUM('borrowed', 'pending_return', 'returned', 'overdue') DEFAULT 'borrowed',
    notes TEXT,
    return_notes TEXT,
    condition_on_return ENUM('good', 'damaged', 'need_repair') DEFAULT 'good',
    approved_by INT,
    checked_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (equipment_id) REFERENCES equipments(equipment_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (approved_by) REFERENCES users(user_id),
    FOREIGN KEY (checked_by) REFERENCES users(user_id)
);

-- ตารางวัสดุ
CREATE TABLE materials (
    material_id INT PRIMARY KEY AUTO_INCREMENT,
    material_code VARCHAR(50) UNIQUE NOT NULL,
    material_name VARCHAR(200) NOT NULL,
    type_id INT,
    unit VARCHAR(50),
    quantity INT DEFAULT 0,
    min_quantity INT DEFAULT 10,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (type_id) REFERENCES equipment_types(type_id)
);

-- ตารางการเบิกวัสดุ
CREATE TABLE material_requisition (
    requisition_id INT PRIMARY KEY AUTO_INCREMENT,
    material_id INT,
    user_id INT,
    quantity INT NOT NULL,
    requisition_date DATETIME NOT NULL,
    status ENUM('pending', 'approved', 'rejected', 'completed') DEFAULT 'pending',
    purpose TEXT,
    approved_by INT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (material_id) REFERENCES materials(material_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (approved_by) REFERENCES users(user_id)
);

-- ข้อมูลเริ่มต้น - ประเภทอุปกรณ์
INSERT INTO equipment_types (type_name, description) VALUES
('ทุกประเภท', 'อุปกรณ์ทั่วไป'),
('Computer Notebook', 'เครื่องคอมพิวเตอร์โน้ตบุ๊ค'),
('Robotic Arm', 'แขนกลหุ่นยนต์'),
('Mobile Robot', 'หุ่นยนต์เคลื่อนที่'),
('AI System', 'ระบบปัญญาประดิษฐ์'),
('Electronics', 'อุปกรณ์อิเล็กทรอนิกส์'),
('Display', 'อุปกรณ์แสดงผล'),
('Audio', 'อุปกรณ์เสียง'),
('Storage', 'อุปกรณ์จัดเก็บข้อมูล'),
('Cable', 'สายเคเบิล'),
('Power', 'อุปกรณ์ไฟฟ้า'),
('Network', 'อุปกรณ์เครือข่าย'),
('IOT', 'อุปกรณ์ IOT'),
('Camera', 'กล้อง');

-- ข้อมูลเริ่มต้น - อุปกรณ์
INSERT INTO equipments (equipment_code, equipment_name, type_id, brand, model, quantity, available_quantity) VALUES
('NB001', 'Computer Notebook', 2, '', '', 5, 5),
('ARM001', 'Robotic Arm', 3, 'Hiwonder', 'AiArm', 2, 2),
('LIMO001', 'Mobile Robot', 4, 'AGILE X', 'Limo', 1, 1),
('SORT001', 'Autonomous AI Sorting System', 5, 'Hiwonder', 'Autonomous Al Sorting System', 1, 1),
('GRAV001', 'Electronics Board', 6, 'Gravitech', '', 3, 3),
('DISP001', 'Display 75 inches', 7, 'PULIN', '75 inches', 1, 1),
('CROW001', 'Crow Pi 2', 6, 'Elecrow', 'Crow Pi 2', 2, 2),
('SPEAK001', 'Speaker System', 8, 'Behringer', 'MPA40BT-PRO', 2, 2),
('ENC001', 'Enclosure SATA', 9, '', 'Nvem-SATA', 5, 5),
('ENC002', 'Enclosure M.2', 9, 'ORICO', 'TCM2-10G-C3-BP-HW Blue', 3, 3),
('HDMI001', 'Cable HDMI 5M', 10, 'UGREEN', 'V.1.4 M/M 5M', 10, 10),
('HDMI002', 'Cable HDMI 10M', 10, 'UGREEN', 'V.1.4 M/M 10M', 5, 5),
('PLUG001', 'ปลั๊กแยก 4 ทาง', 11, '', 'หัวเทียบทองเหลือง', 8, 8),
('PLUG002', 'ปลั๊กไฟ 5 เมตร', 11, '', '', 10, 10),
('IKON001', 'อุปกรณ์อิเล็กทรอนิกส์', 6, 'IKON', '2931', 2, 2),
('AMP001', 'เครื่องขยายเสียง', 8, '', '', 3, 3),
('NET001', 'Network Equipment', 12, '', '', 5, 5),
('IOT001', 'IOT Device', 13, '', '', 4, 4),
('CAM001', 'กล้อง', 14, '', '', 3, 3);

-- ข้อมูลเริ่มต้น - ผู้ใช้ (password: admin123)
INSERT INTO users (username, password, full_name, email, role) VALUES
('admin', 'admin', 'ผู้ดูแลระบบ', 'admin@system.com', 'admin'),
('staff', 'staff', 'เจ้าหน้าที่', 'staff@system.com', 'staff');