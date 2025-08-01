
-- 1. Salesperson Table
CREATE TABLE Salesperson (
    Salesperson_ID INT PRIMARY KEY AUTO_INCREMENT,
    F_NAME VARCHAR(50) NOT NULL,
    L_NAME VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(100) NOT NULL,
    phone VARCHAR(15) NOT NULL
);

-- 2. Client Table
CREATE TABLE Client (
    C_ID INT PRIMARY KEY AUTO_INCREMENT,
    C_NAME VARCHAR(100) NOT NULL,
    business VARCHAR(100),
    phone VARCHAR(15),
    email VARCHAR(100),
    address TEXT,
    Salesperson_ID INT NOT NULL,
    FOREIGN KEY (Salesperson_ID) REFERENCES Salesperson(Salesperson_ID)
);

-- 3. Campaign Table
CREATE TABLE Campaign (
    CA_ID INT PRIMARY KEY AUTO_INCREMENT,
    CA_NAME VARCHAR(100) NOT NULL,
    C_ID INT NOT NULL,
    Salesperson_ID INT NOT NULL,
    FOREIGN KEY (C_ID) REFERENCES Client(C_ID),
    FOREIGN KEY (Salesperson_ID) REFERENCES Salesperson(Salesperson_ID)
);

-- 4. Live Announcement (LA) Table
CREATE TABLE LiveAnnouncement (
    LA_ID INT PRIMARY KEY AUTO_INCREMENT,
    la_request_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    La_Name VARCHAR(100) NOT NULL,
    La_info TEXT,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    days VARCHAR(50),
    CA_ID INT NOT NULL,
    FOREIGN KEY (CA_ID) REFERENCES Campaign(CA_ID)
);

-- 5. Interview Table
CREATE TABLE Interview (
    IN_ID INT PRIMARY KEY AUTO_INCREMENT,
    IN_NAME VARCHAR(100) NOT NULL,
    IN_TIME TIME NOT NULL,
    IN_DATE DATE NOT NULL,
    IN_INFO TEXT,
    CA_ID INT NOT NULL,
    FOREIGN KEY (CA_ID) REFERENCES Campaign(CA_ID)
);

-- 6. Giveaways Table
CREATE TABLE Giveaways (
    G_ID INT PRIMARY KEY AUTO_INCREMENT,
    G_NAME VARCHAR(100) NOT NULL,
    G_TIME TIME NOT NULL,
    G_INFO TEXT,
    G_DATE DATE NOT NULL,
    CA_ID INT NOT NULL,
    FOREIGN KEY (CA_ID) REFERENCES Campaign(CA_ID)
);

-- 7. Admin Sales Table
CREATE TABLE AdminSales (
    SA_ID INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(15),
    last_login TIMESTAMP,
    password VARCHAR(100) NOT NULL
);

-- 8. Admin Production Table
CREATE TABLE AdminProduction (
    PA_ID INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(15),
    last_login TIMESTAMP,
    password VARCHAR(100) NOT NULL
);

-- 9. Request Table
CREATE TABLE Request (
    Request_ID INT PRIMARY KEY AUTO_INCREMENT,
    SA_ID INT NOT NULL,
    PA_ID INT NOT NULL,
    Request_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    Request_info TEXT,
    FOREIGN KEY (SA_ID) REFERENCES AdminSales(SA_ID),
    FOREIGN KEY (PA_ID) REFERENCES AdminProduction(PA_ID)
);

-- 10. Log Table
CREATE TABLE Log (
    Log_ID INT PRIMARY KEY AUTO_INCREMENT,
    Log_Type VARCHAR(20) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    time TIME NOT NULL,
    days VARCHAR(50),
    Entity_ID INT NOT NULL,
    log_text TEXT,
    created_by INT NOT NULL,
    FOREIGN KEY (created_by) REFERENCES AdminProduction(PA_ID)
);

-- 11. RJ (Radio Jockey) Table
CREATE TABLE RJ (
    RJ_ID INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(100) NOT NULL,
    last_login TIMESTAMP
);

-- 12. Task Update Table
CREATE TABLE TaskUpdate (
    Task_ID INT PRIMARY KEY AUTO_INCREMENT,
    Log_ID INT NOT NULL,
    RJ_ID INT NOT NULL,
    update_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status VARCHAR(50) NOT NULL,
    comments TEXT,
    FOREIGN KEY (Log_ID) REFERENCES Log(Log_ID),
    FOREIGN KEY (RJ_ID) REFERENCES RJ(RJ_ID)
);

-- 13. Super Admin Table
CREATE TABLE SuperAdmin (
    SuperAdmin_ID INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(100) NOT NULL
);

-- 14. ClientContact Table
CREATE TABLE ClientContact (
    Contact_ID INT PRIMARY KEY AUTO_INCREMENT,
    C_ID INT NOT NULL,
    Contact_Type VARCHAR(20) NOT NULL,
    Contact_Detail VARCHAR(100) NOT NULL,
    FOREIGN KEY (C_ID) REFERENCES Client(C_ID)
);
