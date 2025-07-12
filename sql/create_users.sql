-- 1. Users
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password_hash TEXT NOT NULL,
    profile_image TEXT,
    points_balance INT DEFAULT 0,
    is_admin BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Items
CREATE TABLE items (
    id SERIAL PRIMARY KEY,
    user_id INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    category VARCHAR(100),
    type VARCHAR(50),
    size VARCHAR(20),
    condition VARCHAR(50),
    status VARCHAR(20) DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. Item Images
CREATE TABLE item_images (
    id SERIAL PRIMARY KEY,
    item_id INT NOT NULL REFERENCES items(id) ON DELETE CASCADE,
    image_url TEXT NOT NULL,
    is_primary BOOLEAN DEFAULT FALSE
);

-- 4. Tags
CREATE TABLE tags (
    id SERIAL PRIMARY KEY,
    name VARCHAR(50) UNIQUE NOT NULL
);

-- 5. Item Tags
CREATE TABLE item_tags (
    item_id INT REFERENCES items(id) ON DELETE CASCADE,
    tag_id INT REFERENCES tags(id) ON DELETE CASCADE,
    PRIMARY KEY (item_id, tag_id)
);

-- 6. Swaps
CREATE TABLE swaps (
    id SERIAL PRIMARY KEY,
    requester_id INT REFERENCES users(id),
    responder_id INT REFERENCES users(id),
    requester_item_id INT REFERENCES items(id),
    responder_item_id INT REFERENCES items(id),
    status VARCHAR(20) DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 7. Redemptions
CREATE TABLE redemptions (
    id SERIAL PRIMARY KEY,
    user_id INT REFERENCES users(id),
    item_id INT REFERENCES items(id),
    points_used INT NOT NULL,
    status VARCHAR(20) DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 8. Admin Actions
CREATE TABLE admin_actions (
    id SERIAL PRIMARY KEY,
    admin_id INT REFERENCES users(id),
    item_id INT REFERENCES items(id),
    action VARCHAR(50),
    reason TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 9. Activity Log
CREATE TABLE activity_log (
    id SERIAL PRIMARY KEY,
    user_id INT REFERENCES users(id),
    action_type VARCHAR(50),
    reference_id INT,
    message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
