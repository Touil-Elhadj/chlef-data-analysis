# دليل التثبيت — Guide d'installation

## التثبيت على alwaysdata (الطريقة المُوصى بها)

### الطريقة 1: عبر SSH (الأسهل والأسرع)

1. **الاتّصال بالسيرفر**:
   ```bash
   ssh ssh-username@ssh-username.alwaysdata.net
   # استبدل username باسم حسابك على alwaysdata
   ```

2. **الانتقال إلى مجلّد www**:
   ```bash
   cd www/
   ```

3. **سحب المشروع من GitHub**:
   ```bash
   git clone https://github.com/Touil-Elhadj/chlef-touilelhadj.git .
   ```

4. **تثبيت المكتبات عبر Composer**:
   ```bash
   composer install --no-dev --optimize-autoloader
   ```

   هذا الأمر سيُنزّل تلقائيًّا مكتبة `touilelhadj/biostat-php` من Packagist
   ويضعها في `vendor/touilelhadj/biostat-php/`.

5. **إعداد ملف `.env`**:
   ```bash
   cp .env.example .env
   nano .env
   ```
   املأ بيانات قاعدة البيانات (الموجودة في لوحة alwaysdata).

6. **استيراد قاعدة البيانات**:
   ```bash
   mysql -h mysql-username.alwaysdata.net -u username -p database_name < database/schema.sql
   ```

7. **إعداد المسؤول**:
   ```bash
   php bin/seed-admin.php
   ```

---

### الطريقة 2: عبر FTP فقط (للذين لا يستطيعون استخدام SSH)

إذا كانت استضافتك لا تدعم SSH، يجب أن تُشغّل composer **على جهازك المحلّي**
ثم ترفع كلّ شيء عبر FTP.

#### على جهازك (Windows / Mac / Linux):

1. **تنزيل composer** (إن لم يكن مُثبّتًا):
   - Windows: حمّل من https://getcomposer.org/download/ وثبّت
   - Mac/Linux: `curl -sS https://getcomposer.org/installer | php`

2. **استنساخ المشروع**:
   ```bash
   git clone https://github.com/Touil-Elhadj/chlef-touilelhadj.git
   cd chlef-touilelhadj
   ```

3. **تثبيت المكتبات محلّيًّا**:
   ```bash
   composer install --no-dev --optimize-autoloader
   ```

   هذا يُنشئ مجلّد `vendor/` كاملًا على جهازك.

4. **رفع كلّ شيء عبر FileZilla**:
   - اتّصل بـ FTP باستخدام بيانات alwaysdata
   - ارفع **كلّ** المجلّدات والملفّات، بما في ذلك مجلّد `vendor/` كاملًا
   - تأكّد من رفع الملفّات المخفيّة (`.htaccess`, `.env`)

5. **إعداد قاعدة البيانات**:
   - افتح phpMyAdmin من لوحة alwaysdata
   - استورد ملفّ `database/schema.sql`

---

## ⚠️ تحديث المنصّة لاحقًا

### إذا استخدمت SSH:
```bash
ssh user@user.alwaysdata.net
cd www/
git pull origin main
composer install --no-dev --optimize-autoloader   # في حال تحديث المكتبة
```

### إذا استخدمت FTP:
```bash
# على جهازك:
cd chlef-touilelhadj
git pull origin main
composer install --no-dev --optimize-autoloader

# ثم ارفع التغييرات (vendor/ مُتضمَّن) عبر FileZilla
```

---

## التحقّق من نجاح التثبيت

افتح المتصفّح وانتقل إلى:
```
https://your-domain.alwaysdata.net/index.php
```

ثم سجّل دخولًا كمسؤول وافتح:
```
https://your-domain.alwaysdata.net/tests/run_tests.php
```

يجب أن ترى نتائج اختبارات الوحدة. إذا ظهرت أخطاء PHP، تحقّق من:

1. **وجود مجلّد `vendor/`** في الجذر:
   ```bash
   ls vendor/touilelhadj/biostat-php/src/
   # يجب أن ترى: BiostatAnalysis.php  Distributions.php  LinearAlgebra.php  Exceptions/
   ```

2. **سماحيّات الملفات** (إن ظهر خطأ "Permission denied"):
   ```bash
   chmod -R 755 vendor/
   chmod -R 644 vendor/*.php
   ```

3. **ملفّ `.htaccess`** يحجب الوصول لمجلّد `vendor/`:
   تأكّد أنّ ملف `.htaccess` في الجذر يحتوي على:
   ```
   <DirectoryMatch "/(vendor|database|logs|tests|bin)/">
       Require all denied
   </DirectoryMatch>
   ```

---

## الأخطاء الشائعة

### `Fatal error: Class "TouilElhadj\BiostatPhp\BiostatAnalysis" not found`

السبب: مجلّد `vendor/` غير موجود أو غير مكتمل.

الحل:
```bash
composer install --no-dev --optimize-autoloader
```
أو إذا كنت ترفع عبر FTP، أعد رفع مجلّد `vendor/` كاملًا.

### `composer: command not found`

السبب: composer غير مُثبّت على السيرفر أو على جهازك.

الحل على alwaysdata: composer مُثبّت مسبقًا في `/usr/local/bin/composer`.
إذا لم يُتعرّف عليه، استخدم المسار الكامل:
```bash
/usr/local/bin/composer install --no-dev
```

---

## للمزيد من المعلومات

- مكتبة biostat-php: https://github.com/Touil-Elhadj/biostat-php
- توثيق alwaysdata: https://help.alwaysdata.com/
- توثيق Composer: https://getcomposer.org/doc/

---

## En français (court résumé)

### Installation rapide via SSH (recommandé)

```bash
ssh user@user.alwaysdata.net
cd www/
git clone https://github.com/Touil-Elhadj/chlef-touilelhadj.git .
composer install --no-dev --optimize-autoloader
cp .env.example .env
nano .env   # remplir DB credentials
mysql -h mysql-user.alwaysdata.net -u user -p db_name < database/schema.sql
php bin/seed-admin.php
```

### Mise à jour

```bash
git pull origin main
composer install --no-dev --optimize-autoloader
```
