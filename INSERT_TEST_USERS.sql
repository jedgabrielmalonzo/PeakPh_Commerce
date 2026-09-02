-- ============================================================
-- TEST USERS FOR ETHICAL HACKING / PENETRATION TESTING
-- Brute Force Practice Dataset
-- ============================================================
-- These are intentionally weak credentials for course activities
-- DO NOT use in production environments
-- ============================================================

-- Clear existing test users (optional - comment out to preserve)
-- DELETE FROM users WHERE email IN (
--   'john.doe@test.com', 'jane.smith@test.com', 'admin.user@test.com',
--   'test.user@test.com', 'demo@test.com', 'user1@test.com', 'user2@test.com',
--   'hacker@test.com', 'support@test.com', 'marketing@test.com'
-- );

-- ============================================================
-- GROUP 1: VERY WEAK PASSWORDS (Easy to crack)
-- ============================================================
INSERT INTO users (username, email, password, role, status) VALUES
('John Doe', 'john.doe@test.com', '123456', 'User', 'Active'),
('Jane Smith', 'jane.smith@test.com', 'password', 'User', 'Active'),
('Admin User', 'admin.user@test.com', '12345678', 'User', 'Active');

-- ============================================================
-- GROUP 2: COMMON WEAK PASSWORDS (Dictionary attacks)
-- ============================================================
INSERT INTO users (username, email, password, role, status) VALUES
('Test User', 'test.user@test.com', 'qwerty', 'User', 'Active'),
('Demo Account', 'demo@test.com', 'letmein', 'User', 'Active'),
('User One', 'user1@test.com', 'welcome', 'User', 'Active'),
('User Two', 'user2@test.com', '111111', 'User', 'Active');

-- ============================================================
-- GROUP 3: SEASON/PATTERN-BASED PASSWORDS
-- ============================================================
INSERT INTO users (username, email, password, role, status) VALUES
('Hacker Test', 'hacker@test.com', 'Summer2024', 'User', 'Active'),
('Support Team', 'support@test.com', 'Winter2024', 'User', 'Active'),
('Marketing Dept', 'marketing@test.com', 'Admin123', 'User', 'Active');

-- ============================================================
-- GROUP 4: COMPANY-RELATED PASSWORDS
-- ============================================================
INSERT INTO users (username, email, password, role, status) VALUES
('Peak User 1', 'peak.user1@test.com', 'PeakPH', 'User', 'Active'),
('Peak User 2', 'peak.user2@test.com', 'peakph123', 'User', 'Active'),
('Tent Lover', 'tent.lover@test.com', 'camping', 'User', 'Active'),
('Hiker Guy', 'hiker@test.com', 'hiking', 'User', 'Active');

-- ============================================================
-- GROUP 5: KEYBOARD PATTERNS
-- ============================================================
INSERT INTO users (username, email, password, role, status) VALUES
('Qwerty User', 'qwerty@test.com', 'asdfgh', 'User', 'Active'),
('Pattern User', 'pattern@test.com', 'zxcvbn', 'User', 'Active'),
('Number User', 'number@test.com', '1234567', 'User', 'Active');

-- ============================================================
-- GROUP 6: REAL-WORLD COMMON PASSWORDS (Top 25 weak passwords)
-- ============================================================
INSERT INTO users (username, email, password, role, status) VALUES
('User Three', 'user3@test.com', 'password123', 'User', 'Active'),
('User Four', 'user4@test.com', '123123', 'User', 'Active'),
('User Five', 'user5@test.com', 'admin', 'User', 'Active'),
('User Six', 'user6@test.com', 'iloveyou', 'User', 'Active'),
('User Seven', 'user7@test.com', 'abc123', 'User', 'Active'),
('User Eight', 'user8@test.com', 'monkey', 'User', 'Active'),
('User Nine', 'user9@test.com', 'dragon', 'User', 'Active'),
('User Ten', 'user10@test.com', 'master', 'User', 'Active');

-- ============================================================
-- BONUS: DEFAULT-LIKE CREDENTIALS
-- ============================================================
INSERT INTO users (username, email, password, role, status) VALUES
('Default User', 'default@test.com', 'default', 'User', 'Active'),
('Test Admin', 'testadmin@test.com', 'testpass', 'User', 'Active'),
('Guest Account', 'guest@test.com', 'guest123', 'User', 'Active');

-- ============================================================
-- REFERENCE GUIDE FOR ATTACKING GROUPS
-- ============================================================
/*
WEAK PASSWORD LIST (in order of likelihood):
1. 123456
2. password
3. 12345678
4. qwerty
5. letmein
6. welcome
7. 111111
8. PeakPH / peakph123
9. Summer2024 / Winter2024
10. asdfgh
11. password123
12. 123123
13. admin
14. iloveyou
15. abc123
16. monkey
17. dragon
18. master
19. default
20. testpass
21. camping
22. hiking
23. Admin123
24. guest123
25. zxcvbn

TIPS FOR BRUTE FORCING THIS SITE:
- Use hydra, medusa, or wfuzz
- No rate limiting enabled - unlimited attempts allowed
- No account lockout - can try forever
- No delays between attempts
- Plain text passwords stored (vulnerable)
- SQL Injection also possible in email field

EXAMPLE HYDRA COMMAND:
hydra -l john.doe@test.com -P passwords.txt localhost http-post-form "/auth/login_handler.php:email=^USER^&password=^PASS^:Invalid email"

EXAMPLE CURL LOOP:
for pass in 123456 password 12345678 qwerty; do
  curl -X POST http://localhost/PeakPH_Commerce/auth/login_handler.php \
    -d "email=john.doe@test.com&password=$pass"
done
*/

-- ============================================================
-- VERIFICATION QUERIES
-- ============================================================
-- To verify users were inserted:
-- SELECT COUNT(*) FROM users;
-- SELECT email, password FROM users WHERE email LIKE '%.test.com';
