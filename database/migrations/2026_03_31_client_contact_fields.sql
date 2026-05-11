ALTER TABLE clients
    ADD COLUMN contact_role VARCHAR(30) NULL AFTER contact_name,
    ADD COLUMN secondary_contact_name VARCHAR(150) NULL AFTER phone,
    ADD COLUMN secondary_contact_role VARCHAR(30) NULL AFTER secondary_contact_name,
    ADD COLUMN second_email VARCHAR(190) NULL AFTER secondary_contact_role,
    ADD COLUMN second_phone VARCHAR(60) NULL AFTER second_email;
