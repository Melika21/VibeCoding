# AI Context — Request & Coupon System (RCS) WordPress Plugin

> این فایل برای هر ایجنت AI که در آینده بر این افزونه کار کند نوشته شده است. خواندن اجباری است قبل از شروع هر تغییر یا توسعه جدید.

---

## 1. هدف نهایی

توسعه یک افزونه وردپرس / ووکامرس به نام **Request & Coupon System** که شامل:
- ساخت فرم چندمرحله‌ای و مدیریت فیلدها
- ثبت درخواست و مدیریت اصلاحات (revisions)
- تاییدیه specialist (متخصص) و کد تخفیف WooCommerce
- اعلان‌های SMS (سرویس SMS.ir)
- لاگraš و گزارش‌گیری

در این مرحله فقط **skeleton و زیرساخت نصب/فعال‌سازی** کامل است. هیچ لاجیکک تجاری (business logic) اضافه نشده است.

---

## 2. نسخه و پیش‌نیازها

| مورد | مقدار |
|------|-------|
| نام افزونه | Request & Coupon System |
| Prefix کلاس‌ها | `RCS_` |
| نسخه افزونه | 1.0.0 |
| حداقل PHP | 7.4 |
| حداقل وردپرس | 6.0 |
| حداقل ووکامرس | 7.0 |
| Text Domain | `request-coupon-system` |
| Role اضافه شده | `rcs_expert` |

فایل‌ها:
- `D:\VibeCoding\kilo-ai\plugin.php` — نقطه ورود اصلی
- `D:\VibeCoding\kilo-ai\ai-context.md` — این فایل

---

## 3. ساختار دایرکتوری

```
kilo-ai/
├── plugin.php                    # نقطه ورود؛ ثابت‌ها، autoloader، هوک‌های نصب/حذف
├── admin/
│   └── Admin.php                 # RCS_Admin_Admin — پنل مدیریت (استبدال)
├── frontend/
│   └── Frontend.php              # RCS_Frontend_Frontend — فرانت‌اند (استبدال)
├── elementor/
│   ├── Elementor.php             # RCS_Elementor_Elementor — افزودن ویجت المنتور
│   └── widgets/
│       └── FormWidget.php        # RCS_Elementor_FormWidget — ویجت فرم
└── includes/
    ├── Core/
    │   ├── Plugin.php            # RCS_Core_Plugin — orquestrator zentrale
    │   ├── Activator.php         # RCS_Core_Activator — عملیات نصب
    │   ├── Deactivator.php       # RCS_Core_Deactivator — عملیات غیرفعال‌سازی
    │   ├── Uninstaller.php       # RCS_Core_Uninstaller — عملیات حذف کامل
    │   └── Requirements.php      # RCS_Core_Requirements — بررسی پیش‌نیازها
    ├── Forms/
    │   ├── FormManager.php
    │   ├── FormRepository.php
    │   ├── FieldManager.php
    │   ├── Submission-Handler.php
    │   ├── Validation.php
    │   ├── ConditionalLogic.php
    │   ├── DraftManager.php
    │   └── MultiStepManager.php
    ├── Requests/
    │   ├── RequestManager.php
    │   ├── RequestRepository.php
    │   ├── RequestStatus.php
    │   └── RequestAccess.php
    ├── Revisions/
    │   ├── RevisionManager.php
    │   └── RevisionRepository.php
    ├── Approval/
    │   ├── ApprovalManager.php
    │   ├── ApprovalValidator.php
    │   └── ExpertRole.php
    ├── Coupons/
    │   ├── CouponManager.php
    │   ├── CouponValidator.php
    │   ├── CouponOwnership.php
    │   └── WooCommerceCoupon.php
    ├── SMS/
    │   ├── SmsManager.php
    │   ├── SmsTemplateManager.php
    │   ├── SmsSender.php
    │   ├── SmsRetry.php
    │   └── SmsStatus.php
    ├── Scheduler/
    │   └── Scheduler.php
    ├── Logs/
    │   ├── LogManager.php
    │   └── LogRepository.php
    ├── Reports/
    │   └── ReportManager.php
    ├── Integrations/
    │   ├── WooCommerce/
    │   │   └── WooCommerceIntegration.php
    │   ├── SMSIr/
    │   │   └── SmsIrClient.php
    │   └── Elementor/
    │       └── ElementorIntegration.php
    └── Database/
        ├── Installer.php
        └── Schema.php
```

کلاس‌ها جميعًا **singleton** هستند (متد `instance()`،构造\_privated).

---

## 4. فلوی فعال‌سازی (Activation Flow)

1. وردپرس فایل `plugin.php` را include می‌کند.
2. `RCS_Autoloader::instance()` در خط 148 فراخوانی می‌شود → autoloader ثبت می‌شود **قبل از هر هوکی**.
3. `register_activation_hook` از **static method callback** استفاده می‌کند (`array('RCS_Core_Activator', 'activate')`) نه closure، چون closure قابل serialize نیست و باعث خطای "Serialization of 'Closure' is not allowed" می‌شود.
4. `RCS_Core_Activator::activate()` اجرا می‌شود:
   - `check_requirements()` — بررسی PHP/WP/WC و ذخیره خطاها در transient (بدون `wp_die` تا فعال‌سازی مسدود نشود)
   - `create_tables()` —dbDelta برای 7 جدول
   - `create_roles()` — افزودن نقش `rcs_expert`
   - `create_default_settings()` — تنظیمات پیش‌فرض (SMS.ir، expired coupon، ...)
   - `register_scheduled_tasks()` — رویداد روزانه `rcs_coupon_expiry_reminder`
