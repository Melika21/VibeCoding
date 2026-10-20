# AI Context — Request & Coupon System (RCS) WordPress Plugin

> این فایل مرجع قطعی پروژه است. **قبل از هر تغییری آن را کامل بخوانید.**
> هر ایجنتی که روی این افزونه کار می‌کند باید پس از خواندن این فایل بداند: چه چیزی ساخته شده، چه چیزی هنوز ساخته نشده، گردش داده چگونه است، و قدم بعدی دقیقاً چیست.
> نسخه فعلی: **۱.۰.۰ (اسکلت / زیرساخت)** — فاز پیاده‌سازی منطق کسب‌وکار هنوز آغاز نشده است.

---

## ۱. هدف نهایی

افزونه‌ای ماژولار برای وردپرس/ووکامرس که یک گردش کامل را پوشش می‌دهد:

```text
کاربر
  ↓
ویجت فرم المنتور (یا رندر مستقل)
  ↓
موتور فرم (Forms)
  ↓
درخواست (Request)
  ↓
نسخه‌های اصلاحی (Revision)
  ↓
تأیید/رد کارشناس (Approval)
  ↓
ساخت کوپن (Coupons → WooCommerce)
  ↓
اعلان پیامک (SMS → SMS.ir)
```

امکانات نهایی:

- ساخت فرم (چندمرحله‌ای) و مدیریت فیلدها با منطق شرطی
- ثبت درخواست، پیش‌نویس، و چند نسخه اصلاحی با حفظ تاریخچه
- تأیید/رد توسط کارشناس با تعیین درصد و سقف تخفیف
- ساخت **دقیقاً یک** کوپن WooCommerce برای هر درخواست تأییدشده
- اعلان پیامکی مبتنی بر Pattern در SMS.ir (مشتری + مدیر + یادآوری انقضا)
- لاگ کامل ممیزی (Audit Log) و گزارش‌گیری
- گزینه بازگردانی/تغییر کوپن توسط مدیر
- اتصال المنتور صرفاً به‌عنوان لایه نمایش

**WooCommerce مرجع اصلی (source of truth) کوپن است.** افزونه هرگز موتور تخفیف موازی نمی‌سازد؛ فقط رابطه‌های موردنیاز خودش را در جدول‌های خودش نگه می‌دارد.

---

## ۲. نسخه و پیش‌نیازها

| مورد | مقدار |
|------|-------|
| نام افزونه | Request & Coupon System |
| نسخه | 1.0.0 |
| پیشوند ثابت‌ها | `RCS_` |
| پیشوند کلاس‌ها | `RCS_` (بدون namespace — سراسری) |
| حداقل PHP | 7.4 |
| حداقل وردپرس | 6.0 |
| حداقل ووکامرس | 7.0 (اختیاری؛ فقط برای کوپن لازم است) |
| Text Domain | `request-coupon-system` |
| Role اختصاصی | `rcs_expert` |
| پیشوند جدول‌ها | `rcs_` |
| پیشوند کوپن‌ها | `rcs_` |
| پیشوند Optionها | `rcs_` |
| پیشوند هوک‌ها | `rcs_` |
| پیشوند Capabilityها | `rcs_` |

---

## ۳. وضعیت فعلی (مهم‌ترین بخش)

### ۳.۱ کاملاً پیاده‌سازی‌شده و تست‌شده

