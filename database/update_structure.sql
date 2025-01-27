-- Обновяване на структурата на таблицата
ALTER TABLE properties MODIFY COLUMN type VARCHAR(50) NOT NULL;
ALTER TABLE properties MODIFY COLUMN title_en VARCHAR(255) NOT NULL;
ALTER TABLE properties MODIFY COLUMN location_en VARCHAR(255) NOT NULL; 