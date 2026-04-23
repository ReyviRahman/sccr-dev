-- =====================================================
-- SQL untuk menambahkan permission ABS_HRD_MONITOR
-- Jalankan di MySQL database sccr_db
-- =====================================================

-- 1. Insert permission ke tabel auth_permissions
INSERT INTO auth_permissions (code, module_code, created_at, updated_at) 
VALUES ('ABS_HRD_MONITOR', '01001', NOW(), NOW());

-- Verifikasi data
SELECT * FROM auth_permissions WHERE code = 'ABS_HRD_MONITOR';

-- 2. (Opsional) Assign permission ke role admin HR
-- Uncomment baris di bawah jika ingin langsung assign ke role
-- INSERT INTO auth_role_permissions (role_id, permission_id, created_at, updated_at)
-- SELECT r.id, p.id, NOW(), NOW()
-- FROM auth_roles r
-- CROSS JOIN auth_permissions p
-- WHERE r.code = 'admin_hr'  -- sesuaikan dengan kode role admin HR
-- AND p.code = 'ABS_HRD_MONITOR';