| فایل | مسئولیت |
|------|----------|
| `plugin.php` | هدر افزونه، ثابت‌ها، `RCS_Autoloader` (نقشه دستی)، رجیستر کردن قلاب‌های فعال/غیرفعال‌سازی، بوت `RCS_Core_Plugin` |
| `uninstall.php` | نقطه ورود حذف: تعریف `RCS_UNINSTALLING` و فراخوانی `RCS_Core_Uninstaller::uninstall()` |
| `includes/Core/Plugin.php` | ارکستراتور: بوت، بارگذاری textdomain، راه‌اندازی ماژول‌ها روی `plugins_loaded` (اولویت ۲۰) |
| `includes/Core/Activator.php` | بررسی پیش‌نیازها، ساخت جدول‌ها، نقش‌ها، کپسیتی‌ها، تنظیمات پیش‌فرض، زمان‌بندی |
| `includes/Core/Deactivator.php` | لغو زمان‌بندی + flush rewrite (داده حفظ می‌شود) |
| `includes/Core/Uninstaller.php` | حذف کامل داده: کوپن‌های ساخته‌شده توسط افزونه، جدول‌ها، optionها، نقش، کپسیتی‌ها |
| `includes/Core/Requirements.php` | بررسی PHP/WP/WooCommerce/Elementor؛ خطاها در transient ذخیره می‌شوند (فعال‌سازی مسدود نمی‌شود) |
| `includes/Database/Schema.php` | تعریف ۷ جدول + `CREATE TABLE` برای dbDelta + `table_exists()` + `drop()` |
| `includes/Database/Installer.php` | `install()` / `maybe_upgrade()` / `drop()` |
| `includes/Logs/LogManager.php` | نوشتن رکورد ممیزی (actor، role، action، IP، نتیجه، خطا) |
| `includes/Logs/LogRepository.php` | درج در جدول `rcs_logs` |
| `includes/Scheduler/Scheduler.php` | ثبت/لغو کرون‌های روزانه و ساعتی |
| `includes/Approval/ExpertRole.php` | ساخت/به‌روزرسانی/حذف نقش `rcs_expert` و کپسیتی‌های آن |
| `admin/Admin.php` | ثبت منوی پنل + ۶ زیرمنو + نمایش notice خطاهای پیش‌نیاز |
| `admin/pages/FormsPage.php` | فرم‌ساز: فهرست فرم‌ها، صفحه ساخت/ویرایش، ذخیره و حذف با nonce و کپسیتی |
| `admin/views/forms/index.php` | جدول فهرست فرم‌ها |
| `admin/views/forms/builder.php` | صفحه ساخت فرم: نام، وضعیت، توضیح و ردیف‌های فیلد |
| `admin/assets/css/rcs-forms.css` + `admin/assets/js/rcs-forms.js` | استایل و اسکریپت فرم‌ساز (افزودن/حذف فیلد) |
| `includes/Forms/FormManager.php` | منطق فرم: ساخت، ویرایش، حذف، خواندن، فهرست فرم‌های منتشرشده |
| `includes/Forms/FormRepository.php` | دسترسی داده به `rcs_forms` (درج، به‌روزرسانی، حذف، خواندن، کوئری) |
| `includes/Forms/FieldManager.php` | تعریف ۱۰ نوع فیلد، ذخیره/خواندن فیلدها، نرمال‌سازی و تولید markup هر فیلد |
| `includes/Forms/SubmissionHandler.php` | endpoint ارسال فرم: بررسی عضو بودن کاربر، بررسی nonce، اعتبارسنجی مقادیر، ثبت لاگ |
| `frontend/Frontend.php` | رندر فرم + وضعیت عضویت بازدیدکننده + رجیستر/enqueue استایل فرم + فعال‌سازی endpoint ارسال |
| `frontend/views/form.php` | قالب HTML فرم (فیلدها، nonce، دکمه ارسال، پیام نتیجه) و پیام ورود برای مهمان |
| `frontend/assets/css/rcs-form.css` | استایل فرم (grid، برچسب، input، notice) |
| `elementor/Elementor.php` | اتصال به لایه یکپارچه‌سازی المنتور |
| `elementor/widgets/FormWidget.php` | ویجت فرم: انتخاب فرم، عنوان/توضیح، متن دکمه، چیدمان ستون‌ها |
| `includes/Integrations/Elementor/ElementorIntegration.php` | تشخیص المنتور، ثبت دسته `rcs`، ثبت ویجت (در try/catch) |
| `includes/Integrations/SMSIr/SmsIrClient.php` | ارتباط با API سرویس‌دهنده: احراز هویت، ارسال Pattern، پاسخ و خطای سرویس‌دهنده |
| `includes/SMS/SmsManager.php` | منطق پیامک: ساخت رکورد، ارسال به مشتری/مدیر، نرمال‌سازی شماره، خواندن رکوردها |
| `includes/SMS/SmsTemplateManager.php` | انواع پیامک، شناسه Pattern و نگاشت پارامترها |
| `includes/SMS/SmsSender.php` | ارسال یک رکورد از طریق کلاینت سرویس‌دهنده |
| `includes/SMS/SmsRetry.php` | تلاش مجدد روی همان رکورد پیامک ناموفق |
| `admin/pages/SmsPage.php` | صفحه تنظیمات و تاریخچه پیامک + عملیات تلاش مجدد (با nonce و کپسیتی) |
| `admin/views/sms/index.php` | فرم تنظیمات SMS.ir و جدول تاریخچه پیامک‌ها |
| `includes/Integrations/WooCommerce/WooCommerceIntegration.php` | تشخیص ووکامرس + جای قلاب‌های ووکامرس |
| `includes/Integrations/SMSIr/SmsIrClient.php` | نگهداری کلاینت SMS.ir (فعلاً فقط بررسی پیکربندی) |
| `includes/Requests/RequestStatus.php` | ثابت‌های وضعیت درخواست |
| `includes/SMS/SmsStatus.php` | ثابت‌های وضعیت پیامک |

### ۳.۲ فقط اسکلت (کلاس خالی، بدون منطق)

`RCS_Forms_Validation`، `RCS_Forms_ConditionalLogic`، `RCS_Forms_DraftManager`، `RCS_Forms_MultiStepManager`، `RCS_Requests_RequestManager`، `RCS_Requests_RequestRepository`، `RCS_Requests_RequestAccess`، `RCS_Revisions_RevisionManager`، `RCS_Revisions_RevisionRepository`، `RCS_Approval_ApprovalManager`، `RCS_Approval_ApprovalValidator`، `RCS_Coupons_CouponManager`، `RCS_Coupons_CouponValidator`، `RCS_Coupons_CouponOwnership`، `RCS_Coupons_WooCommerceCoupon`، `RCS_Reports_ReportManager`.

**قانون:** هر کلاس اسکلت، بدنه خالی دارد و فقط `instance()` دارد. منطق کسب‌وکار را داخل همین کلاس‌ها بنویسید، نه در فایل/کلاس جدید.

---

## ۳.۳ قابلیت پیاده‌سازی‌شده: فرم‌ساز + ویجت المنتور

### چه چیزی ساخته شد

- **فرم‌ساز در پنل** (`Request & Coupon > Forms`): فهرست فرم‌ها، ساخت فرم جدید، ویرایش، حذف (با nonce و کپسیتی `rcs_manage_forms`).
- **تعریف فیلد** با ۱۰ نوع مصوب: text، textarea، number، email، mobile، select، radio، checkbox، date، file — هر فیلد دارای key، label، required، options، step و sort_order.
- **رندر فرم در فرانت** با قالب `frontend/views/form.php`، nonce، نام فیلدها به شکل `rcs_field[key]` و استایل مستقل `rcs-form`.
- **ویجت المنتور** `RCS Form` با کنترل‌های: انتخاب فرم (فقط فرم‌های منتشرشده)، نمایش عنوان، نمایش توضیح، متن دکمه ارسال و چیدمان ۱/۲/۳ ستونی. ویجت در دسته‌ی اختصاصی **Request & Coupon** (`rcs`) ظاهر می‌شود.
- **endpoint ارسال** `admin-post.php?action=rcs_submit_form`: بررسی nonce، اعتبارسنجی فیلدهای الزامی/ایمیل/موبایل، ثبت در لاگ ممیزی و بازگشت با پیام نتیجه.

