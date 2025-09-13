USE `sparta_academy`;

-- Seed users
INSERT INTO users (email, password, first_name, last_name, roles, is_active)
VALUES
  ('alice@example.com', '$2y$10$abcdefghijklmnopqrstuvJKLmnopqrstu1234567890ab', 'Alice', 'Anderson', JSON_ARRAY('ROLE_USER'), true),
  ('bob@example.com',   '$2y$10$abcdefghijklmnopqrstuvJKLmnopqrstu1234567890ab', 'Bob',   'Baxter',   JSON_ARRAY('ROLE_USER'), true),
  ('carol@example.com', '$2y$10$abcdefghijklmnopqrstuvJKLmnopqrstu1234567890ab', 'Carol', 'Clark',    JSON_ARRAY('ROLE_USER'), true),
  ('dave@example.com',  '$2y$10$abcdefghijklmnopqrstuvJKLmnopqrstu1234567890ab', 'Dave',  'Dawson',   JSON_ARRAY('ROLE_USER'), true),
  ('erin@example.com',  '$2y$10$abcdefghijklmnopqrstuvJKLmnopqrstu1234567890ab', 'Erin',  'Ellis',    JSON_ARRAY('ROLE_USER'), true),
  ('admin@example.com', '$2y$10$abcdefghijklmnopqrstuvJKLmnopqrstu1234567890ab', 'Ada',   'Admin',    JSON_ARRAY('ROLE_USER','ROLE_ADMIN'), true)
ON DUPLICATE KEY UPDATE email = email;

-- Seed reviews (reference users by email)
INSERT INTO reviews (movie_title, rating, summary, review_body, director, actors, release_year, genre, user_id, is_reported, view_count)
VALUES
  ('The Matrix', 5, 'Mind-bending sci-fi', 'A hacker discovers reality is a simulation.', 'The Wachowskis', '["Keanu Reeves","Laurence Fishburne","Carrie-Anne Moss"]', 1999, 'Sci-Fi', (SELECT user_id FROM users WHERE email='alice@example.com'), false, 42),
  ('Inception', 5, 'Dreams within dreams', 'A thief steals secrets through dream-sharing technology.', 'Christopher Nolan', '["Leonardo DiCaprio","Joseph Gordon-Levitt","Elliot Page"]', 2010, 'Sci-Fi', (SELECT user_id FROM users WHERE email='bob@example.com'), false, 35),
  ('Parasite', 5, 'Masterful social satire', 'A poor family schemes to become employed by a wealthy household.', 'Bong Joon-ho', '["Song Kang-ho","Choi Woo-shik","Park So-dam"]', 2019, 'Thriller', (SELECT user_id FROM users WHERE email='carol@example.com'), false, 20),
  ('Interstellar', 4, 'Epic space odyssey', 'Explorers travel through a wormhole in search of a new home.', 'Christopher Nolan', '["Matthew McConaughey","Anne Hathaway","Jessica Chastain"]', 2014, 'Sci-Fi', (SELECT user_id FROM users WHERE email='dave@example.com'), false, 27),
  ('The Godfather', 5, 'Iconic crime drama', 'The aging patriarch of an organized crime dynasty transfers control to his reluctant son.', 'Francis Ford Coppola', '["Marlon Brando","Al Pacino","James Caan"]', 1972, 'Crime', (SELECT user_id FROM users WHERE email='erin@example.com'), false, 18),
  ('Spirited Away', 5, 'Enchanting fantasy', 'A girl finds herself in a world of spirits.', 'Hayao Miyazaki', '["Rumi Hiiragi","Miyu Irino","Mari Natsuki"]', 2001, 'Animation', (SELECT user_id FROM users WHERE email='alice@example.com'), false, 22),
  ('Whiplash', 4, 'Intense and gripping', 'A young drummer is pushed to his limits by a ruthless instructor.', 'Damien Chazelle', '["Miles Teller","J.K. Simmons"]', 2014, 'Drama', (SELECT user_id FROM users WHERE email='bob@example.com'), false, 16),
  ('Arrival', 4, 'Linguistics meets aliens', 'A linguist works to communicate with extraterrestrials.', 'Denis Villeneuve', '["Amy Adams","Jeremy Renner","Forest Whitaker"]', 2016, 'Sci-Fi', (SELECT user_id FROM users WHERE email='carol@example.com'), false, 14);

