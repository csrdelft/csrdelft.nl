ALTER TABLE login_sessions ADD authentication_method enum('ut','ct','pl','rpl','plaott') NOT NULL AFTER lock_ip;