### سازگاری با المنتور ۴.x (Elementor 4.3.5)

| نکته | تصمیم |
|-------|--------|
| کلاس پایه | `extends \Elementor\Widget_Base` (امضای `register( Widget_Base )` در المنتور ۴ تایپ‌هینت دارد) |
| `is_dynamic_content()` | با امضای `: bool` مطابق والد در المنتور ۴ |
| لود شدن فایل ویجت | کلاس داخل `if ( class_exists( '\Elementor\Widget_Base' ) )` اعلام شده تا بدون المنتور هرگز fatal ندهد |
| ثبت دسته | `elementor/elements/categories_registered` + `add_category()` (در ۴.x taxonomy قدیمی `elementor_element_category` حذف شده) |
| fallback دسته | اگر دسته `rcs` ثبت نشده باشد `general` برگردانده می‌شود (سازگار با ۳.x) |
| گزینه‌های کنترل SELECT | در فرانت به‌خاطر `Performance::should_optimize_controls()` حذف می‌شوند (رفتار خود المنتور)؛ در ادیتور سالم هستند |

### آنچه هنوز در این قابلیت انجام نشده

- ارسال فرم **درخواست/نسخه ایجاد نمی‌کند**؛ فقط اعتبارسنجی و ثبت لاگ انجام می‌شود. ساخت Request و Revision در فاز ماژول Requests انجام خواهد شد.
- منطق شرطی، چندمرحله‌ای و آپلود فایل (اعتبارسنجی MIME/حجم) هنوز پیاده نشده‌اند.
- استایل ادیتور المنتور (لحظه‌ای) برای ویجت اضافه نشده است.

---

## ۳.۵ قابلیت پیاده‌سازی‌شده: الزام عضو بودن کاربر (فرم‌ها فقط برای اعضا)

### قانون

ارسال هر فرم فقط برای **کاربر واردشده (عضو)** مجاز است. این قانون **سراسری** است و در حال حاضر قابل خاموش‌کردن نیست.

### رفتار مهمان (کاربر وارد نشده)

| مورد | رفتار |
|------|-------|
| نمایش فرم در فرانت | به‌جای فرم، پیام «Only registered members can send this form» + لینک ورود نمایش داده می‌شود |
| فیلدها و دکمه ارسال | رندر نمی‌شوند (ساختار فرم به مهمان افشا نمی‌شود) |
| nonce | رندر نمی‌شود |
| ارسال مستقیم endpoint | به صفحه ورود وردپرس هدایت می‌شود با `rcs_login_required=1` و `redirect_to` |
| لاگ ممیزی | هیچ رکوردی ثبت نمی‌شود (درخواست اصلاً پردازش نمی‌شود) |

### ترتیب بررسی‌ها در endpoint (بند ۳۸ سند معماری)

```text
1. Authentication  → is_user_logged_in()   (اولین بررسی، قبل از nonce)
2. Nonce           → wp_verify_nonce()
3. Form exists     → ۴۰۴ در غیر این صورت
4. Field validation → پیام خطا به کاربر
5. Audit log       → ثبت در rcs_logs
```

> **چرا بررسی عضویت قبل از nonce:** nonce برای کاربر واردنشده (uid = 0) هم قابل تولید است، پس اگر nonce زودتر بررسی شود، مهمان با خطای ۴۰۳ مواجه می‌شود به‌جای هدایت به صفحه ورود. بنابراین ترتیب بالا رعایت شده است.

### فایل‌های درگیر

| فایل | تغییر |
|------|-------|
| `includes/Forms/SubmissionHandler.php` | بررسی `is_user_logged_in()` در ابتدای `handle()` + هدایت به صفحه ورود |
| `frontend/Frontend.php` | انتقال وضعیت عضویت و لینک ورود به قالب + متد `get_login_url()` |
| `frontend/views/form.php` | نمایش پیام ورود به‌جای فرم برای مهمان |
| `frontend/assets/css/rcs-form.css` | استایل کادر پیام ورود |

> در ماژول Requests (قدم بعدی) همین ترتیب باید در `RCS_Requests_RequestAccess` رعایت شود: احراز هویت → بررسی مالکیت → بررسی وضعیت.

---

## ۳.۴ قابلیت پیاده‌سازی‌شده: پیامک SMS.ir

### قرارداد سرویس‌دهنده

```text
Endpoint : POST https://api.sms.ir/v1/send/verify
Headers  : x-api-key: <API key> | Content-Type: application/json | Accept: text/plain
Body     : { "mobile": "9120000000", "templateId": 111111, "parameters": [ { "name": "Code", "value": "1234" } ] }
Response : { "status": 1, "message": "موفق", "data": { "messageId": 89545112, "cost": 1.0 } }
```

- پیامک **الگویی (Pattern)** است؛ متن پیام هرگز در افزونه ذخیره یا ارسال نمی‌شود.
- کد وضعیت سرویس‌دهنده: `1` موفق، `0` خطای سامانه، `10` کلید نامعتبر، `11` کلید غیرفعال، `12` محدود به IP.
- کدهای HTTP: ۲۰۰ موفق، ۴۰۰ خطای منطقی، ۴۰۱ احراز هویت، ۴۲۹ تعداد درخواست زیاد، ۵۰۰ خطای غیرمنتظره.
- ارتباط با HTTP API وردپرس (`wp_remote_request`) انجام می‌شود تا در تست قابل شبیه‌سازی باشد.

### انواع پیامک و الگوها

| نوع | گیرنده | پارامترهای پیش‌فرض |
|-----|--------|---------------------|
| `approval_customer` | مشتری | name, discount, expiry |
| `approval_admin` | مدیر | name, discount, request |
| `rejection_customer` | مشتری | name, reason |
| `expiry_reminder_customer` | مشتری | name, expiry |

