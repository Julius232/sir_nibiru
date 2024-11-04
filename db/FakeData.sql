-- Insert 10 fake users with realistic usernames and AUTO_INCREMENT user_id
INSERT INTO `users` (`username`, `wallet_address`) VALUES
('Alice', 'wallet1abcDEF1234567890abcdefghijklmnopqrs'),
('Bob', 'wallet2abcDEF1234567890abcdefghijklmnopqrs'),
('Charlie', 'wallet3abcDEF1234567890abcdefghijklmnopqrs'),
('David', 'wallet4abcDEF1234567890abcdefghijklmnopqrs'),
('Eve', 'wallet5abcDEF1234567890abcdefghijklmnopqrs'),
('Frank', 'wallet6abcDEF1234567890abcdefghijklmnopqrs'),
('Grace', 'wallet7abcDEF1234567890abcdefghijklmnopqrs'),
('Hank', 'wallet8abcDEF1234567890abcdefghijklmnopqrs'),
('Ivy', 'wallet9abcDEF1234567890abcdefghijklmnopqrs'),
('Judy', 'wallet10abcDEF1234567890abcdefghijklmnopqrs');

-- Insert random donations for these users with random amounts between 10000 and 10000000
INSERT INTO `donations` (`signature`, `user_id`, `donation_amount`, `action`, `timestamp`) VALUES
('sig1', (SELECT user_id FROM users WHERE username = 'Alice'), ROUND(RAND() * (10000000 - 10000) + 10000, 2), 'clean', NOW() - INTERVAL FLOOR(RAND() * 30) DAY),
('sig2', (SELECT user_id FROM users WHERE username = 'Bob'), ROUND(RAND() * (10000000 - 10000) + 10000, 2), 'play', NOW() - INTERVAL FLOOR(RAND() * 30) DAY),
('sig3', (SELECT user_id FROM users WHERE username = 'Charlie'), ROUND(RAND() * (10000000 - 10000) + 10000, 2), 'feed', NOW() - INTERVAL FLOOR(RAND() * 30) DAY),
('sig4', (SELECT user_id FROM users WHERE username = 'David'), ROUND(RAND() * (10000000 - 10000) + 10000, 2), 'clean', NOW() - INTERVAL FLOOR(RAND() * 30) DAY),
('sig5', (SELECT user_id FROM users WHERE username = 'Eve'), ROUND(RAND() * (10000000 - 10000) + 10000, 2), 'play', NOW() - INTERVAL FLOOR(RAND() * 30) DAY),
('sig6', (SELECT user_id FROM users WHERE username = 'Frank'), ROUND(RAND() * (10000000 - 10000) + 10000, 2), 'feed', NOW() - INTERVAL FLOOR(RAND() * 30) DAY),
('sig7', (SELECT user_id FROM users WHERE username = 'Grace'), ROUND(RAND() * (10000000 - 10000) + 10000, 2), 'clean', NOW() - INTERVAL FLOOR(RAND() * 30) DAY),
('sig8', (SELECT user_id FROM users WHERE username = 'Hank'), ROUND(RAND() * (10000000 - 10000) + 10000, 2), 'play', NOW() - INTERVAL FLOOR(RAND() * 30) DAY),
('sig9', (SELECT user_id FROM users WHERE username = 'Ivy'), ROUND(RAND() * (10000000 - 10000) + 10000, 2), 'feed', NOW() - INTERVAL FLOOR(RAND() * 30) DAY),
('sig10', (SELECT user_id FROM users WHERE username = 'Judy'), ROUND(RAND() * (10000000 - 10000) + 10000, 2), 'clean', NOW() - INTERVAL FLOOR(RAND() * 30) DAY);
-- Insert 10 additional fake users with realistic usernames and AUTO_INCREMENT user_id
INSERT INTO `users` (`username`, `wallet_address`) VALUES
('Karen', 'wallet11abcDEF1234567890abcdefghijklmnopqrs'),
('Leo', 'wallet12abcDEF1234567890abcdefghijklmnopqrs'),
('Mona', 'wallet13abcDEF1234567890abcdefghijklmnopqrs'),
('Nick', 'wallet14abcDEF1234567890abcdefghijklmnopqrs'),
('Olive', 'wallet15abcDEF1234567890abcdefghijklmnopqrs'),
('Paul', 'wallet16abcDEF1234567890abcdefghijklmnopqrs'),
('Quincy', 'wallet17abcDEF1234567890abcdefghijklmnopqrs'),
('Rose', 'wallet18abcDEF1234567890abcdefghijklmnopqrs'),
('Steve', 'wallet19abcDEF1234567890abcdefghijklmnopqrs'),
('Tina', 'wallet20abcDEF1234567890abcdefghijklmnopqrs');

