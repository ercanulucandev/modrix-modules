# Modüller

| Modül | Panelde | Sitede | Ana sayfa bölümü | Sayfaya ekleme |
|---|---|---|---|---|
| SSS | Tanıtım > SSS | `/faq` (Google FAQPage verisi) | Sıkça sorulanlar | `[faq]`, tek grup: `[faq odeme]` |
| Ekip | Tanıtım > Ekip | `/team` | Ekibimiz | `[team]` |
| Referanslar | Tanıtım > Yorumlar, Logolar | `/references` | Müşteri yorumları, Referans logoları | `[testimonials]`, `[clients]` |
| Portfolyo | Tanıtım > Portfolyo | `/portfolio`, `/portfolio/{adres}` (büyütülebilir galeri) | Portfolyo | `[gallery proje-adresi]` |
| Bülten | Pazarlama > Bülten (aboneler, CSV, toplu e-posta) | onay/ayrılma sayfaları | Bülten | `[newsletter]` |
| Çerez onayı | Modüller > Çerez onayı > Ayarlar | alt bant / köşe kutusu | — | tercihler: `<a href="#cookie-settings">` |
| Canlı destek | Modüller > Canlı destek > Ayarlar (Tawk.to, Crisp, Tidio) | sağ alt pencere | — | — |

Sayfaları menüye eklemek: Görünüm > Menüler > Hazır bağlantılar > "Modül sayfaları". Başlık ve açıklamalar her modülün
Ayarlar sayfasında. Sayfa tasarımını tema ezebilir: `app/Themes/{Tema}/modules/Faq/index.php` gibi.

**Bülten:** çift onay (önerilir) açıkken abone olan kişiye onay bağlantısı gider; KVKK/ETK onay kutusu ve aydınlatma metni
bağlantısı ayarlardan. Her bülten e-postasına "Abonelikten çık" bağlantısı eklenir. Gönderim Ayarlar > E-posta'yı kullanır;
binlerce abonede toplu e-posta servisi (Mailchimp, Brevo) daha uygundur: CSV'yi oraya aktarın.

**Çerez onayı:** açıkken Google Analytics (Ayarlar > SEO) "analitik", canlı destek "işlevsel" izni verilene kadar yüklenmez.
Kendi kodunuzu (Ayarlar > Özel kod) bekletmek için: `<script type="text/plain" data-consent="marketing">...</script>`
(dış dosya: `data-src="https://..."`). JS'ten: `ModrixConsent.get()` → `['analytics', ...]` ya da `null`, `ModrixConsent.open()`.

Kurulum: Modrix panelinde **Sistem > Mağaza**. Ayrıntılı rehberler: [Mağaza](shop.md), [Randevu](booking.md).