هر پیامک یک رکورد مستقل در `rcs_sms` است؛ شکست یکی روی دیگری اثر ندارد.

### تنظیمات (option `rcs_sms_settings`)

| کلید | توضیح | ضروری؟ |
|------|--------|--------|
| `api_key` | کلید API پنل برنامه‌نویسان SMS.ir (کلید Sandbox هم پذیرفته می‌شود) | برای ارسال لازم |
| `pattern_ids` | نگاشت نوع پیامک → شناسه Pattern | برای ارسال لازم |
| `sender_line` | خط ارسال | **اختیاری — بدون ستاره و بدون `required`** |
| `admin_numbers` | شماره‌های مدیر (هر خط یک شماره) | برای پیامک به مدیر لازم |

نگاشت نام پارامترها در option جداگانه `rcs_sms_parameters` نگهداری می‌شود (نام منطقی → نام پارامتر در قالب سرویس‌دهنده).

> **خط ارسال** چون ارسال Pattern از خط خدماتی ثبت‌شده در پنل انجام می‌شود، در API ارسال پارامتری به نام آن وجود ندارد؛ فیلد آن فقط نگهداری می‌شود و هرگز اجباری نیست.

### قواعد ارسال و تلاش مجدد

- شماره موبایل پیش از ذخیره نرمال می‌شود: `09120000000`، `+989120000000` و `00989120000000` همگی به `9120000000` تبدیل می‌شوند.
- ارسال به مشتری: `SmsManager::send_to_customer( $type, $recipient, $values, $context )`.
- ارسال به مدیر: `SmsManager::send_to_admin( $type, $values, $context )` — برای **هر** شماره مدیر یک رکورد جداگانه ساخته و ارسال می‌شود.
- تلاش مجدد: `SmsRetry::retry( $sms_id )` فقط روی رکورد `failed`، روی **همان رکورد**، با افزایش `retry_count` و ثبت `last_retry_at`.
- رکورد `sent` دوباره ارسال یا retry نمی‌شود (idempotent).
- هر ارسال موفق/ناموفق/تلاش مجدد در لاگ ممیزی ثبت می‌شود (`SMS Sent` / `SMS Failed` / `SMS Retried`).
- ماژول SMS هیچ وابستگی‌ای به Coupons یا Request ندارد؛ گیرنده را caller تعیین می‌کند.

### آنچه هنوز در این قابلیت انجام نشده

- ارسال خودکار پیامک‌ها در گردش تأیید/رد (به ماژول Approval وابسته است).
- گزارش وضعیت تحویل (Delivery) از API سرویس‌دهنده.
- صف پیامک و زمان‌بندی ارسال.

---

## ۴. ساختار کامل دایرکتوری

```text
plugin-root/
├── plugin.php                 ← نقطه ورود، ثابت‌ها، autoloader، قلاب‌های lifecycle
├── uninstall.php              ← نقطه ورود حذف
├── readme.txt                 ← ریدمی استاندارد وردپرس
├── ai-context.md              ← همین سند
│
├── includes/
│   ├── Core/
│   │   ├── Plugin.php         ← RCS_Core_Plugin
│   │   ├── Activator.php      ← RCS_Core_Activator
│   │   ├── Deactivator.php    ← RCS_Core_Deactivator
│   │   ├── Uninstaller.php    ← RCS_Core_Uninstaller
│   │   └── Requirements.php   ← RCS_Core_Requirements
│   ├── Forms/
│   │   ├── FormManager.php · FormRepository.php · FieldManager.php
│   │   ├── SubmissionHandler.php · Validation.php · ConditionalLogic.php
│   │   └── DraftManager.php · MultiStepManager.php
│   ├── Requests/
│   │   ├── RequestManager.php · RequestRepository.php
│   │   └── RequestStatus.php · RequestAccess.php
│   ├── Revisions/
│   │   └── RevisionManager.php · RevisionRepository.php
│   ├── Approval/
│   │   └── ApprovalManager.php · ApprovalValidator.php · ExpertRole.php
│   ├── Coupons/
│   │   └── CouponManager.php · CouponValidator.php
│   │       CouponOwnership.php · WooCommerceCoupon.php
│   ├── SMS/
│   │   └── SmsManager.php · SmsTemplateManager.php · SmsSender.php
│   │       SmsRetry.php · SmsStatus.php
│   ├── Scheduler/Scheduler.php
│   ├── Logs/LogManager.php · LogRepository.php
│   ├── Reports/ReportManager.php
│   ├── Integrations/
│   │   ├── WooCommerce/WooCommerceIntegration.php
│   │   ├── SMSIr/SmsIrClient.php
│   │   └── Elementor/ElementorIntegration.php
│   └── Database/Installer.php · Schema.php
│
├── admin/
│   ├── Admin.php
│   ├── pages/  FormsPage · RequestsPage · CouponsPage · SmsPage · ReportsPage · SettingsPage
│   ├── views/   forms/ requests/ coupons/ sms/ reports/ settings/  (هر کدام index.php)
│   └── assets/  css/ js/
│
├── frontend/
│   ├── Frontend.php
│   ├── views/
│   └── assets/  css/ js/
│
├── elementor/
│   ├── Elementor.php
│   └── widgets/FormWidget.php
│
├── templates/
└── languages/
```

**ساختار ثابت است.** فایل جدید فقط وقتی ساخته شود که مسئولیت آن در هیچ کلاس موجودی جا نشود.

---

## ۵. چرخه حیات (Lifecycle)

### فعال‌سازی — `RCS_Core_Activator::activate()`

