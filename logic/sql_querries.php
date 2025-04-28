<?php
// Database table name constants
define('TBL_STUDENTS', 'students');
define('TBL_PARENTS', 'parents');
define('TBL_USERS', 'tbl_users');

// SQL Query Constants for Students Registration
define('SQL_CHECK_EMAIL_EXISTS', 
    "SELECT email FROM " . TBL_STUDENTS . " WHERE email = ?"
);

define('SQL_CHECK_STUDENT_ID_EXISTS', 
    "SELECT student_id FROM " . TBL_STUDENTS . " WHERE student_id = ?"
);

define('SQL_INSERT_USER',
    "INSERT INTO " . TBL_USERS . " (email, password, role) 
     VALUES (?, ?, 'student')"
);

define('SQL_INSERT_STUDENT',
    "INSERT INTO " . TBL_STUDENTS . 
    " (first_name, middle_name, last_name, grade_level, section, email, phone_number, password) 
     VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
);

define('SQL_INSERT_PARENT',
    "INSERT INTO " . TBL_PARENTS . 
    " (parent_name, contact_number, student_id) 
     VALUES (?, ?, ?)"
);

define('SQL_GET_STUDENT',
    "SELECT s.*, p.parent_name, p.contact_number, u.email 
     FROM " . TBL_STUDENTS . " s
     LEFT JOIN " . TBL_PARENTS . " p ON s.student_id = p.student_id
     LEFT JOIN " . TBL_USERS . " u ON s.user_id = u.id
     WHERE s.student_id = ?"
);

define('SQL_UPDATE_STUDENT',
    "UPDATE " . TBL_STUDENTS . 
    " SET first_name = ?, middle_name = ?, last_name = ?, 
          grade_level = ?, section = ?, phone_number = ?
     WHERE student_id = ?"
);

define('SQL_UPDATE_PARENT',
    "UPDATE " . TBL_PARENTS . 
    " SET parent_name = ?, contact_number = ?
     WHERE student_id = ?"
);

define('SQL_UPDATE_USER',
    "UPDATE " . TBL_USERS . 
    " SET email = ?
     WHERE id = (SELECT user_id FROM " . TBL_STUDENTS . " WHERE student_id = ?)"
);

define('SQL_DELETE_STUDENT',
    "DELETE s, p, u 
     FROM " . TBL_STUDENTS . " s
     LEFT JOIN " . TBL_PARENTS . " p ON s.student_id = p.student_id
     LEFT JOIN " . TBL_USERS . " u ON s.user_id = u.id
     WHERE s.student_id = ?"
);

// Authentication Queries
// define('SQL_LOGIN',
//     "SELECT u.id, u.email, u.role, s.student_id, s.first_name, s.last_name 
//      FROM " . TBL_USERS . " u
//      LEFT JOIN " . TBL_STUDENTS . " s ON u.id = s.user_id
//      WHERE u.email = ? AND u.password = ?"
// );
define('SQL_STUDENT_LOGIN',
    "SELECT email, password, first_name, middle_name, last_name from students where email = ? and password = ?"
);
// Search Queries
define('SQL_SEARCH_STUDENTS',
    "SELECT s.*, p.parent_name, p.contact_number 
     FROM " . TBL_STUDENTS . " s
     LEFT JOIN " . TBL_PARENTS . " p ON s.student_id = p.student_id
     WHERE s.first_name LIKE ? OR s.last_name LIKE ? OR s.student_id LIKE ?"
);

// List Queries
define('SQL_LIST_STUDENTS',
    "SELECT s.*, p.parent_name, p.contact_number 
     FROM " . TBL_STUDENTS . " s
     LEFT JOIN " . TBL_PARENTS . " p ON s.student_id = p.student_id
     ORDER BY s.grade_level, s.section, s.last_name"
);

define('SQL_LIST_STUDENTS_BY_GRADE',
    "SELECT s.*, p.parent_name, p.contact_number 
     FROM " . TBL_STUDENTS . " s
     LEFT JOIN " . TBL_PARENTS . " p ON s.student_id = p.student_id
     WHERE s.grade_level = ?
     ORDER BY s.section, s.last_name"
);

// Password Reset Query
define('SQL_UPDATE_PASSWORD',
    "UPDATE " . TBL_USERS . 
    " SET password = ?
     WHERE email = ?"
);
?>