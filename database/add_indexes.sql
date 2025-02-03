-- Добавяне на индекси
ALTER TABLE properties 
    ADD INDEX idx_property_type (type),
    ADD INDEX idx_property_status (status),
    ADD INDEX idx_property_featured (featured); 