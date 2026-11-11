# Randevu

| | Nerede |
|---|---|
| Randevular (bugün, yaklaşanlar, geçmiş; durum, not) | Admin > **Randevular** |
| Hizmetler (süre, fiyat, açıklama) | Randevular > Hizmetler |
| Personel (berber, doktor, danışman...) | Randevular > Personel |
| Çalışma saatleri, saat aralığı, kurallar, tatil günleri | Modüller > Randevu > Ayarlar (Randevular sayfasındaki "Çalışma saatleri ve kurallar") |
| Sitede | `/booking`, sayfalarda `[booking]`, ana sayfada "Randevu al" bölümü (form ya da düğme) |

## Nasıl çalışır

Ziyaretçi hizmeti, (birden çok personel varsa) kişiyi ya da "fark etmez"i, günü seçer; o günün **boş saatleri** gelir.
Saat ve iletişim bilgileriyle randevu oluşur, e-postasına randevu sayfasının bağlantısı gider (iptal de oradan).

- **Çalışma saatleri:** gün gün `09:00-18:00`; öğle arası için `09:00-12:00, 13:00-18:00`; boş gün kapalı.
  Tatiller: "Kapalı günler"e her satıra bir tarih.
- **Boş saat hesabı:** saat aralığı (ör. 30 dk) adımlarla, hizmet süresi kapanışa sığacak şekilde; "en az X saat önceden" ve
  "en fazla X gün sonrası" kuralları. Her personel aynı anda bir randevu alır; hiç personel yoksa tek takvim.
- **Personel ve hizmet:** personelde "Verdiği hizmetler"e hizmet numaraları (virgülle) yazılırsa sadece onları verir.
- **Onay:** "kendiliğinden onaylansın" kapalıysa randevu "Onay bekliyor" olur (menüde sayısı görünür); siz onaylayınca
  müşteriye e-posta gider. Durum değişikliklerinde "müşteriye e-posta gönder" işaretliyse bildirilir.
- **İptal:** müşteri, randevuya ayarlardaki saatten (varsayılan 24) fazla varken kendi sayfasından iptal eder.
- Aynı saati iki kişinin aynı anda alması engellenir (veritabanı kilidi).

Olaylar: `booking.created` ($randevu), `booking.status` ($randevu, $eskiDurum). Personel eklenmeden önce alınmış
randevular (personelsiz) eklenen personelin takviminde yer tutmaz.
