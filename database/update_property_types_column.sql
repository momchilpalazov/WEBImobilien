-- Обновяване на колоната type в таблицата properties
ALTER TABLE properties MODIFY COLUMN type VARCHAR(50) NOT NULL;

-- Обновяване на съществуващите записи със старите типове към новите
UPDATE properties SET type = 'manufacturing' WHERE type IN ('industrial', 'manufacturing');
UPDATE properties SET type = 'logistics' WHERE type = 'logistics';
UPDATE properties SET type = 'office' WHERE type = 'office';
UPDATE properties SET type = 'logistics_park' WHERE type = 'warehouse';

-- Добавяне на индекс за по-бързо търсене по тип
ALTER TABLE properties ADD INDEX idx_property_type (type); 