-- Seed comments
INSERT INTO comments (review_id, user_id, comment_body, is_edited)
VALUES
  ((SELECT review_id FROM reviews WHERE movie_title='The Matrix' AND user_id=(SELECT user_id FROM users WHERE email='alice@example.com')), (SELECT user_id FROM users WHERE email='bob@example.com'),   'Absolutely iconic!', false),
  ((SELECT review_id FROM reviews WHERE movie_title='The Matrix' AND user_id=(SELECT user_id FROM users WHERE email='alice@example.com')), (SELECT user_id FROM users WHERE email='carol@example.com'), 'Still holds up today.', false),
  ((SELECT review_id FROM reviews WHERE movie_title='Inception' AND user_id=(SELECT user_id FROM users WHERE email='bob@example.com')),    (SELECT user_id FROM users WHERE email='alice@example.com'), 'Love the ending debate.', false),
  ((SELECT review_id FROM reviews WHERE movie_title='Parasite' AND user_id=(SELECT user_id FROM users WHERE email='carol@example.com')),   (SELECT user_id FROM users WHERE email='dave@example.com'),  'Brilliant storytelling.', false),
  ((SELECT review_id FROM reviews WHERE movie_title='Interstellar' AND user_id=(SELECT user_id FROM users WHERE email='dave@example.com')),(SELECT user_id FROM users WHERE email='erin@example.com'),  'That soundtrack!', false),
  ((SELECT review_id FROM reviews WHERE movie_title='The Godfather' AND user_id=(SELECT user_id FROM users WHERE email='erin@example.com')),(SELECT user_id FROM users WHERE email='alice@example.com'), 'A masterpiece.', false),
  ((SELECT review_id FROM reviews WHERE movie_title='Spirited Away' AND user_id=(SELECT user_id FROM users WHERE email='alice@example.com')),(SELECT user_id FROM users WHERE email='erin@example.com'), 'Magical visuals.', false),
  ((SELECT review_id FROM reviews WHERE movie_title='Whiplash' AND user_id=(SELECT user_id FROM users WHERE email='bob@example.com')),     (SELECT user_id FROM users WHERE email='carol@example.com'), 'Intensity unmatched.', false),
  ((SELECT review_id FROM reviews WHERE movie_title='Arrival' AND user_id=(SELECT user_id FROM users WHERE email='carol@example.com')),    (SELECT user_id FROM users WHERE email='bob@example.com'),   'Thought-provoking.', false),
  ((SELECT review_id FROM reviews WHERE movie_title='Arrival' AND user_id=(SELECT user_id FROM users WHERE email='carol@example.com')),    (SELECT user_id FROM users WHERE email='dave@example.com'),  'Beautiful score.', false);

-- Seed likes/dislikes
INSERT INTO review_likes (review_id, user_id, is_like)
VALUES
  ((SELECT review_id FROM reviews WHERE movie_title='The Matrix' AND user_id=(SELECT user_id FROM users WHERE email='alice@example.com')),(SELECT user_id FROM users WHERE email='bob@example.com'),   true),
  ((SELECT review_id FROM reviews WHERE movie_title='The Matrix' AND user_id=(SELECT user_id FROM users WHERE email='alice@example.com')),(SELECT user_id FROM users WHERE email='carol@example.com'), true),
  ((SELECT review_id FROM reviews WHERE movie_title='Inception' AND user_id=(SELECT user_id FROM users WHERE email='bob@example.com')),   (SELECT user_id FROM users WHERE email='alice@example.com'), true),
  ((SELECT review_id FROM reviews WHERE movie_title='Parasite' AND user_id=(SELECT user_id FROM users WHERE email='carol@example.com')),  (SELECT user_id FROM users WHERE email='dave@example.com'),  true),
  ((SELECT review_id FROM reviews WHERE movie_title='Interstellar' AND user_id=(SELECT user_id FROM users WHERE email='dave@example.com')),(SELECT user_id FROM users WHERE email='erin@example.com'), false),
  ((SELECT review_id FROM reviews WHERE movie_title='The Godfather' AND user_id=(SELECT user_id FROM users WHERE email='erin@example.com')),(SELECT user_id FROM users WHERE email='alice@example.com'), true),
  ((SELECT review_id FROM reviews WHERE movie_title='Spirited Away' AND user_id=(SELECT user_id FROM users WHERE email='alice@example.com')),(SELECT user_id FROM users WHERE email='erin@example.com'), true),
  ((SELECT review_id FROM reviews WHERE movie_title='Whiplash' AND user_id=(SELECT user_id FROM users WHERE email='bob@example.com')),    (SELECT user_id FROM users WHERE email='carol@example.com'), true),
  ((SELECT review_id FROM reviews WHERE movie_title='Arrival' AND user_id=(SELECT user_id FROM users WHERE email='carol@example.com')),   (SELECT user_id FROM users WHERE email='bob@example.com'),   true),
  ((SELECT review_id FROM reviews WHERE movie_title='Arrival' AND user_id=(SELECT user_id FROM users WHERE email='carol@example.com')),   (SELECT user_id FROM users WHERE email='dave@example.com'),  true)
ON DUPLICATE KEY UPDATE user_id = user_id;
