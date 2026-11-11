DROP TABLE IF EXISTS booking_appointments;
DELETE FROM collection_items WHERE collection IN ('booking_services', 'booking_staff');