5. `add_action('plugins_loaded', 'rcs_init')` برای khởi 동 لایفرانویم.

### جداول دیتابیس (dbDelta)
1. `rcs_forms`
2. `rcs_form_fields`
3. `rcs_requests`
4. `rcs_request_revisions`
5. `rcs_plugin_coupons`
6. `rcs_sms`
7. `rcs_logs`

---

## 5. Autoloader (manual map)

در `plugin.php` کلاس `final class RCS_Autoloader` تعریف شده است:
- Map دستی کلاس→مسیر Relative داخل `includes/`
- مسیرهای `admin/`، `frontend/`، `elementor/` با `../` شروع می‌شوند (چون径اب از `includes/` است)
- پسوند `.php` به نام کلاس اضافه می‌شود اگر در map نباشد
- اگر فایل readable نباشد، false برگردانده می‌شود (بدون خطا)

نکته مهم: کلیدهای map باید دقیقاً با نام کلاس (بدون `RCS_`) مطابقت داشته باشند.

---

## 6. خطاهای Raised شده و رفع شده

| خطا | علت | رفع |
|-----|------|-----|
| `Serialization of 'Closure' is not allowed` | `register_uninstall_hook` با closure | تغییر به static method callback |
| `ParseError: unexpected token "private"` | `{` اضافی در `Activator.php` | حذف `{` اضافی |
| `Cannot redeclare elementor_fail_php_version()` | المنتور دو بار لود می‌شود (رایگان + Pro) | **نه خطای افزونه ما** — نیاز به حذف/install مجدد المنتور |

---

## 7. حالت فعلی کد

- افزونه **نصب و فعال شده است** (تأیید شده در `wp-content/debug.log` با لاگ‌های `RCS: activation completed`)
- فعال‌سازی با موفقیت انجام می‌شود؛ جدول‌ها، نقش‌ها، تنظیمات ایجاد می‌شوند
- خطای الوحيد باقی‌مانده از **عنصر المنتور** است (تکرار تابع در فایل خود المنتور)، وابسته به نصب دुभل المنتور نیست

---

## 8. electro (Elementor) Integration

`elementor/Elementor.php`:
- `init()` بررسی می‌کند `Elementor\Plugin` وجود داشته باشد قبل ثبت هوک‌ها
- `try-catch` در sekitar ثبت هوک‌ها
- `register_widgets()` فقط اگر `RCS_Elementor_FormWidget` وجود داشته باشد اجرا می‌شود

`elementor/widgets/FormWidget.php`:
- کلاس `RCS_Elementor_FormWidget extends \Elementor\Widget_Base`
- `get_categories()` اگر دسته `rcs` ثبت نشده باشد، به `general` بازمی‌گردد تا خطای دسته نامشخص در المنتور 4.x را tránh کند

`includes/Core/Plugin.php::init_elementor()`:
- در `try-catch` لRFاف شده است

---

## 9. دستورالعمل برای توسعه بخش‌های بعدی

### مراحل پیشنهادی
1. **estore** کد از گیت (حالت کامیت شده)
2. فعال‌سازی افزونه در سایت آزمایشی با وردپرس 6.0+ و ووکامرس 7.0+
3. بررسی `wp-content/debug.log` برای لاگ‌های `RCS:`
4. شروع پیاده‌سازی ماژول‌هاone by one:
   - `Forms/FormManager.php` — ساخت فرم
   - `Requests/RequestManager.php` — ثبت درخواست
   - `Approval/ApprovalManager.php` — تایید specialist
   - `Coupons/CouponManager.php` — کد تخفیف WooCommerce
   - `SMS/SmsManager.php` — اعلان SMS.ir
5. هر کلاس جدید باید با prefix `RCS_` و در مسیر صحیح در `includes/` باشد و به autoloader map در `plugin.php` اضافه شود.

### قوانین کدنویسی
- همه کلاس‌ها `singleton` باشند (متد `instance()`،构造\_privated)
- نام کلاس‌ها با `RCS_` شروع شوند
- نام فایل‌ها با نام کلاس مطابقت داشته باشند
- از `try-catch (\Throwable)` در تمام نقاطی که با وردپرس/عنصر‌های خارجی تعامل دارند استفاده شود
- تماس مستقیم با `$wpdb` فقط در کلاس‌های `Database/` و `Logs/`
- هیچ کامنت فارسی/انگلیسی در کد PHP ننویسید (برخلاف این فایل markdown)

---

## 10. فایل‌های کلی برای مراجعه سریع

| کار | فایل |
|-----|------|
| نقطه ورود / autoloader / هوک‌ها | `plugin.php` |
| ऑर्कستراتور در `plugins_loaded` | `includes/Core/Plugin.php` |
| عملیات نصب (جداول، نقش، تنظیمات) | `includes/Core/Activator.php` |
| ساختار دیتابیس (dbDelta) | `includes/Database/Schema.php` |
| بررسی پیش‌نیازها | `includes/Core/Requirements.php` |
| پاک‌سازی در uninstall | `includes/Core/Uninstaller.php` |
| ویجت المنتور | `elementor/widgets/FormWidget.php` |
| استاداتارضی | `ai-context.md` (این فایل) |

---

> پایان سند. هر ایجنت بعدی باید این فایل را بخواند قبل از هر تغییر در کد افزونه.