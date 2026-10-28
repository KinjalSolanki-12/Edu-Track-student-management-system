-- ==========================================================
-- Education Track - Sample Seed Data
-- Default passwords (plain text, for your reference only):
--   Admin    -> admin123
--   Teachers -> teacher123
--   Students -> student123
-- ==========================================================

USE education_track;

-- Admin account
INSERT INTO admins (username, email, password, full_name) VALUES
('admin', 'admin@edutrack.com', '$2b$10$0OFdt3zNO/Cn/Z9q5f/Eyum8YSIsyNAaQXwTcaYNQR42tDiE5JViq', 'System Administrator');

-- Teachers
INSERT INTO teachers (full_name, email, password, phone, specialization, status) VALUES
('Dr. Sarah Johnson', 'sarah.johnson@edutrack.com', '$2b$10$Dgi3kbH/jcZN6hv1FJVaXeqoGbn9dBlBJxvOgmlQYWe9KNe8vzDJa', '9876543210', 'Computer Science', 'active'),
('Prof. Michael Lee', 'michael.lee@edutrack.com', '$2b$10$Dgi3kbH/jcZN6hv1FJVaXeqoGbn9dBlBJxvOgmlQYWe9KNe8vzDJa', '9876543211', 'Mathematics', 'active'),
('Dr. Emily Davis', 'emily.davis@edutrack.com', '$2b$10$Dgi3kbH/jcZN6hv1FJVaXeqoGbn9dBlBJxvOgmlQYWe9KNe8vzDJa', '9876543212', 'Physics', 'active');

-- Courses
INSERT INTO courses (course_code, course_name, description, credits, teacher_id) VALUES
('CS101', 'Introduction to Programming', 'Fundamentals of programming using modern languages.', 4, 1),
('CS202', 'Data Structures & Algorithms', 'Core data structures and algorithmic thinking.', 4, 1),
('MATH101', 'Calculus I', 'Differential and integral calculus.', 3, 2),
('PHY101', 'General Physics', 'Mechanics, energy and motion fundamentals.', 3, 3);

-- Students
INSERT INTO students (roll_no, full_name, email, password, phone, dob, gender, address, status) VALUES
('STU2026001', 'Aarav Sharma', 'aarav.sharma@student.edutrack.com', '$2b$10$ta/.kWpcYCZG.7NcCYxHuubJVHMDelUkmtRbgid/.rgYWjHCiZdw2', '9998887771', '2005-03-14', 'Male', 'Rajkot, Gujarat', 'active'),
('STU2026002', 'Isha Patel', 'isha.patel@student.edutrack.com', '$2b$10$ta/.kWpcYCZG.7NcCYxHuubJVHMDelUkmtRbgid/.rgYWjHCiZdw2', '9998887772', '2005-07-22', 'Female', 'Ahmedabad, Gujarat', 'active'),
('STU2026003', 'Rohan Mehta', 'rohan.mehta@student.edutrack.com', '$2b$10$ta/.kWpcYCZG.7NcCYxHuubJVHMDelUkmtRbgid/.rgYWjHCiZdw2', '9998887773', '2004-11-05', 'Male', 'Surat, Gujarat', 'active');

-- Enrollments
INSERT INTO enrollments (student_id, course_id) VALUES
(1, 1), (1, 2), (1, 3),
(2, 1), (2, 3), (2, 4),
(3, 2), (3, 4);

-- Attendance (sample for last few days)
INSERT INTO attendance (student_id, course_id, attendance_date, status, marked_by) VALUES
(1, 1, CURDATE() - INTERVAL 3 DAY, 'Present', 'Dr. Sarah Johnson'),
(1, 1, CURDATE() - INTERVAL 2 DAY, 'Present', 'Dr. Sarah Johnson'),
(1, 1, CURDATE() - INTERVAL 1 DAY, 'Absent', 'Dr. Sarah Johnson'),
(1, 2, CURDATE() - INTERVAL 2 DAY, 'Present', 'Dr. Sarah Johnson'),
(1, 3, CURDATE() - INTERVAL 1 DAY, 'Late', 'Prof. Michael Lee'),
(2, 1, CURDATE() - INTERVAL 3 DAY, 'Present', 'Dr. Sarah Johnson'),
(2, 1, CURDATE() - INTERVAL 1 DAY, 'Present', 'Dr. Sarah Johnson'),
(2, 4, CURDATE() - INTERVAL 2 DAY, 'Absent', 'Dr. Emily Davis'),
(3, 2, CURDATE() - INTERVAL 2 DAY, 'Present', 'Dr. Sarah Johnson'),
(3, 4, CURDATE() - INTERVAL 1 DAY, 'Present', 'Dr. Emily Davis');

-- Marks
INSERT INTO marks (student_id, course_id, exam_type, marks_obtained, total_marks, grade) VALUES
(1, 1, 'Midterm', 78, 100, 'B+'),
(1, 1, 'Final', 85, 100, 'A'),
(1, 3, 'Midterm', 65, 100, 'B'),
(2, 1, 'Midterm', 92, 100, 'A+'),
(2, 4, 'Final', 74, 100, 'B+'),
(3, 2, 'Midterm', 55, 100, 'C'),
(3, 4, 'Final', 88, 100, 'A');

-- Assignments
INSERT INTO assignments (course_id, title, description, due_date, uploaded_by) VALUES
(1, 'Assignment 1: Basic Syntax', 'Write 5 programs demonstrating variables, loops and conditionals.', CURDATE() + INTERVAL 7 DAY, 'Dr. Sarah Johnson'),
(2, 'Assignment 1: Linked Lists', 'Implement a singly linked list with insert/delete operations.', CURDATE() + INTERVAL 10 DAY, 'Dr. Sarah Johnson'),
(3, 'Problem Set 1: Limits', 'Solve the attached limit and continuity problems.', CURDATE() + INTERVAL 5 DAY, 'Prof. Michael Lee');

-- Timetable
INSERT INTO timetable (course_id, day_of_week, start_time, end_time, room) VALUES
(1, 'Monday', '09:00:00', '10:30:00', 'Room 101'),
(2, 'Monday', '11:00:00', '12:30:00', 'Room 102'),
(3, 'Tuesday', '09:00:00', '10:30:00', 'Room 201'),
(4, 'Wednesday', '10:00:00', '11:30:00', 'Lab 1'),
(1, 'Thursday', '09:00:00', '10:30:00', 'Room 101'),
(3, 'Friday', '11:00:00', '12:30:00', 'Room 201');

-- Notifications
INSERT INTO notifications (title, message, target_audience, created_by) VALUES
('Welcome to Education Track!', 'The new semester has begun. Please check your timetable and enrolled courses.', 'all', 'System Administrator'),
('Midterm Exam Schedule Released', 'Midterm exams will be held from the 20th of this month. Check the timetable page for details.', 'students', 'System Administrator'),
('Assignment Submission Reminder', 'Please submit all pending assignments before their due dates to avoid grade penalties.', 'students', 'System Administrator');

-- Settings
INSERT INTO settings (setting_key, setting_value) VALUES
('site_name', 'Education Track'),
('academic_year', '2026-2027'),
('institution_name', 'Education Track Institute'),
('contact_email', 'info@edutrack.com'),
('max_upload_size_mb', '5');