```text
register_activation_hook → RCS_Core_Activator::activate()
  1. RCS_Core_Requirements::store()        خطاها در transient (بدون wp_die)
  2. RCS_Database_Installer::install()      dbDelta روی ۷ جدول + option نسخه دیتابیس
  3. RCS_Approval_ExpertRole::create()      نقش rcs_expert + ۳ کپسیتی
  4. grant_admin_capabilities()             ۷ کپسیتی مدیریتی به administrator
  5. create_default_settings()              rcs_sms_settings + rcs_general_settings
  6. RCS_Scheduler_Scheduler::schedule_events()  ۲ کرون
  7. update_option( 'rcs_version' )         نشانه فعال بودن افزونه
  8. flush_rewrite_rules()
```

### اجرا (Runtime)

```text
WordPress → plugin.php → RCS_Autoloader::register()
          → RCS_Core_Plugin::instance()->boot()
            → 'init' (priority 0)        → load_plugin_textdomain
            → 'plugins_loaded' (prio 20)  → init_modules():
                 RCS_Database_Installer::maybe_upgrade()
                 Logs · Scheduler · Frontend · Elementor · WooCommerce
                 + Admin (فقط اگر is_admin())
```

### غیرفعال‌سازی — `RCS_Core_Deactivator::deactivate()`

```text
wp_clear_scheduled_hook( rcs_coupon_expiry_reminder )
wp_clear_scheduled_hook( rcs_sms_retry )
flush_rewrite_rules()
→ هیچ داده‌ای حذف نمی‌شود (جدول‌ها، optionها، نقش، لاگ‌ها باقی می‌مانند)
```

### حذف (Uninstall) — `RCS_Core_Uninstaller::uninstall()`

```text
uninstall.php  (WP_UNINSTALL_PLUGIN تعریف می‌شود) → plugin.php (بدون بوت) → uninstall()
  1. لغو کرون‌ها
  2. حذف کوپن‌های ووکامرس که متای _rcs_managed_coupon دارند (فقط همین‌ها)
  3. حذف ۷ جدول
  4. حذف optionها + transient
  5. حذف کپسیتی‌ها از administrator
  6. حذف نقش rcs_expert
```

> **نکته مهم:** `Installer::maybe_upgrade()` فقط وقتی جدول‌ها را می‌سازد که option `rcs_version` موجود باشد. به این ترتیب بعد از uninstall، بارگذاری مجدد وردپرس دوباره جدول‌ها را نمی‌سازد (این رگرسیون تست شده است).

---

## ۶. مدل داده (دقیقاً همان چیزی که ساخته می‌شود)

| جدول | کلید اصلی | ستون‌های کلیدی |
|------|-----------|----------------|
| `rcs_forms` | `id` | name, status, settings, created_by, created_at, updated_at |
| `rcs_form_fields` | `field_id` | form_id, field_type, field_key, label, options, validation, conditional_logic, step, sort_order, field_settings |
| `rcs_requests` | `request_id` | form_id, user_id, current_revision_id, status, created_at, updated_at, approved_at, rejected_at |
| `rcs_request_revisions` | `revision_id` | request_id, revision_number, submitted_by, submitted_data, status, created_at, submitted_at, rejection_reason |
| `rcs_plugin_coupons` | `id` | request_id (**UNIQUE**), user_id, wc_coupon_id, coupon_code, created_at, approved_at, disabled_at |
| `rcs_sms` | `id` | request_id, user_id, recipient, sms_type, pattern_id, params, status, provider_response, error_message, retry_count, created_at, sent_at, last_retry_at |
| `rcs_logs` | `id` | actor_user_id, actor_role, action, request_id, revision_id, coupon_id, sms_id, ip_address, result, error_message, created_at |

قید `UNIQUE KEY coupon_request_id (request_id)` در `rcs_plugin_coupons` **قانون «یک کوپن برای هر درخواست» را در سطح دیتابیس** تضمین می‌کند.

ستون‌های زمانی از نوع `datetime NULL DEFAULT NULL` هستند (نه zero-date) تا با `sql_mode` سخت‌گیرانه MySQL 8 / MariaDB سازگار باشد.

---

## ۷. قراردادهای ثابت

### ۷.۱ کپسیتی‌ها

| کپسیتی | نقش |
|---------|------|
| `rcs_manage_forms` | مدیریت فرم‌ها |
| `rcs_manage_requests` | مدیریت درخواست‌ها |
| `rcs_manage_coupons` | مدیریت کوپن‌ها |
| `rcs_manage_sms` | مدیریت پیامک‌ها (شامل retry) |
| `rcs_view_reports` | مشاهده گزارش‌ها |
| `rcs_manage_settings` | تنظیمات |
| `rcs_view_logs` | مشاهده لاگ‌ها |
| `rcs_review_requests` | بررسی درخواست (نقش کارشناس) |
| `rcs_approve_requests` | تأیید درخواست (نقش کارشناس) |
| `rcs_reject_requests` | رد درخواست (نقش کارشناس) |

> **تصمیم موقت:** فقط نقش `rcs_expert` کپسیتی‌های تأیید/رد را دارد و مدیر (administrator) ندارد. این بند در سند معماری «تعیین‌نشده» بود؛ اگر مدیر هم باید تأیید کند، کافی است در `RCS_Core_Plugin::admin_capabilities()` سه کپسیتی کارشناس اضافه شود.

### ۷.۲ هوک‌ها و کرون‌ها

| هوک | نوع | کاربرد |
|------|------|--------|
| `activate_{plugin}` | action | `RCS_Core_Activator::activate` |
| `deactivate_{plugin}` | action | `RCS_Core_Deactivator::deactivate` |
| `init` (0) | action | بارگذاری textdomain |
| `plugins_loaded` (20) | action | راه‌اندازی ماژول‌ها |
| `admin_menu` | action | ثبت منوی پنل |
| `admin_notices` | action | نمایش خطاهای پیش‌نیاز |
| `wp_enqueue_scripts` | action | رجیستر assets فرانت |
| `elementor/widgets/register` | action | ثبت ویجت فرم |
| `woocommerce_init` | action | شروع قلاب‌های ووکامرس |
| `rcs_coupon_expiry_reminder` | cron (روزانه) | یادآوری انقضای کوپن |
| `rcs_sms_retry` | cron (ساعتی) | تلاش مجدد پیامک ناموفق |

