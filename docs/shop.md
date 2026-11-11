# Mağaza (e-ticaret)

| | Nerede |
|---|---|
| Ürünler (varyant, stok, görseller, açıklama) | Admin > Ürünler |
| Kategoriler | Admin > Ürün kategorileri (ya da Mağaza > Kategoriler sekmesi) |
| Siparişler (durum, kargo takip no, ödeme onayı) | Admin > Siparişler |
| Kargo, ödeme yöntemleri, banka hesapları, bildirim | Modüller > Mağaza > Ayarlar |
| Sitede | `/shop`, `/shop/category/{adres}`, `/shop/{adres}`, `/cart`, `/checkout`, `/order/{token}` |
| Ana sayfa | Özelleştir > bölüm "Ürünler" (öne çıkan ya da son eklenen) |

## Akış

Sepet → ödeme formu (iletişim, adres, isteğe bağlı kurumsal fatura, ödeme yöntemi, **mesafeli satış sözleşmesi onayı**) →
sipariş oluşur, **stok o anda düşer** (aynı anda iki kişi son ürünü alamaz), müşteriye ve size e-posta gider.

| Yöntem | Sipariş sonrası |
|---|---|
| Havale / EFT | "Ödeme bekleniyor"; müşteri IBAN'ı görür. Parayı görünce sipariş sayfasında **Ödeme alındı** → "Hazırlanıyor" |
| Kapıda ödeme | Doğrudan "Hazırlanıyor"; isteğe bağlı ek ücret |
| iyzico | iyzico'nun güvenli ödeme sayfasına gider, dönüşte sonuç iyzico'ya sorulur → "Ödendi" |
| PayTR | Sitede PayTR çerçevesi; sonucu PayTR sunucusu Bildirim URL'ine yollar (imza doğrulanır) → "Ödendi" |

Kart ödemesi yarıda kalırsa müşteri sipariş sayfasındaki **Kartla öde** ile tekrar dener. İptal ve iade edilen siparişin
ürünleri stoğa geri eklenir. Durum değiştirirken "Müşteriye e-posta gönder" işaretliyse (ör. kargoya verildi + takip no)
müşteri bilgilendirilir. Sipariş sayfası adresi gizlidir (`/order/` + 32 karakter); müşteri giriş yapmadan takip eder.

## Kartla ödemeyi açmak

**iyzico:** iyzico.com'da üye işyeri olun; test için sandbox-merchant.iyzipay.com'dan ücretsiz test hesabı açın.
Ayarlar'a API anahtarı ve gizli anahtarı girin, "test modu" açıkken test kartlarıyla deneyin (ör. 5528790000000008,
12/30, 123), sonra test modunu kapatıp canlı anahtarları girin.

**PayTR:** Mağaza no, merchant key, merchant salt. PayTR mağaza panelinde **Bildirim URL**:
`https://siteniz.com/api/shop/paytr/notify` (bu adres olmadan ödemeler "Ödendi"ye geçmez). Test modu açıkken PayTR'ın
test kartlarıyla deneyin.

Anahtarlar veritabanında Ayarlar'da durur; gizli olanlar formda gösterilmez.

## Yasal

Ödeme sayfasında "Ön bilgilendirme formu ve mesafeli satış sözleşmesi" onayı zorunludur. Sözleşmeyi Sayfalar'da oluşturup
adresini Ayarlar > "Mesafeli satış sözleşmesi bağlantısı"na yazın. Çerez onayı, KVKK aydınlatma metni ve iade koşulları
sayfalarını da ekleyin.

## Geliştirici

- Sepet oturumda, fiyatlar her seferinde veritabanından; tutarlar kuruş cinsinden hesaplanır (`Shop::cents`).
- Sepet sayısı `modrix_cart` çerezinden JS ile yazılır (sayfa önbelleğiyle uyumlu). Sepet, ödeme, sipariş sayfaları önbelleğe girmez
  (`cache.skip` hook'u).
- Temalar üst kısımda `site.header_actions` hook'unu basar (sepet simgesi buradan gelir).
- Olaylar: `shop.order.created` ($sipariş), `shop.order.paid` ($sipariş), `shop.order.status` ($sipariş, $eskiDurum).
- Tema ezebilir: `app/Themes/{Tema}/modules/Shop/index.php`, `show.php`, `product-card.php`, `cart.php`...
