# Modrix modülleri

[Modrix](https://github.com/ercanulucandev/modrix) için resmi modüller. Sitelerde **Sistem > Mağaza > Modüller**'den tek tıkla
kurulur ve güncellenir; her paket SHA-256 özetiyle doğrulanır.

| Modül | Ne işe yarar |
|---|---|
| Sayfalar | Hakkımızda, İletişim gibi sabit sayfalar; alt sayfalar, şablonlar |
| Blog | Yazılar, kategoriler, etiketler, kapak görseli, zamanlanmış yayın, RSS |
| Formlar | Sürükle-bırak form oluşturucu, gelen kutusu, e-posta bildirimi, spam koruması |
| Mağaza | Ürün, varyant, stok, sepet; havale, kapıda ödeme, iyzico, PayTR; siparişler |
| Randevu | Hizmetler, personel, çalışma saatleri, boş saat seçimi, onay ve iptal |
| SSS · Ekip · Referanslar · Portfolyo | Tanıtım içerikleri; sayfa, ana sayfa bölümü ve kısa kodlarla |
| Bülten | Çift onaylı abonelik, KVKK onay kutusu, toplu e-posta |
| Çerez onayı | KVKK/GDPR çerez bildirimi; onay verilene kadar analiz ve canlı destek kodları çalışmaz |
| Canlı destek · WhatsApp | Tawk.to / Crisp / Tidio, WhatsApp düğmesi |

Rehberler: [Modüller](docs/modules.md) · [Mağaza (e-ticaret)](docs/shop.md) · [Randevu](docs/booking.md)

## Depo düzeni

| | |
|---|---|
| `catalog.json` | Sitelerin okuduğu katalog: sürüm, açıklama, kategori, uyumluluk, fiyat, indirme adresi, SHA-256 |
| `packages/` | Kurulum paketleri (ZIP) |
| `modules/` | Okunabilir kaynak kod |
| `icons/` | Modül simgeleri |

Modül geliştirmek: [Modül yazma rehberi](https://github.com/ercanulucandev/modrix/blob/main/docs/module-tutorial.md) ·
yayınlamak: çekirdeğin yanında bu depo `modrix-modules` adıyla dururken `php modrix store:build`
([ayrıntı](https://github.com/ercanulucandev/modrix/blob/main/docs/store.md)).