### ۷.۳ Optionها

| Option | محتوا |
|--------|--------|
| `rcs_db_version` | نسخه اسکیمای دیتابیس (`1.0.0`) |
| `rcs_version` | نسخه افزونه — نشانه فعال بودن |
| `rcs_sms_settings` | api_key, pattern_ids, sender_line, admin_numbers |
| `rcs_general_settings` | تنظیمات عمومی |
| `rcs_requirements_errors` (transient) | خطاهای پیش‌نیاز (۱ ساعت) |
| `rcs_sms_parameters` | نگاشت نام پارامتر هر نوع پیامک به نام پارامتر در قالب سرویس‌دهنده |

### ۷.۴ وضعیت‌ها

```text
Request: rcs draft | pending | rejected | approved
SMS:     pending | sent | failed
```

---

## ۸. قواعد معماری (الزامی)

### ۸.۱ وابستگی مجاز

```text
Forms → Requests      Requests → Revisions     Approval → Requests
Approval → Coupons    Approval → SMS           Coupons → WooCommerce
SMS → SMS.ir          Elementor → Forms
```

### ۸.۲ وابستگی ممنوع

```text
Forms → Coupons        Forms → SMS          Coupons → SMS
SMS → Request logic    Elementor → Coupons  Elementor → SMS
```

### ۸.۳ قواعد کدنویسی

1. همه کلاس‌ها `final` و singleton با `instance()` و constructor خصوصی (کلاس‌های utility مثل `Activator` فقط متد static دارند).
2. نام کلاس = `RCS_<Module>_<ClassName>`؛ نام فایل = نام کلاس بدون پیشوند.
3. **هیچ کامنتی (فارسی یا انگلیسی) داخل کد PHP ننویسید.** تنها استثنا هدر افزونه در `plugin.php` است.
4. تماس مستقیم با `$wpdb` فقط در `includes/Database/`، `includes/Logs/` و کلاس‌های `*Repository` (لایه دسترسی داده) مجاز است؛ در کلاس‌های Manager و صفحات ادمین ممنوع است.
5. هر تعامل با وردپرس/ووکامرس/المنتور/سرویس بیرونی داخل `try { } catch ( \Throwable $e ) { }`.
6. هر کلاس جدید باید به نقشه `RCS_Autoloader::$map` در `plugin.php` اضافه شود، وگرنه بارگذاری نمی‌شود.
7. Manager = منطق دامنه. Repository = فقط دسترسی داده (بدون تصمیم کسب‌وکاری).
8. منطق تکراری ممنوع؛ جریان تأیید فقط در `RCS_Approval_ApprovalManager` است.
9. کلاس Repository/Manager اضافه نسازید مگر مسئولیت آن در کلاس موجود جا نشود.

### ۸.۴ امنیت (الزامی در همه ماژول‌ها)

```text
درخواست کاربر:  is_user_logged_in → بررسی مالکیت → بررسی وضعیت → عملیات
عملیات کارشناس: current_user_can( rcs_* ) → بررسی دسترسی → عملیات
عملیات مدیر:    current_user_can( rcs_manage_* ) → nonce → sanitize → عملیات
```

- همیشه `wp_verify_nonce` برای عملیات POST/AJAX
- ورودی: `sanitize_text_field` / `sanitize_key` / `absint`
- خروجی: `esc_html` / `esc_attr` / `esc_url`
- هر عملیات مهم مدیریتی → `RCS_Logs_LogManager::instance()->log()`

---

## ۹. قواعد کسب‌وکار (قابل پیاده‌سازی در فاز بعد)

### ۹.۱ وضعیت درخواست

| وضعیت | ویرایش‌پذیر | رفتار |
|-------|-------------|-------|
| `draft` | بله | پیش‌نویس |
| `pending` | خیر (در انتظار بررسی) | قابل تأیید/رد |
| `rejected` | بله | ویرایش → ساخت Revision جدید → `pending` |
| `approved` | خیر (قفل) | بدون Revision جدید، بدون تأیید مجدد، بدون کوپن دوم |

هر resubmit یک Revision جدید می‌سازد و **تاریخچه نسخه‌های قبلی هرگز بازنویسی نمی‌شود**. کوپن به Request تعلق دارد، نه به Revision.

### ۹.۲ جریان تأیید

```text
ApprovalManager
  → ApprovalValidator (وجود، وضعیت، تأیید‌نشدن قبلی، صحت revision، کاربر، درصد و سقف تخفیف)
  → CouponManager → WooCommerceCoupon (ساخت کوپن در ووکامرس)
  → ذخیره رابطه در rcs_plugin_coupons
  → Request = approved + قفل
  → ساخت دو رکورد SMS (مشتری + مدیر) → SmsSender → SmsIrClient
```

- اگر ساخت کوپن شکست خورد → **هیچ SMS ارسال نمی‌شود** و درخواست تأییدشده محسوب نمی‌شود.
- اگر کوپن ساخته شد ولی SMS شکست خورد → کوپن معتبر است، Request تأییدشده است، SMS قابل retry است.
- تأیید باید **idempotent** باشد (اجرای دوباره = کوپن دوم ممنوع).

### ۹.۳ قواعد کوپن

