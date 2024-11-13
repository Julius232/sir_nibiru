-- 1% of EGG_PARTY_TARGET
INSERT INTO donations (signature, user_id, donation_amount, action, timestamp)
VALUES ('sig_1', 38, 10000.00, 'play', NOW());

-- 10% of EGG_PARTY_TARGET
INSERT INTO donations (signature, user_id, donation_amount, action, timestamp)
VALUES ('sig_2', 38, 100000.00, 'clean', NOW());

-- 25% of EGG_PARTY_TARGET
INSERT INTO donations (signature, user_id, donation_amount, action, timestamp)
VALUES ('sig_3', 38, 250000.00, 'feed', NOW());

-- 50% of EGG_PARTY_TARGET
INSERT INTO donations (signature, user_id, donation_amount, action, timestamp)
VALUES ('sig_4', 38, 500000.00, 'play', NOW());

-- 75% of EGG_PARTY_TARGET
INSERT INTO donations (signature, user_id, donation_amount, action, timestamp)
VALUES ('sig_5', 38, 750000.00, 'clean', NOW());

-- 90% of EGG_PARTY_TARGET
INSERT INTO donations (signature, user_id, donation_amount, action, timestamp)
VALUES ('sig_6', 38, 900000.00, 'feed', NOW());

-- 99% of EGG_PARTY_TARGET
INSERT INTO donations (signature, user_id, donation_amount, action, timestamp)
VALUES ('sig_7', 38, 990000.00, 'play', NOW());

-- 100% of EGG_PARTY_TARGET (End of Egg Party)
INSERT INTO donations (signature, user_id, donation_amount, action, timestamp)
VALUES ('sig_8', 38, 1000000.00, 'clean', NOW());

DELETE FROM donations WHERE user_id = 38 AND signature IN ('sig_1', 'sig_2', 'sig_3', 'sig_4', 'sig_5', 'sig_6', 'sig_7', 'sig_8');
