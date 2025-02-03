-- Индекси за properties
ALTER TABLE properties
ADD INDEX idx_status_type (status, type),
ADD INDEX idx_price (price),
ADD INDEX idx_area (area),
ADD INDEX idx_created_at (created_at);

-- Индекси за inquiries
ALTER TABLE inquiries
ADD INDEX idx_property_status (property_id, status),
ADD INDEX idx_created_at (created_at);

-- Индекси за users
ALTER TABLE users
ADD INDEX idx_role_status (role, status);

-- Индекси за property_images
ALTER TABLE property_images
ADD INDEX idx_property_main (property_id, is_main); 