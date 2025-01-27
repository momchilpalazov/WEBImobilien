-- Обновяване на типовете имоти
UPDATE properties SET type = 'manufacturing' WHERE type IN ('industrial', 'manufacturing');
UPDATE properties SET type = 'logistics' WHERE type = 'logistics';
UPDATE properties SET type = 'office' WHERE type = 'office';
UPDATE properties SET type = 'logistics_park' WHERE type = 'warehouse';

-- Попълване на английските полета
UPDATE properties SET 
    title_en = COALESCE(title_en, title_bg),
    location_en = COALESCE(location_en, location_bg),
    specification_en = COALESCE(specification_en, specification_bg),
    description_en = COALESCE(description_en, description_bg)
WHERE title_en IS NULL 
   OR location_en IS NULL 
   OR specification_en IS NULL 
   OR description_en IS NULL; 