- درصد تخفیف، روی همه محصولات، همه دسته‌ها، شامل محصولات تخفیف‌دار
- بدون حداقل خرید، individual use، limit استفاده کلی = ۱، limit استفاده هر کاربر = ۱
- محدود به User ID دقیق صاحب کوپن و غیرقابل انتقال
- انقضا = دقیقاً ۷ × ۲۴ ساعت پس از تأیید
- یک Request = یک کوپن؛ یک کاربر می‌تواند از طریق Requestهای مختلف کوپن‌های نامحدود بگیرد
- مالکیت در `rcs_plugin_coupons` نگهداری و در اعتبارسنجی بررسی می‌شود
- **الزامی برای فاز بعد:** هنگام ساخت کوپن، متای `_rcs_managed_coupon` روی پست کوپن ووکامرس ثبت شود؛ `RCS_Core_Uninstaller` فقط کوپن‌هایی را حذف می‌کند که این متا را دارند تا کوپن‌های دست‌نخورده ووکامرس آسیب نبینند.

### ۹.۴ انواع پیامک

| نوع | گیرنده | قابل retry |
|-----|---------|-------------|
| `approval_customer` | مشتری | (تصمیم‌نشده — بند ۱۰) |
| `approval_admin` | مدیر | (تصمیم‌نشده) |
| `rejection_customer` | مشتری | (تصمیم‌نشده) |
| `expiry_reminder_customer` | مشتری | (تصمیم‌نشده) |

متن پیامک **هرگز hard-code نمی‌شود**؛ فقط Pattern ID و نگاشت پارامترها ذخیره می‌شود. هر پیامک یک رکورد مستقل است و شکست یکی، دیگری را باطل نمی‌کند. Retry روی **همان رکورد SMS** انجام می‌شود (نه رکورد جدید) و هرگز Request/Revision/Coupons جدید نمی‌سازد.

---

## ۱۰. مواردی که نباید حدس زد (تصمیم‌های باز)

| # | موضوع | وضعیت فعلی |
|---|--------|------------|
| ۱ | نام/اسلاگ/پیشوند/namespace نهایی | **قطعی شد**: Request & Coupon System / `RCS_` (همین سند) |
| ۲ | حداقل نسخه وردپرس | **قطعی شد**: 6.0 (ووکامرس 7.0 اختیاری) |
| ۳ | حداقل نسخه المنتور | هنوز مشخص نشده — فعلاً اعلام نمی‌شود |
| ۴ | ثبت‌نام کاربر / OTP | مشخص نشده — پیاده‌سازی نشود |
| ۵ | آیا مدیر هم می‌تواند تأیید/رد کند | **موقت**: فقط `rcs_expert` (قابل تغییر در یک خط) |
| ۶ | آیا تغییر کوپن پس از تأیید SMS جدید می‌خواهد | مشخص نشده — فعلاً خیر/بدون پیاده‌سازی |
| ۷ | آیا کوپن غیرفعال‌شده دوباره فعال می‌شود | مشخص نشده |
| ۸ | آیا مدیر می‌تواند مهلت ۷ روزه را تغییر دهد | مشخص نشده |
| ۹ | کدام انواع SMS قابل retry هستند | مشخص نشده (ساختار پشتیبانی می‌کند) |
| ۱۰ | یک یا چند شماره مدیر | ساختار `admin_numbers` آرایه‌ای است |
| ۱۱ | MIMEهای مجاز آپلود | مشخص نشده — hard-code نکنید |
| ۱۲ | حداکثر حجم آپلود | مشخص نشده |
| ۱۳ | رفتار چند پیش‌نویس همزمان | مشخص نشده |
| ۱۴ | اپراتورهای منطق شرطی | مشخص نشده |
| ۱۵ | پیاده‌سازی دقیق زمان‌بند | ساختار کرون ساخته شده، منطق نه |
| ۱۶ | نیازهای گزارش/خروجی اکسل | مشخص نشده |

> اگر برای پیاده‌سازی یکی از این موارد نیاز شد، **توقف کنید و سؤال کنید** — فرض نکنید.

---

## ۱۱. نقشه راه فاز بعد (به‌ترتیب پیشنهادی)

هر مرحله را جداگانه پیاده و تست کنید. مرحله ۱ انجام شده است.

1. ~~**Forms**~~ — ✅ **انجام شد**: `FormManager` (CRUD) + `FormRepository` + `FieldManager` (۱۰ نوع فیلد) + `SubmissionHandler` + فرم‌ساز پنل + ویجت المنتور
2. **Requests** — `RequestManager` + `RequestRepository` + `RequestAccess` (مالکیت/وضعیت) + `DraftManager` ← **قدم بعدی**
3. **Revisions** — `RevisionManager` + `RevisionRepository` (شماره‌گذاری نسخه، حفظ تاریخچه)
4. **Approval** — `ApprovalValidator` سپس `ApprovalManager` (جریان تأیید/رد idempotent)
5. **Coupons** — `WooCommerceCoupon` (adapter) → `CouponManager` → `CouponValidator` → `CouponOwnership`
6. **SMS** — ✅ **انجام شد**: `SmsTemplateManager` (انواع و نگاشت پارامتر) → `SmsSender` → `SmsIrClient` (API) → `SmsManager` → `SmsRetry` + صفحه تنظیمات و تاریخچه
7. **Scheduler** — پیاده‌سازی `run_coupon_expiry_reminder` و `run_sms_retry` (idempotent)
8. **Reports** — `ReportManager` روی همین جدول‌ها
9. **Forms (تکمیل)** — منطق شرطی (`ConditionalLogic`)، چندمرحله‌ای (`MultiStepManager`)، اعتبارسنجی پیشرفته (`Validation`) و اتصال endpoint ارسال به ساخت Request
10. **اعضای اجباری (تکمیل)** — اعمال همان قاعده عضویت در عملیات درخواست‌ها داخل `RCS_Requests_RequestAccess`
11. **UI** — جایگزینی ویوهای placeholder با رابط واقعی

---

## ۱۲. محیط تست و روال اعتبارسنجی

محیط مرجع تست‌شده: **WordPress 7.1.3 + MariaDB 11.8 + PHP 8.4 + WP-CLI**.

