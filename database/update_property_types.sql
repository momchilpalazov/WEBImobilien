-- Първо обновяваме съществуващите записи
UPDATE properties SET type = 'industrial' WHERE type IN ('retail', 'warehouse');

-- След това променяме ENUM стойностите
ALTER TABLE properties MODIFY COLUMN type ENUM('industrial', 'logistics', 'office') NOT NULL; 