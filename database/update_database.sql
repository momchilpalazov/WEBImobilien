-- Обновяване на структурата на таблицата
ALTER TABLE properties MODIFY COLUMN type VARCHAR(50) NOT NULL;
ALTER TABLE properties MODIFY COLUMN title_en VARCHAR(255) NOT NULL;
ALTER TABLE properties MODIFY COLUMN location_en VARCHAR(255) NOT NULL;
ALTER TABLE properties ADD COLUMN IF NOT EXISTS specification_en TEXT AFTER specification_ru;
ALTER TABLE properties ADD COLUMN IF NOT EXISTS description_en TEXT AFTER description_ru;

-- Добавяне на индекси (ако не съществуват)
CREATE INDEX IF NOT EXISTS idx_property_type ON properties (type);
CREATE INDEX IF NOT EXISTS idx_property_status ON properties (status);
CREATE INDEX IF NOT EXISTS idx_property_featured ON properties (featured);

-- Обновяване на съществуващите записи
UPDATE properties SET type = 'manufacturing' WHERE type IN ('industrial', 'manufacturing');
UPDATE properties SET type = 'logistics' WHERE type = 'logistics';
UPDATE properties SET type = 'office' WHERE type = 'office';
UPDATE properties SET type = 'logistics_park' WHERE type = 'warehouse';

-- Задаване на стойности по подразбиране за новите колони
UPDATE properties SET 
    title_en = COALESCE(title_en, title_bg),
    location_en = COALESCE(location_en, location_bg),
    specification_en = COALESCE(specification_en, specification_bg),
    description_en = COALESCE(description_en, description_bg)
WHERE title_en IS NULL 
   OR location_en IS NULL 
   OR specification_en IS NULL 
   OR description_en IS NULL; 