-- Blog 1.1.0: tablo adına modül öneki (başka modüllerle çakışmasın)
-- 1.0.0'dan güncellenen kurulumlarda mevcut veriler korunur; yeni kurulumlarda ilk migration'dan hemen sonra çalışır.
RENAME TABLE posts TO blog_posts;