-- Insert random donations for these users with random amounts between 10000 and 10000000
INSERT INTO `donations` (`signature`, `user_id`, `donation_amount`, `action`, `timestamp`) VALUES
('sig11', (SELECT user_id FROM users WHERE username = 'Karen'), ROUND(RAND() * (10000000 - 10000) + 10000, 2), 'play', NOW() - INTERVAL FLOOR(RAND() * 30) DAY),
('sig12', (SELECT user_id FROM users WHERE username = 'Leo'), ROUND(RAND() * (10000000 - 10000) + 10000, 2), 'feed', NOW() - INTERVAL FLOOR(RAND() * 30) DAY),
('sig13', (SELECT user_id FROM users WHERE username = 'Mona'), ROUND(RAND() * (10000000 - 10000) + 10000, 2), 'clean', NOW() - INTERVAL FLOOR(RAND() * 30) DAY),
('sig14', (SELECT user_id FROM users WHERE username = 'Nick'), ROUND(RAND() * (10000000 - 10000) + 10000, 2), 'play', NOW() - INTERVAL FLOOR(RAND() * 30) DAY),
('sig15', (SELECT user_id FROM users WHERE username = 'Olive'), ROUND(RAND() * (10000000 - 10000) + 10000, 2), 'feed', NOW() - INTERVAL FLOOR(RAND() * 30) DAY),
('sig16', (SELECT user_id FROM users WHERE username = 'Paul'), ROUND(RAND() * (10000000 - 10000) + 10000, 2), 'clean', NOW() - INTERVAL FLOOR(RAND() * 30) DAY),
('sig17', (SELECT user_id FROM users WHERE username = 'Quincy'), ROUND(RAND() * (10000000 - 10000) + 10000, 2), 'play', NOW() - INTERVAL FLOOR(RAND() * 30) DAY),
('sig18', (SELECT user_id FROM users WHERE username = 'Rose'), ROUND(RAND() * (10000000 - 10000) + 10000, 2), 'feed', NOW() - INTERVAL FLOOR(RAND() * 30) DAY),
('sig19', (SELECT user_id FROM users WHERE username = 'Steve'), ROUND(RAND() * (10000000 - 10000) + 10000, 2), 'clean', NOW() - INTERVAL FLOOR(RAND() * 30) DAY),
('sig20', (SELECT user_id FROM users WHERE username = 'Tina'), ROUND(RAND() * (10000000 - 10000) + 10000, 2), 'play', NOW() - INTERVAL FLOOR(RAND() * 30) DAY);

-- Additional donations with varying action types and user interactions
INSERT INTO `donations` (`signature`, `user_id`, `donation_amount`, `action`, `timestamp`) VALUES
('sig21', (SELECT user_id FROM users WHERE username = 'Alice'), ROUND(RAND() * (10000000 - 10000) + 10000, 2), 'feed', NOW() - INTERVAL FLOOR(RAND() * 90) DAY),
('sig22', (SELECT user_id FROM users WHERE username = 'Bob'), ROUND(RAND() * (10000000 - 10000) + 10000, 2), 'clean', NOW() - INTERVAL FLOOR(RAND() * 45) DAY),
('sig23', (SELECT user_id FROM users WHERE username = 'Charlie'), ROUND(RAND() * (10000000 - 10000) + 10000, 2), 'play', NOW() - INTERVAL FLOOR(RAND() * 60) DAY),
('sig24', (SELECT user_id FROM users WHERE username = 'David'), ROUND(RAND() * (10000000 - 10000) + 10000, 2), 'feed', NOW() - INTERVAL FLOOR(RAND() * 80) DAY),
('sig25', (SELECT user_id FROM users WHERE username = 'Eve'), ROUND(RAND() * (10000000 - 10000) + 10000, 2), 'clean', NOW() - INTERVAL FLOOR(RAND() * 50) DAY),
('sig26', (SELECT user_id FROM users WHERE username = 'Frank'), ROUND(RAND() * (10000000 - 10000) + 10000, 2), 'play', NOW() - INTERVAL FLOOR(RAND() * 70) DAY),
('sig27', (SELECT user_id FROM users WHERE username = 'Grace'), ROUND(RAND() * (10000000 - 10000) + 10000, 2), 'feed', NOW() - INTERVAL FLOOR(RAND() * 85) DAY),
('sig28', (SELECT user_id FROM users WHERE username = 'Hank'), ROUND(RAND() * (10000000 - 10000) + 10000, 2), 'clean', NOW() - INTERVAL FLOOR(RAND() * 40) DAY),
('sig29', (SELECT user_id FROM users WHERE username = 'Ivy'), ROUND(RAND() * (10000000 - 10000) + 10000, 2), 'play', NOW() - INTERVAL FLOOR(RAND() * 90) DAY),
('sig30', (SELECT user_id FROM users WHERE username = 'Judy'), ROUND(RAND() * (10000000 - 10000) + 10000, 2), 'feed', NOW() - INTERVAL FLOOR(RAND() * 75) DAY);