```bash
# فعال‌سازی
wp plugin activate request-coupon-system
wp db query "SHOW TABLES LIKE 'rcs\_%';"          # باید ۷ جدول باشد
wp db query "SELECT option_name FROM wp_options WHERE option_name LIKE 'rcs%';"
wp role list                                       # rcs_expert باید باشد
wp cron event list --fields=hook                   # rcs_coupon_expiry_reminder, rcs_sms_retry

# بررسی خطاها (WP_DEBUG + WP_DEBUG_LOG باید فعال باشند)
cat wp-content/debug.log                            # باید خالی باشد

# غیرفعال‌سازی: داده باید باقی بماند
wp plugin deactivate request-coupon-system

# حذف: uninstall.php وردپرس اجرا می‌شود
wp eval 'uninstall_plugin("request-coupon-system/plugin.php");'
```

**قواعد تست:**
- قبل از هر تست، `wp-content/debug.log` را خالی کنید تا خطاهای قبلی گم نشوند.
- هر تست جدید باید هم «خروجی بدون خطا» و هم «اثر واقعی در دیتابیس» را بررسی کند.
- `wp db query` وردپرس را بارگذاری **نمی‌کند**؛ برای تست اثر زمان اجرا از `wp eval` استفاده کنید.
- `wp plugin delete` قلاب uninstall را اجرا **نمی‌کند**؛ برای تست uninstall از `wp eval 'uninstall_plugin(...)'` استفاده کنید.
- برای شبیه‌سازی POST در CLI: `$_SERVER['REQUEST_METHOD'] = 'POST'` را ست کنید.
- برای گرفتن redirect در CLI: فیلتر `wp_redirect` را حذف و با یک closure که exception پرتاب می‌کند جایگزین کنید (`remove_all_filters('wp_redirect')`).
- برای گرفتن `wp_die` در CLI: فیلتر `wp_die_handler` را با closure‌ای که exception پرتاب می‌کند جایگزین کنید.
- گزینه‌های کنترل SELECT در المنتور ۴ فقط در زمینه‌ی ادیتور نگهداری می‌شوند؛ در CLI (فرانت) به‌خاطر `Performance::should_optimize_controls()` حذف می‌شوند. برای تست آن‌ها مقدار استاتیک `is_frontend` را با reflection روی `false` بگذارید.
- تست افزونه باید **دو بار** اجرا شود: یک‌بار با المنتور فعال و یک‌بار با المنتور غیرفعال (برای اطمینان از نبود fatal).

---

## ۱۳. تاریخچه تصمیم‌ها و انحراف‌ها

| موضوع | تصمیم | دلیل |
|--------|--------|------|
| نام‌گذاری | `RCS_` به‌جای namespace | قرارداد پروژه در `ai-context.md` نسخه قبلی؛ ساده‌تر برای WP |
| نام جدول‌ها | `rcs_*` | سند معماری پیشوند را باز گذاشته بود (`{prefix}_plugin_*`)؛ این سند مرجع است |
| نام فایل `SubmissionHandler` | `includes/Forms/SubmissionHandler.php` | مطابق ساختار مصوب (بدون خط تیره) |
| خطاهای پیش‌نیاز | transient + notice | فعال‌سازی مسدود نشود؛ گزارش در پنل |
| بازسازی جدول بعد از uninstall | غیرفعال | با نیازمندی `rcs_version` در `maybe_upgrade()` |
| کپسیتی تأیید برای مدیر | ندارد (فعلاً) | بند ۱۰ مورد ۵ تعیین‌نشده |
| ادمین‌پنل | منو + ۶ صفحه با ویوی placeholder | بدون UI نهایی در این فاز |
| وضعیت فرم | `draft` / `published` | ویجت المنتور فقط فرم‌های `published` را نشان می‌دهد |
| بارگذاری ویجت المنتور | کلاس داخل `if ( class_exists( '\Elementor\Widget_Base' ) )` | بدون المنتور نباید fatal بدهد (باگ واقعی که در تست کشف شد) |
| `is_dynamic_content()` | با امضای `: bool` | امضای والد در المنتور ۴.۳.۵ `: bool` دارد و ناسازگاری باعث fatal می‌شد |
| دسته ویجت المنتور | `rcs` از طریق `elementor/elements/categories_registered` | در المنتور ۴.x taxonomy قدیمی دسته‌ها حذف شده است |
| استفاده از `$wpdb` | در `*Repository` و `SmsManager` هم مجاز | ماژول SMS در ساختار مصوب Repository ندارد، پس دسترسی داده در خود `SmsManager` است (بند ۳۴ سند معماری) |
| خط ارسال (Sender line) | **اختیاری**، بدون ستاره و بدون `required` | درخواست کاربر؛ ارسال Pattern از خط خدماتی پنل انجام می‌شود و پارامتری به نام خط ارسال در API وجود ندارد |
| متن پیامک | ذخیره یا ارسال نمی‌شود | بند ۱۸ سند معماری: فقط Pattern ID و نگاشت پارامتر |
| endpoint سرویس‌دهنده | `POST /v1/send/verify` با هدر `x-api-key` | مستندات رسمی SMS.ir؛ تنها متد مبتنی بر Pattern |
| ارسال فرم | فقط برای کاربر واردشده، سراسری | درخواست کاربر؛ مهمان به صفحه ورود هدایت می‌شود و فرم رندر نمی‌شود |
| ترتیب بررسی امنیتی | احراز هویت قبل از nonce | nonce کاربر واردنشده هم معتبر است؛ بند ۳۸ سند معماری |

---

> پایان سند. پیش از هر تغییری این فایل را بخوانید و پس از هر تغییر معنادار، بخش‌های مرتبط (۳، ۶، ۷، ۱۳) را به‌روز کنید.
